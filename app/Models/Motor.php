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

        if ($catalogImage = $this->existingCatalogImagePath()) {
            return asset($catalogImage);
        }

        return asset('images/motors/default.jpg');
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

    private function existingCatalogImagePath(): ?string
    {
        $basePath = 'images/motors/' . Str::slug($this->nama_motor);

        foreach (['webp', 'jpg', 'jpeg'] as $extension) {
            $path = $basePath . '.' . $extension;

            if (file_exists(public_path($path))) {
                return $path;
            }
        }

        return null;
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
}
