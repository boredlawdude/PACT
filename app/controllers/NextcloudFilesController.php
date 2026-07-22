<?php
declare(strict_types=1);

require_once APP_ROOT . '/app/services/NextcloudWebDavService.php';

class NextcloudFilesController
{
    private NextcloudWebDavService $service;

    public function __construct()
    {
        $this->service = new NextcloudWebDavService();
    }

    public function index(): void
    {
        $path = (string)($_GET['path'] ?? '');
        $error = null;
        $isConfigured = $this->service->isConfigured();
        $listing = [
            'current_path' => '',
            'parent_path' => '',
            'entries' => [],
            'web_url' => $this->service->buildWebUiUrl(''),
        ];

        if ($isConfigured) {
            try {
                $listing = $this->service->listDirectory($path);
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }

        require APP_ROOT . '/app/views/nextcloud_files/index.php';
    }

    public function download(): void
    {
        $path = (string)($_GET['path'] ?? '');

        try {
            $file = $this->service->downloadFile($path);
        } catch (Throwable $e) {
            http_response_code(400);
            echo 'Download failed: ' . h($e->getMessage());
            return;
        }

        header('Content-Type: ' . $file['content_type']);
        if (!empty($file['content_length'])) {
            header('Content-Length: ' . (string)$file['content_length']);
        }
        $safeName = str_replace(['"', "\r", "\n"], '_', (string)$file['name']);
        header('Content-Disposition: attachment; filename="' . $safeName . '"');

        echo $file['body'];
        exit;
    }
}
