<?php

namespace App\Helpers\Utils;

use Illuminate\Support\Facades\Storage;

class StorageHelper
{
    public const TMP_DISK_NAME = 'tmp';
    public const CACHE_DISK_NAME = 'cache';
    public const CLOUD_DISK_NAME = 'google';

    /**
     * Move a file from disk to disk
     * @param string $filePath
     * @param string $fromDiskName
     * @param string $toDiskName
     * @param string|null $targetFolder
     * @return false|string
     */
    public static function moveFile(string $filePath, string $fromDiskName, string $toDiskName, string $targetFolder = null): bool|string
    {
        try {
            $originDisk = Storage::disk($fromDiskName);
            $destinationDisk = Storage::disk($toDiskName);
            if (!$originDisk->exists($filePath)) throw new \Exception('invalid file path');
            $newFilePath = $targetFolder ? implode('/', [$targetFolder, $filePath]) : $targetFolder; // TODO: change file name!
            $destinationDisk->writeStream($newFilePath, $originDisk->readStream($filePath));
            $originDisk->delete($filePath);
            return $newFilePath;
        } catch (\Exception $ex) {
            return false;
        }
    }


    /**
     * Remove resource file from disk
     * @param string $fromDiskName
     * @param string $filePath
     * @return bool
     */
    public static function removeFile(string $fromDiskName, string $filePath): bool
    {
        try {
            $originDisk = Storage::disk($fromDiskName);
            $originDisk->delete($filePath);
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * Move a file from disk to disk
     * @param string $filePath
     * @param string $fromDiskName
     * @param string $toDiskName
     * @param string|null $targetFolder
     * @return false|string
     */
    public static function copyFile(string $filePath, string $fromDiskName, string $toDiskName, string $targetFolder = null): bool|string
    {
        try {
            $originDisk = Storage::disk($fromDiskName);
            $destinationDisk = Storage::disk($toDiskName);
            if (!$originDisk->exists($filePath)) throw new \Exception('invalid file path');
            $newFilePath = $targetFolder ? implode('/', [$targetFolder, $filePath]) : $filePath; // TODO: change file name!
            $ret = $destinationDisk->writeStream($newFilePath, $originDisk->readStream($filePath));
            return $newFilePath;
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * Try to cache files from clouds
     * @param array $filePaths
     * @return bool
     */
    public static function cacheFromCloud(array $filePaths, bool $ignoreError = true, bool $force = false)
    {
        foreach ($filePaths as $item) {
            try {
                $disk = Storage::disk(StorageHelper::CACHE_DISK_NAME);
                if (!$disk->exists($item) || $force) {
                    StorageHelper::copyFile($item, StorageHelper::CLOUD_DISK_NAME, StorageHelper::CACHE_DISK_NAME);
                }
            } catch (\Exception $ex) {
                if (!$ignoreError) return false;
            }
        }
        return true;
    }

    /**
     * return cached file path
     * @param string $tail
     * @return string
     */
    public static function getCachedImagePathIfPossible(string $tail, bool $force = false): string
    {
        $disk = Storage::disk(StorageHelper::CACHE_DISK_NAME);
        $filePath = null;
        if (!$disk->exists($tail) || $force) {
            if ($cachePath = StorageHelper::copyFile($tail, StorageHelper::CLOUD_DISK_NAME, StorageHelper::CACHE_DISK_NAME)) {
                $filePath = $disk->path($cachePath);
            }
        } else {
            $filePath = $disk->path($tail);
        }
        return $filePath ?? resource_path() . '/images/default/default-thumbnail.jpg';
    }

    /**
     * storage image
     * png, jpg, jpeg
     */
    public static function storageImage(
        string $folder,
        string $data,
        string $diskName = self::CLOUD_DISK_NAME,
        string $rem = ''
    ) {
        try {
            // TODO: Fix: Save image to cache folder, add its path to a queue to upload slowly
            list($extension, $content) = explode(';', $data);
            $tmpExtension = explode('/', $extension);
            // Check mimetype
            if (!in_array($tmpExtension[1], array('png', 'jpg', 'jpeg'))) throw new \Exception('invalid image');
            // preg_match('/.([0-9]+) /', microtime(), $m);
            // $fileName = sprintf(uuid_create() . '%s%s.%s', date('YmdHis'), $m[1], $tmpExtension[1]);
            $fileName = sprintf(uuid_create() . '.%s', $tmpExtension[1]);
            $content = explode(',', $content)[1];
            $storage = Storage::disk($diskName);

            $checkDirectory = $storage->exists($folder);
            if (!$checkDirectory) {
                $storage->makeDirectory($folder);
            }
            $storage->put($folder . '/' . $fileName, base64_decode($content), 'public');
            // TODO: Should add to a queue table to clear later
            if ($rem != '' && Storage::disk($diskName)->exists($rem)) {
                Storage::disk($diskName)->delete($rem);
            }
            return $folder . '/' . $fileName;
        } catch (\Exception $e) {
            return false;
        }
    }
}
