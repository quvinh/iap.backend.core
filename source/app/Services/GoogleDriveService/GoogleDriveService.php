<?php

namespace App\Services\GoogleDriveService;

use App\Helpers\Utils\StorageHelper;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoogleDriveService
{
    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $this->client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        $this->client->addScope(Google_Service_Drive::DRIVE);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');

        $this->service = new Google_Service_Drive($this->client);
    }

    public function uploadFile($filePath, $fileName)
    {
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($fileName);
        // $file->setParents([env('GOOGLE_DRIVE_FOLDER')]);
        $file->setParents(['1jV8lLMJWhaZiLBN_JIqkOn__kiRLlKFZ']);
        $fileData = File::get(Storage::disk(StorageHelper::TMP_DISK_NAME)->path($filePath));
        $result = $this->service->files->create($file, [
            'data' => $fileData,
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'uploadType' => 'multipart'
        ]);

        return $result;
    }

    public function convertToSpreadsheet($fileId)
    {
        $file = new Google_Service_Drive_DriveFile();
        $file->setMimeType('application/vnd.google-apps.spreadsheet');

        $result = $this->service->files->update($fileId, $file, [
            'fields' => 'id, mimeType'
        ]);

        return $result;
    }
}
