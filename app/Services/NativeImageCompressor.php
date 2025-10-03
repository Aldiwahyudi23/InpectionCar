<?php
// app/Services/NativeImageCompressor.php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NativeImageCompressor
{
    /**
     * Compress image untuk web display (50% quality)
     */
    public function compressForWeb($imagePath, $quality = 50, $maxWidth = 800)
    {
        $fullPath = storage_path('app/' . $imagePath);
        
        // Cek file exists
        if (!file_exists($fullPath)) {
            return Storage::url($imagePath); // Fallback ke original
        }

        $optimizedPath = 'optimized/' . $imagePath;
        $fullOptimizedPath = storage_path('app/' . $optimizedPath);

        // Jika optimized version sudah ada, langsung return
        if (file_exists($fullOptimizedPath)) {
            return Storage::url($optimizedPath);
        }

        // Buat directory optimized jika belum ada
        $optimizedDir = dirname($fullOptimizedPath);
        if (!is_dir($optimizedDir)) {
            mkdir($optimizedDir, 0755, true);
        }

        // Compress image
        $compressionSuccess = $this->compressImage($fullPath, $fullOptimizedPath, $quality, $maxWidth);

        if ($compressionSuccess) {
            return Storage::url($optimizedPath);
        }

        // Fallback ke original jika compression gagal
        return Storage::url($imagePath);
    }

    /**
     * Core compression function menggunakan native GD
     */
    private function compressImage($sourcePath, $destPath, $quality, $maxWidth)
    {
        try {
            // Get image info
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                return false;
            }

            $mimeType = $imageInfo['mime'];
            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];

            // Create image resource berdasarkan mime type
            switch ($mimeType) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($sourcePath);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($sourcePath);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($sourcePath);
                    break;
                default:
                    return false; // Unsupported format
            }

            if (!$image) {
                return false;
            }

            // Calculate new dimensions
            if ($originalWidth > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = intval($originalHeight * $maxWidth / $originalWidth);
            } else {
                $newWidth = $originalWidth;
                $newHeight = $originalHeight;
            }

            // Create new image canvas
            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // Preserve transparency untuk PNG
            if ($mimeType == 'image/png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            // Resize image
            imagecopyresampled(
                $newImage, $image, 
                0, 0, 0, 0, 
                $newWidth, $newHeight, 
                $originalWidth, $originalHeight
            );

            // Save compressed image
            $success = false;
            switch ($mimeType) {
                case 'image/jpeg':
                    $success = imagejpeg($newImage, $destPath, $quality);
                    break;
                case 'image/png':
                    // PNG quality range 0-9 (0 = highest compression)
                    $pngQuality = 9 - intval($quality / 10);
                    $success = imagepng($newImage, $destPath, $pngQuality);
                    break;
                case 'image/gif':
                    $success = imagegif($newImage, $destPath);
                    break;
            }

            // Clean up memory
            imagedestroy($image);
            imagedestroy($newImage);

            return $success;

        } catch (\Exception $e) {
            Log::error('Image compression failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk compress multiple images
     */
    public function compressMultiple($images, $quality = 50)
    {
        return collect($images)->map(function ($image) use ($quality) {
            if (is_array($image) && isset($image['image_path'])) {
                $image['optimized_url'] = $this->compressForWeb($image['image_path'], $quality);
            }
            return $image;
        });
    }
}