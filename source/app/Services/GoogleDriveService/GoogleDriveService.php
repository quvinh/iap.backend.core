<?php

namespace App\Services\GoogleDriveService;

use Exception;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_Permission;
use Google_Service_Sheets;
use Google_Service_Script;
use Google_Service_Script_CreateProjectRequest;
use Google_Service_Script_ScriptFile;
use Google_Service_Script_Content;
use Google_Service_Script_ExecutionRequest;
use App\Helpers\Responses\ApiResponse;
use App\Helpers\Utils\StorageHelper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoogleDriveService
{
    protected $client;
    protected $service;
    protected $scriptService;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $this->client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        $this->client->addScope(Google_Service_Drive::DRIVE);
        $this->client->addScope(Google_Service_Script::DRIVE);
        $this->client->addScope(Google_Service_Script::SCRIPT_PROJECTS);
        $this->client->addScope(Google_Service_Sheets::SPREADSHEETS);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');

        $this->service = new Google_Service_Drive($this->client);
        $this->scriptService = new Google_Service_Script($this->client);
    }

    /**
     * Upload file to google drive
     * @param string $filePath
     * @param string $fileName
     */
    public function uploadFile($filePath, $fileName, $emails = [], $disk = StorageHelper::TMP_DISK_NAME)
    {
        $folderName = 'excel/' . date('Ymd');
        $folderId = $this->getOrCreateFolder($folderName);

        $file = new Google_Service_Drive_DriveFile();
        $file->setName($fileName);
        $file->setParents([$folderId]);
        $fileData = File::get(Storage::disk($disk)->path($filePath));
        $result = $this->service->files->create($file, [
            'data' => $fileData,
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'uploadType' => 'multipart'
        ]);

        $convertedFile = $this->convertToSpreadsheet($result->id);

        if ($convertedFile && !empty($emails)) {
            foreach ($emails as $email) {
                $this->addPermissions($convertedFile->id, $email, 'writer'); // 'writer' for edit, 'reader' for view
            }
        }

        // $scriptProject = $this->createAppsScriptProject($convertedFile->id);
        // $this->addAppsScriptToProject($scriptProject->scriptId, $this->getSampleScript());

        // Create trigger
        // $this->runAppsScriptFunction($scriptProject->scriptId, "createOnEditTrigger");
        // $scriptId = '1Lz8rsKvsEO6Rb-JPTvCq9zxoCl3PZOrVgxx1lh1uy0qHRgsfo-QKQI1e';
        // $this->executeScript($scriptProject->scriptId, 'createOnEditTrigger', [$convertedFile->id]);

        return [
            'file' => $convertedFile,
            // 'script_id' => $scriptProject->scriptId,
            // 'function_name' => 'onLoadSold',
            'url' => $this->generateFileUrl($convertedFile->id)
        ];
    }

    private function generateFileUrl($fileId)
    {
        return 'https://docs.google.com/spreadsheets/d/' . $fileId . '/edit';
    }

    /**
     * Convert to spreadsheet
     * @param string $fileId
     */
    public function convertToSpreadsheet($fileId)
    {
        try {
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'mimeType' => 'application/vnd.google-apps.spreadsheet'
            ]);
            $result = $this->service->files->copy($fileId, $fileMetadata);

            // Delete the original XLSX file after conversion
            $this->service->files->delete($fileId);

            return $result;
        } catch (Exception $ex) {
            $response = ApiResponse::v1();
            return $response->fail([
                'message' => 'Error converting file: ' . $ex->getMessage(),
                'code' => $ex->getCode()
            ]);
        }
    }

    /**
     * Get or create folder
     * @param string $folderName
     * @return string
     */
    private function getOrCreateFolder($folderName)
    {
        $parentFolderId = env('GOOGLE_DRIVE_FOLDER_ID'); // 1jV8lLMJWhaZiLBN_JIqkOn__kiRLlKFZ
        $folders = explode('/', $folderName);
        $currentFolderId = $parentFolderId;

        foreach ($folders as $folder) {
            $currentFolderId = $this->getOrCreateSubFolder($currentFolderId, $folder);
        }

        return $currentFolderId;
    }

    /**
     * Get or create sub folder
     * @param string $parentFolderId
     * @param string $folderName
     * @return string $id
     */
    private function getOrCreateSubFolder($parentFolderId, $folderName)
    {
        $query = "name = '$folderName' and mimeType = 'application/vnd.google-apps.folder' and '$parentFolderId' in parents";
        $response = $this->service->files->listFiles([
            'q' => $query,
            'spaces' => 'drive',
            'fields' => 'files(id, name)',
            'pageSize' => 1,
        ]);

        if (count($response->files) > 0) {
            return $response->files[0]->id;
        } else {
            $folder = new Google_Service_Drive_DriveFile();
            $folder->setName($folderName);
            $folder->setMimeType('application/vnd.google-apps.folder');
            $folder->setParents([$parentFolderId]);

            $result = $this->service->files->create($folder, [
                'fields' => 'id'
            ]);
            return $result->id;
        }
    }

    /**
     * Add permissions
     * @param string $fileId
     * @param string $email
     * @param string $role
     */
    public function addPermissions($fileId, $email, $role)
    {
        try {
            $permission = new Google_Service_Drive_Permission();
            $permission->setType('user');
            $permission->setRole($role);
            $permission->setEmailAddress($email);

            $this->service->permissions->create($fileId, $permission);
        } catch (Exception $ex) {
            $response = ApiResponse::v1();
            return $response->fail([
                'message' => 'Error adding permission: ' . $ex->getMessage(),
                'code' => $ex->getCode()
            ]);
        }
    }

    public function createAppsScriptProject($spreadsheetId)
    {
        $createProjectRequest = new Google_Service_Script_CreateProjectRequest();
        $createProjectRequest->setTitle('Script_' . date('His'));
        $createProjectRequest->setParentId($spreadsheetId);

        return $this->scriptService->projects->create($createProjectRequest);
    }

    public function addAppsScriptToProject($scriptId, $scriptContent)
    {
        $file = new Google_Service_Script_ScriptFile();
        $file->setName('Code');
        $file->setType('SERVER_JS');
        $file->setSource($scriptContent);

        // Add manifest appsscript.json
        $manifestFile = new Google_Service_Script_ScriptFile();
        $manifestFile->setName('appsscript');
        $manifestFile->setType('JSON');
        $manifestFile->setSource(json_encode([
            'timeZone' => 'Asia/Ho_Chi_Minh',
            'exceptionLogging' => 'STACKDRIVER'
        ]));
        // $manifestFile->setSource(json_encode([
        //     'timeZone' => 'Asia/Ho_Chi_Minh',
        //     'dependencies' => [
        //         'enabledAdvancedServices' => [
        //             [
        //                 'userSymbol' => 'Sheets',
        //                 'version' => 'v4',
        //                 'serviceId' => 'sheets'
        //             ]
        //         ]
        //     ],
        //     'exceptionLogging' => 'STACKDRIVER',
        //     'runtimeVersion' => 'V8',
        //     'oauthScopes' => [
        //         'https://www.googleapis.com/auth/drive',
        //         'https://www.googleapis.com/auth/drive.file',
        //         'https://www.googleapis.com/auth/spreadsheets',
        //         'https://www.googleapis.com/auth/script.scriptapp',
        //         'https://www.googleapis.com/auth/script.projects'
        //     ]
        // ]));

        $content = new Google_Service_Script_Content();
        $content->setFiles([$file, $manifestFile]);

        return $this->scriptService->projects->updateContent($scriptId, $content);
    }


    private function getSampleScript()
    {
        return file_get_contents(resource_path('scripts/script.js'));
    }

    // public function runAppsScriptFunction($scriptId, $functionName)
    // {
    //     $request = new Google_Service_Script_ExecutionRequest();
    //     $request->setFunction($functionName);

    //     return $this->scriptService->scripts->run($scriptId, $request);
    // }

    public function executeScript($scriptId, $functionName, $parameters = [])
    {
        $request = new Google_Service_Script_ExecutionRequest([
            'function' => $functionName,
            'parameters' => $parameters,
        ]);

        return $this->scriptService->scripts->run($scriptId, $request);
    }

    public function runAppsScriptFunction(array $params): array
    {
        // id: AKfycbwoio8lsxDln6GgsuukHE46UsuOqORhTHZ8qXHjz40B93OXDEmOSEMqzuCmQjVCIiYB
        // $url = "https://script.google.com/macros/s/AKfycbwoio8lsxDln6GgsuukHE46UsuOqORhTHZ8qXHjz40B93OXDEmOSEMqzuCmQjVCIiYB/exec";
        $url = "https://script.google.com/macros/s/AKfycbwbYwf_V9V_6TlzEsy3jf4vqU0sZTtwAjY8_NK1gRE/dev";
        $token = env('GOOGLE_DRIVE_ACCESS_TOKEN');
        $function = $params['function'] ?? null;
        $script_id = $params['script_id'] ?? null;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get($url, [
            'function' => $function,
            'script_id' => $script_id,
        ]);

        try {
            if ($response->status() == 200) {
                $body = json_decode($response->body());
                return (array)$body;
            }
        } catch (\Exception $ex) {
            return [
                'result' => $response->successful() ?? null,
                'status' => $response->status() ?? null,
                'body' => $response->body() ?? null,
                'message' => ['error' => $ex->getMessage()],
            ];
        }

        return [
            'result' => $response->successful() ?? null,
            'status' => $response->status() ?? null,
            'body' => $response->body() ?? null,
        ];
    }
}
