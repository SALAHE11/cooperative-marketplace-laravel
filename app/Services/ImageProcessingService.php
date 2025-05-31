<?php
// app/Services/ImageProcessingService.php

namespace App\Services;

use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ImageProcessingService
{
    /**
     * Process and store product image with enhanced metadata and fallback support
     */
    public static function processProductImage($uploadedFile, $productId, $sortOrder = 0)
    {
        try {
            // Generate unique filename
            $uuid = Str::uuid();
            $extension = $uploadedFile->getClientOriginalExtension();
            $filename = $uuid . '.' . $extension;

            $directory = 'products/' . $productId;
            Storage::makeDirectory('public/' . $directory);

            $originalPath = $directory . '/original_' . $filename;
            $thumbnailPath = $directory . '/thumb_' . $filename;

            // Calculate file hash
            $fileHash = hash_file('sha256', $uploadedFile->path());

            // Check for duplicate
            $existingImage = ProductImage::where('file_hash', $fileHash)
                                        ->where('product_id', $productId)
                                        ->whereNull('deleted_at')
                                        ->first();

            if ($existingImage) {
                throw new \Exception('Cette image a déjà été uploadée pour ce produit.');
            }

            // Get image dimensions and metadata
            $imageInfo = getimagesize($uploadedFile->path());
            $width = $imageInfo[0] ?? null;
            $height = $imageInfo[1] ?? null;
            $mimeType = $imageInfo['mime'] ?? $uploadedFile->getMimeType();

            // Create database record first
            $productImage = ProductImage::create([
                'product_id' => $productId,
                'image_path' => $originalPath,
                'thumbnail_path' => $thumbnailPath,
                'file_size' => $uploadedFile->getSize(),
                'width' => $width,
                'height' => $height,
                'mime_type' => $mimeType,
                'original_filename' => $uploadedFile->getClientOriginalName(),
                'processing_status' => 'processing',
                'file_hash' => $fileHash,
                'sort_order' => $sortOrder,
                'alt_text' => "Image produit " . ($sortOrder + 1),
            ]);

            // Store original file
            $uploadedFile->storeAs('public/' . $directory, 'original_' . $filename);

            // Try advanced image processing with Intervention Image
            $processed = self::processWithInterventionImage($uploadedFile, $originalPath, $thumbnailPath);

            if (!$processed) {
                // Fallback to basic PHP image processing
                $processed = self::processWithBasicPHP($uploadedFile, $originalPath, $thumbnailPath);
            }

            if (!$processed) {
                // Ultimate fallback: just copy the original as thumbnail
                self::copyAsThumb($originalPath, $thumbnailPath);
            }

            // Mark as ready
            $productImage->markAsReady();

            return $productImage;

        } catch (\Exception $e) {
            Log::error('Image processing failed', [
                'error' => $e->getMessage(),
                'product_id' => $productId,
                'file' => $uploadedFile->getClientOriginalName()
            ]);

            // Mark as failed if record was created
            if (isset($productImage)) {
                $productImage->markAsFailed($e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * Process image using Intervention Image library
     */
    private static function processWithInterventionImage($uploadedFile, $originalPath, $thumbnailPath)
    {
        try {
            // Check if Intervention Image is available
            if (!class_exists('Intervention\Image\Facades\Image')) {
                return false;
            }

            $img = \Intervention\Image\Facades\Image::make($uploadedFile->path());

            // Create thumbnail (300x300)
            $thumb = clone $img;
            $thumb->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $thumb->save(storage_path('app/public/' . $thumbnailPath), 85);

            // Optimize original (max 1200px width)
            $img->resize(1200, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save(storage_path('app/public/' . $originalPath), 85);

            return true;

        } catch (\Exception $e) {
            Log::warning('Intervention Image processing failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Process image using basic PHP GD functions
     */
    private static function processWithBasicPHP($uploadedFile, $originalPath, $thumbnailPath)
    {
        try {
            // Check if GD extension is available
            if (!extension_loaded('gd')) {
                return false;
            }

            $imageInfo = getimagesize($uploadedFile->path());
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $type = $imageInfo[2];

            // Create image resource from file
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $source = imagecreatefromjpeg($uploadedFile->path());
                    break;
                case IMAGETYPE_PNG:
                    $source = imagecreatefrompng($uploadedFile->path());
                    break;
                case IMAGETYPE_WEBP:
                    if (function_exists('imagecreatefromwebp')) {
                        $source = imagecreatefromwebp($uploadedFile->path());
                    } else {
                        return false;
                    }
                    break;
                default:
                    return false;
            }

            if (!$source) {
                return false;
            }

            // Create thumbnail
            $thumbWidth = 300;
            $thumbHeight = 300;

            // Calculate proportional dimensions
            if ($width > $height) {
                if ($width > $thumbWidth) {
                    $thumbHeight = ($height * $thumbWidth) / $width;
                } else {
                    $thumbWidth = $width;
                    $thumbHeight = $height;
                }
            } else {
                if ($height > $thumbHeight) {
                    $thumbWidth = ($width * $thumbHeight) / $height;
                } else {
                    $thumbWidth = $width;
                    $thumbHeight = $height;
                }
            }

            // Create thumbnail image
            $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);

            // Preserve transparency for PNG and WEBP
            if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP) {
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
                imagefill($thumb, 0, 0, $transparent);
            }

            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

            // Save thumbnail
            $thumbPath = storage_path('app/public/' . $thumbnailPath);
            switch ($type) {
                case IMAGETYPE_JPEG:
                    imagejpeg($thumb, $thumbPath, 85);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($thumb, $thumbPath, 8);
                    break;
                case IMAGETYPE_WEBP:
                    if (function_exists('imagewebp')) {
                        imagewebp($thumb, $thumbPath, 85);
                    }
                    break;
            }

            // Optimize original if needed
            if ($width > 1200) {
                $newWidth = 1200;
                $newHeight = ($height * $newWidth) / $width;

                $optimized = imagecreatetruecolor($newWidth, $newHeight);

                if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP) {
                    imagealphablending($optimized, false);
                    imagesavealpha($optimized, true);
                    $transparent = imagecolorallocatealpha($optimized, 255, 255, 255, 127);
                    imagefill($optimized, 0, 0, $transparent);
                }

                imagecopyresampled($optimized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                // Save optimized original
                $originalFilePath = storage_path('app/public/' . $originalPath);
                switch ($type) {
                    case IMAGETYPE_JPEG:
                        imagejpeg($optimized, $originalFilePath, 85);
                        break;
                    case IMAGETYPE_PNG:
                        imagepng($optimized, $originalFilePath, 8);
                        break;
                    case IMAGETYPE_WEBP:
                        if (function_exists('imagewebp')) {
                            imagewebp($optimized, $originalFilePath, 85);
                        }
                        break;
                }

                imagedestroy($optimized);
            }

            // Clean up memory
            imagedestroy($source);
            imagedestroy($thumb);

            return true;

        } catch (\Exception $e) {
            Log::warning('Basic PHP image processing failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Ultimate fallback: copy original as thumbnail
     */
    private static function copyAsThumb($originalPath, $thumbnailPath)
    {
        try {
            $originalFilePath = storage_path('app/public/' . $originalPath);
            $thumbFilePath = storage_path('app/public/' . $thumbnailPath);

            if (file_exists($originalFilePath)) {
                copy($originalFilePath, $thumbFilePath);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::warning('Copy fallback failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Batch process multiple images
     */
    public static function processMultipleImages($uploadedFiles, $productId)
    {
        $results = [];
        $errors = [];

        foreach ($uploadedFiles as $index => $file) {
            try {
                $image = self::processProductImage($file, $productId, $index);
                $results[] = $image;
            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage()
                ];
                Log::error('Batch image processing failed', [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'product_id' => $productId
                ]);
            }
        }

        return [
            'success' => $results,
            'errors' => $errors
        ];
    }

    /**
     * Reprocess failed images
     */
    public static function reprocessFailedImages($productId)
    {
        $failedImages = ProductImage::where('product_id', $productId)
                                   ->where('processing_status', 'failed')
                                   ->get();

        $results = [
            'processed' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($failedImages as $image) {
            try {
                $originalPath = storage_path('app/public/' . $image->image_path);

                if (!file_exists($originalPath)) {
                    throw new \Exception('Original file not found');
                }

                // Try to reprocess
                $processed = self::processWithBasicPHP(
                    new \Illuminate\Http\UploadedFile($originalPath, $image->original_filename),
                    $image->image_path,
                    $image->thumbnail_path
                );

                if (!$processed) {
                    self::copyAsThumb($image->image_path, $image->thumbnail_path);
                }

                $image->markAsReady();
                $results['processed']++;

            } catch (\Exception $e) {
                $image->markAsFailed($e->getMessage());
                $results['failed']++;
                $results['errors'][] = [
                    'image_id' => $image->id,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Reorder product images
     */
    public static function reorderImages($productId, $imageIds)
    {
        try {
            DB::beginTransaction();

            foreach ($imageIds as $order => $imageId) {
                ProductImage::where('id', $imageId)
                           ->where('product_id', $productId)
                           ->update(['sort_order' => $order]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Image reordering failed', [
                'error' => $e->getMessage(),
                'product_id' => $productId
            ]);
            return false;
        }
    }

    /**
     * Delete product images directory
     */
    public static function deleteProductImages($productId)
    {
        try {
            $directory = 'products/' . $productId;
            if (Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->deleteDirectory($directory);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to delete product images directory', [
                'error' => $e->getMessage(),
                'product_id' => $productId
            ]);
        }
    }

    /**
     * Clean up orphaned files
     */
    public static function cleanupOrphanedFiles()
    {
        try {
            // This would be run as a scheduled job
            $orphanedImages = ProductImage::onlyTrashed()
                                         ->where('deleted_at', '<', now()->subDays(7))
                                         ->get();

            foreach ($orphanedImages as $image) {
                $image->deleteFiles();
                $image->forceDelete();
            }

            Log::info('Cleaned up orphaned files', [
                'count' => $orphanedImages->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Orphaned files cleanup failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get system image processing capabilities
     */
    public static function getImageProcessingInfo()
    {
        return [
            'gd_available' => extension_loaded('gd'),
            'gd_info' => extension_loaded('gd') ? gd_info() : null,
            'intervention_image_available' => class_exists('Intervention\Image\Facades\Image'),
            'supported_formats' => self::getSupportedFormats(),
            'max_upload_size' => ini_get('upload_max_filesize'),
            'max_post_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit')
        ];
    }

    /**
     * Get supported image formats
     */
    private static function getSupportedFormats()
    {
        $formats = ['jpeg', 'jpg'];

        if (extension_loaded('gd')) {
            $gdInfo = gd_info();

            if ($gdInfo['PNG Support']) {
                $formats[] = 'png';
            }

            if (function_exists('imagewebp') && $gdInfo['WebP Support']) {
                $formats[] = 'webp';
            }
        }

        return $formats;
    }

    /**
     * Validate uploaded file
     */
    public static function validateImageFile($file, $maxSize = 2048)
    {
        $errors = [];

        // Check file size (in KB)
        $fileSizeKB = $file->getSize() / 1024;
        if ($fileSizeKB > $maxSize) {
            $errors[] = "File size ({$fileSizeKB}KB) exceeds maximum allowed size ({$maxSize}KB)";
        }

        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            $errors[] = "File type {$file->getMimeType()} is not supported";
        }

        // Check image dimensions if possible
        if (function_exists('getimagesize')) {
            $imageInfo = getimagesize($file->path());
            if (!$imageInfo) {
                $errors[] = "Invalid image file";
            } else {
                $width = $imageInfo[0];
                $height = $imageInfo[1];

                // Check minimum dimensions
                if ($width < 100 || $height < 100) {
                    $errors[] = "Image dimensions too small (minimum 100x100px)";
                }

                // Check maximum dimensions
                if ($width > 5000 || $height > 5000) {
                    $errors[] = "Image dimensions too large (maximum 5000x5000px)";
                }
            }
        }

        return $errors;
    }
}
