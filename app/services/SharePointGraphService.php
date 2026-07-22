<?php
declare(strict_types=1);

final class SharePointGraphService
{
    private const GRAPH_BASE_URL = 'https://graph.microsoft.com/v1.0';

    private ?string $accessToken = null;

    public function isConfigured(): bool
    {
        return $this->config('MS_TENANT_ID') !== ''
            && $this->config('MS_CLIENT_ID') !== ''
            && $this->config('MS_CLIENT_SECRET') !== ''
            && $this->config('MS_SHAREPOINT_DRIVE_ID') !== ''
            && $this->config('MS_SHAREPOINT_BASE_FOLDER') !== '';
    }

    public function uploadContractDocument(int $contractId, array $document): array
    {
        $filePath = trim((string)($document['file_path'] ?? ''));
        $fileName = trim((string)($document['file_name'] ?? ''));

        if ($filePath === '' || $fileName === '') {
            throw new RuntimeException('Document path or file name is missing.');
        }

        $absolutePath = $this->resolveAbsolutePath($filePath);
        if (!is_file($absolutePath)) {
            throw new RuntimeException('Document file not found on disk.');
        }

        $folderPath = $this->buildContractFolderPath($contractId);
        $this->ensureFolderPath($folderPath);

        $targetPath = $folderPath . '/' . $fileName;
        $encodedPath = $this->encodePath($targetPath);

        $response = $this->request(
            'PUT',
            self::GRAPH_BASE_URL . '/drives/' . rawurlencode($this->config('MS_SHAREPOINT_DRIVE_ID')) . '/root:/' . $encodedPath . ':/content',
            file_get_contents($absolutePath),
            [
                'Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]
        );

        return $this->mapDriveItem($response);
    }

    public function fetchDocumentMetadata(string $itemId): array
    {
        if ($itemId === '') {
            throw new RuntimeException('SharePoint item ID is required.');
        }

        $response = $this->request(
            'GET',
            self::GRAPH_BASE_URL . '/drives/' . rawurlencode($this->config('MS_SHAREPOINT_DRIVE_ID')) . '/items/' . rawurlencode($itemId)
                . '?$select=id,name,webUrl,eTag,lastModifiedDateTime,lastModifiedBy,parentReference'
        );

        return $this->mapDriveItem($response);
    }

    public function buildDesktopEditUrl(string $webUrl): string
    {
        return $webUrl === '' ? '' : 'ms-word:ofe|u|' . $webUrl;
    }

    public function buildContractFolderPath(int $contractId): string
    {
        $baseFolder = trim($this->config('MS_SHAREPOINT_BASE_FOLDER'), '/');
        $yearFolder = date('Y');

        return $baseFolder . '/' . $yearFolder . '/contract-' . $contractId;
    }

    private function mapDriveItem(array $item): array
    {
        $webUrl = (string)($item['webUrl'] ?? '');

        return [
            'storage_provider' => 'sharepoint',
            'external_document_id' => (string)($item['id'] ?? ''),
            'external_drive_id' => $this->config('MS_SHAREPOINT_DRIVE_ID'),
            'external_site_id' => $this->config('MS_SHAREPOINT_SITE_ID'),
            'external_web_url' => $webUrl,
            'external_word_url' => $this->buildDesktopEditUrl($webUrl),
            'external_path' => (string)($item['parentReference']['path'] ?? ''),
            'external_version' => (string)($item['eTag'] ?? ''),
            'external_modified_at' => (string)($item['lastModifiedDateTime'] ?? ''),
            'external_modified_by' => (string)($item['lastModifiedBy']['user']['displayName'] ?? ''),
        ];
    }

    private function resolveAbsolutePath(string $relativePath): string
    {
        if (str_starts_with($relativePath, '/')) {
            return $relativePath;
        }

        return APP_ROOT . '/' . ltrim($relativePath, '/');
    }

    private function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', trim($path, '/'))));
    }

    private function request(string $method, string $url, ?string $body = null, array $extraHeaders = []): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('SharePoint integration is not configured.');
        }

        if (!function_exists('curl_init')) {
            throw new RuntimeException('cURL extension is required for SharePoint integration.');
        }

        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Could not initialize cURL.');
        }

        $headers = array_merge([
            'Authorization: Bearer ' . $this->getAccessToken(),
            'Accept: application/json',
        ], $extraHeaders);

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 45,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException('Graph request failed: ' . $error);
        }

        $decoded = json_decode($raw, true);
        if ($status < 200 || $status >= 300) {
            $message = is_array($decoded)
                ? (string)($decoded['error']['message'] ?? $raw)
                : $raw;
            throw new RuntimeException('Graph request failed: ' . $message);
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function ensureFolderPath(string $folderPath): void
    {
        $segments = array_values(array_filter(explode('/', trim($folderPath, '/')), static fn(string $segment): bool => $segment !== ''));
        $parentPath = '';

        foreach ($segments as $segment) {
            $currentPath = ltrim($parentPath . '/' . $segment, '/');
            if (!$this->pathExists($currentPath)) {
                $this->createFolder($parentPath, $segment);
            }
            $parentPath = $currentPath;
        }
    }

    private function pathExists(string $path): bool
    {
        try {
            $this->request(
                'GET',
                self::GRAPH_BASE_URL . '/drives/' . rawurlencode($this->config('MS_SHAREPOINT_DRIVE_ID')) . '/root:/' . $this->encodePath($path)
            );
            return true;
        } catch (RuntimeException $e) {
            $message = $e->getMessage();
            if (str_contains($message, 'itemNotFound') || str_contains($message, 'Resource not found')) {
                return false;
            }
            throw $e;
        }
    }

    private function createFolder(string $parentPath, string $folderName): void
    {
        $url = self::GRAPH_BASE_URL . '/drives/' . rawurlencode($this->config('MS_SHAREPOINT_DRIVE_ID')) . '/root';
        if ($parentPath !== '') {
            $url .= ':/' . $this->encodePath($parentPath) . ':';
        }
        $url .= '/children';

        $payload = json_encode([
            'name' => $folderName,
            'folder' => new stdClass(),
            '@microsoft.graph.conflictBehavior' => 'replace',
        ], JSON_THROW_ON_ERROR);

        $this->request(
            'POST',
            $url,
            $payload,
            ['Content-Type: application/json']
        );
    }

    private function getAccessToken(): string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        $tokenUrl = 'https://login.microsoftonline.com/' . rawurlencode($this->config('MS_TENANT_ID')) . '/oauth2/v2.0/token';
        $postBody = http_build_query([
            'grant_type' => 'client_credentials',
            'client_id' => $this->config('MS_CLIENT_ID'),
            'client_secret' => $this->config('MS_CLIENT_SECRET'),
            'scope' => 'https://graph.microsoft.com/.default',
        ]);

        $ch = curl_init($tokenUrl);
        if ($ch === false) {
            throw new RuntimeException('Could not initialize token request.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_POSTFIELDS => $postBody,
        ]);

        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException('Token request failed: ' . $error);
        }

        $decoded = json_decode($raw, true);
        if ($status < 200 || $status >= 300 || !is_array($decoded) || empty($decoded['access_token'])) {
            $message = is_array($decoded)
                ? (string)($decoded['error_description'] ?? $decoded['error'] ?? $raw)
                : $raw;
            throw new RuntimeException('Token request failed: ' . $message);
        }

        $this->accessToken = (string)$decoded['access_token'];

        return $this->accessToken;
    }

    private function config(string $key): string
    {
        $value = $_ENV[$key] ?? '';
        return is_string($value) ? trim($value) : '';
    }
}