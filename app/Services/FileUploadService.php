<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload a file to storage.
     */
    public function upload(UploadedFile $file, string $directory = 'uploads'): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');

        return $path;
    }

    /**
     * Upload an image as WebP when the server supports conversion.
     */
    public function uploadImageAsWebp(UploadedFile $file, string $directory = 'uploads', int $quality = 82): string
    {
        if (!function_exists('imagewebp')) {
            return $this->upload($file, $directory);
        }

        $image = match ($file->getMimeType()) {
            'image/jpeg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png' => imagecreatefrompng($file->getRealPath()),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($file->getRealPath()) : null,
            default => null,
        };

        if (!$image) {
            return $this->upload($file, $directory);
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $path = trim($directory, '/') . '/' . Str::uuid() . '.webp';
        ob_start();
        imagewebp($image, null, $quality);
        $contents = ob_get_clean();
        imagedestroy($image);

        if ($contents === false) {
            return $this->upload($file, $directory);
        }

        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    /**
     * Delete a file from storage.
     */
    public function delete(?string $path): void
    {
        if (!$path) {
            return;
        }

        $paths = [$path];
        $webpPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);

        if ($webpPath && $webpPath !== $path) {
            $paths[] = $webpPath;
        }

        foreach (array_unique($paths) as $deletePath) {
            if (Storage::disk('public')->exists($deletePath)) {
                Storage::disk('public')->delete($deletePath);
            }
        }
    }

    /**
     * Upload multiple document files for a pengajuan kredit.
     */
    public function uploadDocuments(array $files): array
    {
        $paths = [];
        $docTypes = ['url_kk', 'url_ktp', 'url_npwp', 'url_slip_gaji', 'url_foto'];

        foreach ($docTypes as $type) {
            if (isset($files[$type]) && $files[$type] instanceof UploadedFile) {
                $paths[$type] = $this->upload($files[$type], 'dokumen');
            }
        }

        return $paths;
    }
}
