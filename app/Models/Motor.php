<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Motor extends Model
{
    protected $table = 'motor';

    private const BRAND_TOKENS = [
        'honda', 'yamaha', 'kawasaki', 'suzuki', 'vespa',
        'ktm', 'ducati', 'bmw', 'aprilia', 'benelli', 'tvs',
    ];

    protected $fillable = [
        'nama_motor', 'id_jenis', 'harga_jual', 'deskripsi_motor',
        'warna', 'kapasitas_mesin', 'tahun_produksi',
        'berat_gram', 'foto1', 'foto2', 'foto3', 'stok',
    ];

    public function jenisMotor(): BelongsTo
    {
        return $this->belongsTo(JenisMotor::class, 'id_jenis');
    }

    public function pengajuanKredit(): HasMany
    {
        return $this->hasMany(PengajuanKredit::class, 'id_motor');
    }

    public function getPrimaryImageUrlAttribute(): string
    {
        if ($storedImage = $this->existingStorageImagePath($this->foto1)) {
            return $this->storageImageUrl($storedImage);
        }

        if ($matchedImage = $this->matchingStorageImagePath()) {
            return $this->storageImageUrl($matchedImage);
        }

        $fallbackPath = 'images/motors/' . Str::slug($this->nama_motor) . '.svg';

        if (file_exists(public_path($fallbackPath))) {
            return asset($fallbackPath);
        }

        return asset('images/motors/default.svg');
    }

    public function getGalleryImageUrlsAttribute(): array
    {
        $photos = collect(['foto1', 'foto2', 'foto3'])
            ->map(fn (string $photo) => $this->existingStorageImagePath($this->{$photo}))
            ->filter()
            ->map(fn (string $path) => $this->storageImageUrl($path))
            ->values()
            ->all();

        return $photos ?: [$this->primary_image_url];
    }

    public function getShippingWeightGramsAttribute(): int
    {
        return (int) ($this->berat_gram ?: config('rajaongkir.default_weight', 125000));
    }

    private function storageImageUrl(string $path): string
    {
        return asset('storage/' . ltrim($path, '/'));
    }

    private function existingStorageImagePath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = ltrim($path, '/');

        if ($webpPath = self::preferredWebpPath($path)) {
            return $webpPath;
        }

        return Storage::disk('public')->exists($path) ? $path : null;
    }

    private static function preferredWebpPath(string $path): ?string
    {
        $path = ltrim($path, '/');

        if (Str::endsWith(strtolower($path), '.webp')) {
            return Storage::disk('public')->exists($path) ? $path : null;
        }

        $webpPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);

        return $webpPath && Storage::disk('public')->exists($webpPath) ? $webpPath : null;
    }

    private function matchingStorageImagePath(): ?string
    {
        $files = self::publicProductImageFiles();

        if ($files === []) {
            return null;
        }

        $nameSlug = Str::slug($this->nama_motor);
        $nameTokens = self::imageTokens($this->nama_motor);
        $specificTokens = array_values(array_filter(
            $nameTokens,
            fn (string $token) => strlen($token) > 2
                && !is_numeric($token)
                && !in_array($token, self::BRAND_TOKENS, true)
        ));

        $bestPath = null;
        $bestSpecificScore = -1;
        $bestTotalScore = -1;
        $bestIsWebp = false;

        foreach ($files as $path) {
            $fileSlug = Str::slug(pathinfo($path, PATHINFO_FILENAME));

            if ($nameSlug && str_contains($fileSlug, $nameSlug)) {
                return $path;
            }

            $specificScore = self::tokenScore($specificTokens, $fileSlug);
            $totalScore = self::tokenScore($nameTokens, $fileSlug);
            $isWebp = Str::endsWith(strtolower($path), '.webp');

            if (
                $specificScore > $bestSpecificScore
                || ($specificScore === $bestSpecificScore && $totalScore > $bestTotalScore)
                || ($specificScore === $bestSpecificScore && $totalScore === $bestTotalScore && $isWebp && !$bestIsWebp)
            ) {
                $bestPath = $path;
                $bestSpecificScore = $specificScore;
                $bestTotalScore = $totalScore;
                $bestIsWebp = $isWebp;
            }
        }

        return $bestSpecificScore > 0 || $bestTotalScore > 0 ? $bestPath : null;
    }

    private static function publicProductImageFiles(): array
    {
        static $files = null;

        if ($files !== null) {
            return $files;
        }

        $files = collect(Storage::disk('public')->files())
            ->filter(fn (string $path) => preg_match('/\.(webp|jpe?g|png)$/i', $path))
            ->map(fn (string $path) => self::preferredWebpPath($path) ?: $path)
            ->unique()
            ->values()
            ->all();

        return $files;
    }

    private static function imageTokens(string $value): array
    {
        return collect(explode('-', Str::slug($value)))
            ->filter(fn (string $token) => strlen($token) > 1)
            ->unique()
            ->values()
            ->all();
    }

    private static function tokenScore(array $tokens, string $haystack): int
    {
        $score = 0;

        foreach ($tokens as $token) {
            if (str_contains($haystack, $token)) {
                $score += strlen($token) >= 4 ? 2 : 1;
            }
        }

        return $score;
    }
}
