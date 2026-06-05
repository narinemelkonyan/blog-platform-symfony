<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageProcessorService
{
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;
    private const COVER_WIDTH = 1200;
    private const COVER_HEIGHT = 630;
    private const AVATAR_WIDTH = 200;
    private const AVATAR_HEIGHT = 200;
    private const WEBP_QUALITY = 85;

    /**
     * Validates image mime type and file size.
     */
    public function validateImage(UploadedFile $file): void
    {
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \InvalidArgumentException(
                sprintf('File size exceeds maximum allowed size of %dMB.', self::MAX_FILE_SIZE / 1024 / 1024)
            );
        }

        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid file type "%s". Allowed types: %s.', $file->getMimeType(), implode(', ', self::ALLOWED_MIME_TYPES))
            );
        }
    }

    /**
     * Processes article cover: resizes, crops to center, saves as WebP.
     */
    public function processArticleCover(UploadedFile $file, string $uploadDir): string
    {
        return $this->process($file, $uploadDir, self::COVER_WIDTH, self::COVER_HEIGHT);
    }

    public function processAvatar(UploadedFile $file, string $uploadDir): string
    {
        return $this->process($file, $uploadDir, self::AVATAR_WIDTH, self::AVATAR_HEIGHT);
    }

    /**
     * Core processing: validates, resizes, crops and saves as WebP.
     * Returns generated filename.
     */
    private function process(UploadedFile $file, string $uploadDir, int $width, int $height): string
    {
        $this->validateImage($file);

        $filename = bin2hex(random_bytes(16)) . '.webp';
        $filepath = $uploadDir . '/' . $filename;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $source  = $this->createImageFromFile($file);
        $resized = $this->resizeAndCrop($source, $width, $height);

        imagewebp($resized, $filepath, self::WEBP_QUALITY);

        imagedestroy($source);
        imagedestroy($resized);

        return $filename;
    }

    /**
     * Creates a GD image resource from an uploaded file based on its mime type.
     */
    private function createImageFromFile(UploadedFile $file): \GdImage
    {
        return match ($file->getMimeType()) {
            'image/jpeg' => imagecreatefromjpeg($file->getPathname()),
            'image/png'  => imagecreatefrompng($file->getPathname()),
            'image/webp' => imagecreatefromwebp($file->getPathname()),
            default      => throw new \InvalidArgumentException('Unsupported image type.'),
        };
    }

    /**
     * Resizes and center-crops the source image to the target dimensions.
     */
    private function resizeAndCrop(\GdImage $source, int $targetWidth, int $targetHeight): \GdImage
    {
        $sourceWidth  = imagesx($source);
        $sourceHeight = imagesy($source);

        $sourceRatio = $sourceWidth / $sourceHeight;
        $targetRatio = $targetWidth / $targetHeight;

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth  = (int) ($sourceHeight * $targetRatio);
            $cropX      = (int) (($sourceWidth - $cropWidth) / 2);
            $cropY      = 0;
        } else {
            $cropWidth  = $sourceWidth;
            $cropHeight = (int) ($sourceWidth / $targetRatio);
            $cropX      = 0;
            $cropY      = (int) (($sourceHeight - $cropHeight) / 2);
        }

        $resized = imagecreatetruecolor($targetWidth, $targetHeight);

        imagecopyresampled(
            $resized, $source,
            0, 0,
            $cropX, $cropY,
            $targetWidth, $targetHeight,
            $cropWidth, $cropHeight
        );

        return $resized;
    }

    /**
     * Delete file
     */
    public function deleteFile(?string $filename, string $uploadDir): void
    {
        if ($filename === null) {
            return;
        }

        $filepath = $uploadDir . '/' . $filename;

        if (!file_exists($filepath)) {
            return;
        }

        unlink($filepath);
    }
}
