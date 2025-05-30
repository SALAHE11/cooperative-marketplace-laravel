<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class ImageProcessingService
{
    /**
     * Process and store product image with multiple sizes
     */
    public static function processProductImage($image, $productId, $index = 0)
    {
        try {
            $directory = 'products/' . $productId;
            Storage::makeDirectory('public/' . $directory);

            $filename = time() . '_' . $index . '_' . $image->getClientOriginalName();
            $originalPath = $directory . '/original_' . $filename;
            $thumbnailPath = $directory . '/thumb_' . $filename;

            // Store original
            $image->storeAs('public/' . $directory, 'original_' . $filename);

            // Create thumbnail if Intervention Image is available
            if (class_exists('Intervention\Image\Facades\Image')) {
                $img = Image::make($image->path());

                // Create thumbnail (300x300)
                $thumb = clone $img;
                $thumb->resize(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $thumb->save(storage_path('app/public/' . $thumbnailPath));

                // Optimize original (max 1200px width)
                $img->resize(1200, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $img->save(storage_path('app/public/' . $originalPath), 85);
            } else {
                // Fallback: just copy the file
                copy($image->path(), storage_path('app/public/' . $thumbnailPath));
            }

            return [
                'original_path' => $originalPath,
                'thumbnail_path' => $thumbnailPath
            ];

        } catch (\Exception $e) {
            Log::error('Image processing failed', [
                'error' => $e->getMessage(),
                'product_id' => $productId
            ]);
            throw $e;
        }
    }

    /**
     * Delete product images
     */
    public static function deleteProductImages($productId)
    {
        try {
            $directory = 'products/' . $productId;
            if (Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->deleteDirectory($directory);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to delete product images', [
                'error' => $e->getMessage(),
                'product_id' => $productId
            ]);
        }
    }
}
