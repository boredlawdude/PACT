<?php
declare(strict_types=1);

final class NextcloudWebDavService
{
    private const DEFAULT_BASE_URL = 'https://files.schifano.com';
    private const DEFAULT_ROOT_TEMPLATE = '/remote.php/dav/files/{username}/';

    /** @var array{username:string,password:string}|null */
    private ?array $resolvedCredentials = null;

    public function isConfigured(): bool
    {
        $credentials = $this->credentials();
        return $credentials['username'] !== '' && $credentials['password'] !== '';
    }

    public function getBaseUrl(): string
    {
        $value = trim((string)($_ENV['NEXTCLOUD_BASE_URL'] ?? self::DEFAULT_BASE_URL));
        return rtrim($value, '/');
    }

    public function listDirectory(string $path = ''): array
    {
        $path = $this->normalizePath($path);

        $body = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<d:propfind xmlns:d="DAV:">
  <d:prop>
    <d:resourcetype/>
    <d:getcontentlength/>
    <d:getlastmodified/>
    <d:getcontenttype/>
  </d:prop>
</d:propfind>
XML;

        $response = $this->request('PROPFIND', $path, $body, [
            'Depth: 1',
            'Content-Type: application/xml; charset=utf-8',
        ], [207]);

        $entries = $this->parsePropfindResponse($response['body'], $path);
        usort($entries, static function (array $a, array $b): int {
            if ($a['is_dir'] !== $b['is_dir']) {
                return $a['is_dir'] ? -1 : 1;
            }
            return strcasecmp((string)$a['name'], (string)$b['name']);
        });

        return [
            'current_path' => $path,
            'parent_path' => $this->parentPath($path),
            'entries' => $entries,
            'web_url' => $this->buildWebUiUrl($path),
        ];
    }

    public function downloadFile(string $path): array
    {
        $path = $this->normalizePath($path);
        if ($path === '') {
            throw new RuntimeException('Choose a file to download.');
        }

        $response = $this->request('GET', $path, null, [], [200]);
        $contentType = $response['headers']['content-type'] ?? 'application/octet-stream';
        $contentLength = (int)($response['headers']['content-length'] ?? 0);

        return [
            'path' => $path,
            'name' => basename($path),
            'content_type' => $contentType,
            'content_length' => $contentLength,
            'body' => $response['body'],
        ];
    }

    public function buildWebUiUrl(string $path = ''): string
    {
        $base = $this->getBaseUrl();
        $dir = '/' . ltrim($this->normalizePath($path), '/');
        if ($dir === '/') {
            $dir = '';
        }

        return $base . '/apps/files/?dir=' . rawurlencode($dir);
    }

    private function request(string $method, string $path, ?string $body, array $headers, array $expectedStatusCodes): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Nextcloud is not configured. Save nextcloud_username and nextcloud_password on your profile, or set NEXTCLOUD_USERNAME and NEXTCLOUD_APP_PASSWORD in .env.');
        }

        if (!function_exists('curl_init')) {
            throw new RuntimeException('cURL extension is required to access Nextcloud.');
        }

        $url = $this->buildWebDavUrl($path);
        $responseHeaders = [];

        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Could not initialize cURL request.');
        }

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_ENCODING => '',
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $this->username() . ':' . $this->appPassword(),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$responseHeaders): int {
                $trimmed = trim($line);
                if ($trimmed === '' || !str_contains($trimmed, ':')) {
                    return strlen($line);
                }
                [$name, $value] = explode(':', $trimmed, 2);
                $responseHeaders[strtolower(trim($name))] = trim($value);
                return strlen($line);
            },
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);

        if ($raw === false) {
            throw new RuntimeException('Nextcloud request failed: ' . $error);
        }

        if (!in_array($status, $expectedStatusCodes, true)) {
            $message = trim(strip_tags((string)$raw));
            if ($message === '') {
                $message = 'HTTP ' . $status;
            }
            throw new RuntimeException('Nextcloud request failed (' . $status . '): ' . $message);
        }

        return [
            'status' => $status,
            'headers' => $responseHeaders,
            'body' => (string)$raw,
        ];
    }

    private function parsePropfindResponse(string $xml, string $currentPath): array
    {
        $xml = $this->normalizeXmlPayload($xml);
        if ($xml === '') {
            throw new RuntimeException('Nextcloud returned an empty directory response.');
        }

        if (stripos($xml, '<!doctype html') !== false || stripos($xml, '<html') !== false) {
            throw new RuntimeException('Nextcloud returned HTML instead of WebDAV XML. Check the WebDAV root path and credentials.');
        }

        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
        if (!$doc) {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $loaded = @$dom->loadXML($xml, LIBXML_NONET | LIBXML_NOCDATA);
            if ($loaded) {
                $doc = simplexml_import_dom($dom);
            }
        }

        if (!$doc) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $firstError = trim((string)($errors[0]->message ?? 'Unknown XML parse error'));
            $fallbackEntries = $this->parsePropfindResponseFallback($xml, $currentPath);
            if (!empty($fallbackEntries)) {
                return $fallbackEntries;
            }
            $hrefOnlyEntries = $this->parsePropfindHrefsOnly($xml, $currentPath);
            if (!empty($hrefOnlyEntries)) {
                return $hrefOnlyEntries;
            }
            $preview = $this->responsePreview($xml);
            throw new RuntimeException('Could not parse Nextcloud directory response: ' . $firstError . '. Response preview: ' . $preview);
        }
        libxml_clear_errors();

        $doc->registerXPathNamespace('d', 'DAV:');
        $responses = $doc->xpath('/d:multistatus/d:response');
        if (!is_array($responses)) {
            return [];
        }

        $entries = [];
        $rootPath = $this->webDavRootPath();

        foreach ($responses as $response) {
            $hrefNode = $response->xpath('d:href');
            if (!is_array($hrefNode) || empty($hrefNode)) {
                continue;
            }

            $href = urldecode((string)$hrefNode[0]);
            $hrefPath = (string)(parse_url($href, PHP_URL_PATH) ?? '');
            if (!str_starts_with($hrefPath, $rootPath)) {
                continue;
            }

            $relative = trim(substr($hrefPath, strlen($rootPath)), '/');
            if ($relative === $currentPath) {
                continue;
            }

            $name = basename($relative);
            if ($name === '' || $name === '.' || $name === '..') {
                continue;
            }

            $isDir = !empty($response->xpath('d:propstat/d:prop/d:resourcetype/d:collection'));
            $sizeNode = $response->xpath('d:propstat/d:prop/d:getcontentlength');
            $modifiedNode = $response->xpath('d:propstat/d:prop/d:getlastmodified');
            $mimeNode = $response->xpath('d:propstat/d:prop/d:getcontenttype');

            $size = (is_array($sizeNode) && !empty($sizeNode)) ? trim((string)$sizeNode[0]) : '';
            $modifiedRaw = (is_array($modifiedNode) && !empty($modifiedNode)) ? trim((string)$modifiedNode[0]) : '';
            $modifiedAt = null;
            if ($modifiedRaw !== '') {
                $ts = strtotime($modifiedRaw);
                if ($ts !== false) {
                    $modifiedAt = gmdate('Y-m-d H:i:s', $ts);
                }
            }

            $entries[] = [
                'name' => $name,
                'path' => $relative,
                'is_dir' => $isDir,
                'size' => ($size !== '' && ctype_digit($size)) ? (int)$size : null,
                'modified_at' => $modifiedAt,
                'mime_type' => (is_array($mimeNode) && !empty($mimeNode)) ? trim((string)$mimeNode[0]) : '',
            ];
        }

        return $entries;
    }

    /**
     * Tolerant fallback parser for slightly malformed XML payloads.
     * Uses lightweight tag extraction to recover directory listings.
     */
    private function parsePropfindResponseFallback(string $xml, string $currentPath): array
    {
        if (!preg_match_all('/<d:response\b.*?<\/d:response>/si', $xml, $blocks)) {
            return [];
        }

        $entries = [];
        $rootPath = $this->webDavRootPath();

        foreach ($blocks[0] as $block) {
            if (!preg_match('/<d:href>(.*?)<\/d:href>/si', $block, $hrefMatch)) {
                continue;
            }

            $href = html_entity_decode(trim($hrefMatch[1]), ENT_QUOTES | ENT_XML1, 'UTF-8');
            $href = urldecode($href);
            $hrefPath = (string)(parse_url($href, PHP_URL_PATH) ?? '');
            if (!str_starts_with($hrefPath, $rootPath)) {
                continue;
            }

            $relative = trim(substr($hrefPath, strlen($rootPath)), '/');
            if ($relative === $currentPath) {
                continue;
            }

            $name = basename($relative);
            if ($name === '' || $name === '.' || $name === '..') {
                continue;
            }

            $isDir = (bool)preg_match('/<d:resourcetype>\s*<d:collection\s*\/?\s*>/si', $block);

            $size = null;
            if (preg_match('/<d:getcontentlength>(\d+)<\/d:getcontentlength>/si', $block, $sizeMatch)) {
                $size = (int)$sizeMatch[1];
            }

            $modifiedAt = null;
            if (preg_match('/<d:getlastmodified>(.*?)<\/d:getlastmodified>/si', $block, $modMatch)) {
                $modifiedRaw = html_entity_decode(trim($modMatch[1]), ENT_QUOTES | ENT_XML1, 'UTF-8');
                $ts = strtotime($modifiedRaw);
                if ($ts !== false) {
                    $modifiedAt = gmdate('Y-m-d H:i:s', $ts);
                }
            }

            $mimeType = '';
            if (preg_match('/<d:getcontenttype>(.*?)<\/d:getcontenttype>/si', $block, $mimeMatch)) {
                $mimeType = html_entity_decode(trim($mimeMatch[1]), ENT_QUOTES | ENT_XML1, 'UTF-8');
            }

            $entries[] = [
                'name' => $name,
                'path' => $relative,
                'is_dir' => $isDir,
                'size' => $size,
                'modified_at' => $modifiedAt,
                'mime_type' => $mimeType,
            ];
        }

        return $entries;
    }

    /**
     * Last-resort parser: extract only href nodes.
     * This keeps folder navigation working if property XML is malformed.
     */
    private function parsePropfindHrefsOnly(string $xml, string $currentPath): array
    {
        if (!preg_match_all('/<d:href>(.*?)<\/d:href>/si', $xml, $hrefMatches)) {
            return [];
        }

        $rootPath = $this->webDavRootPath();
        $seen = [];
        $entries = [];

        foreach ($hrefMatches[1] as $hrefRaw) {
            $href = html_entity_decode(trim((string)$hrefRaw), ENT_QUOTES | ENT_XML1, 'UTF-8');
            $href = urldecode($href);
            $hrefPath = (string)(parse_url($href, PHP_URL_PATH) ?? '');
            if (!str_starts_with($hrefPath, $rootPath)) {
                continue;
            }

            $relativeRaw = substr($hrefPath, strlen($rootPath));
            $relativeRaw = $relativeRaw === false ? '' : $relativeRaw;
            $relativeRaw = ltrim($relativeRaw, '/');
            if ($relativeRaw === '') {
                continue;
            }

            $isDir = str_ends_with($relativeRaw, '/');
            $relative = trim($relativeRaw, '/');
            if ($relative === '' || $relative === $currentPath) {
                continue;
            }

            // For depth-1 responses, keep direct children only.
            $remainder = $relative;
            if ($currentPath !== '') {
                if (!str_starts_with($relative, $currentPath . '/')) {
                    continue;
                }
                $remainder = substr($relative, strlen($currentPath) + 1);
                if ($remainder === false || $remainder === '') {
                    continue;
                }
            }

            if (str_contains($remainder, '/')) {
                continue;
            }

            $childPath = ($currentPath !== '' ? ($currentPath . '/') : '') . $remainder;
            if (isset($seen[$childPath])) {
                continue;
            }
            $seen[$childPath] = true;

            $entries[] = [
                'name' => basename($childPath),
                'path' => $childPath,
                'is_dir' => $isDir,
                'size' => null,
                'modified_at' => null,
                'mime_type' => '',
            ];
        }

        return $entries;
    }

    private function normalizeXmlPayload(string $xml): string
    {
        if ($xml === '') {
            return '';
        }

        // Remove UTF-8 BOM and leading NUL/whitespace artifacts.
        $xml = preg_replace('/^\xEF\xBB\xBF/', '', $xml) ?? $xml;
        $xml = ltrim($xml, "\x00\x09\x0A\x0D\x20");

        // Strip invalid control bytes that break XML parsers.
        $xml = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $xml) ?? $xml;

        if (function_exists('mb_check_encoding') && function_exists('mb_convert_encoding')) {
            if (!mb_check_encoding($xml, 'UTF-8')) {
                $xml = mb_convert_encoding($xml, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
            }
        }

        return trim($xml);
    }

    private function responsePreview(string $body): string
    {
        $preview = substr($body, 0, 240);
        $preview = preg_replace('/\s+/', ' ', $preview) ?? $preview;
        $preview = trim($preview);
        if ($preview === '') {
            return '[empty]';
        }
        return $preview;
    }

    private function buildWebDavUrl(string $path): string
    {
        $root = rtrim($this->webDavRootUrl(), '/');
        $cleanPath = trim($this->normalizePath($path), '/');
        if ($cleanPath === '') {
            return $root . '/';
        }

        return $root . '/' . $this->encodePath($cleanPath);
    }

    private function webDavRootUrl(): string
    {
        $base = $this->getBaseUrl();
        $template = trim((string)($_ENV['NEXTCLOUD_WEBDAV_ROOT'] ?? self::DEFAULT_ROOT_TEMPLATE));
        $root = str_replace('{username}', rawurlencode($this->credentials()['username']), $template);
        return $base . '/' . ltrim($root, '/');
    }

    private function webDavRootPath(): string
    {
        $path = (string)(parse_url($this->webDavRootUrl(), PHP_URL_PATH) ?? '/');
        $path = urldecode($path);
        $path = '/' . trim($path, '/');
        return rtrim($path, '/') . '/';
    }

    private function parentPath(string $path): string
    {
        $path = $this->normalizePath($path);
        if ($path === '') {
            return '';
        }

        $parts = explode('/', $path);
        array_pop($parts);
        return implode('/', $parts);
    }

    private function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        if ($path === '') {
            return '';
        }

        $segments = explode('/', $path);
        $safe = [];
        foreach ($segments as $segment) {
            $segment = trim($segment);
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($safe);
                continue;
            }
            $safe[] = $segment;
        }

        return implode('/', $safe);
    }

    private function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }

    private function username(): string
    {
        return $this->credentials()['username'];
    }

    private function appPassword(): string
    {
        return $this->credentials()['password'];
    }

    /**
     * Resolve credentials in this order:
     * 1) Logged-in user's Nextcloud credentials from people table
     * 2) Shared env fallback (legacy behavior)
     *
     * people.nextcloud_password can be plain text or encrypted as:
     *   enc:<base64(iv:ciphertext)>
     */
    private function credentials(): array
    {
        if ($this->resolvedCredentials !== null) {
            return $this->resolvedCredentials;
        }

        $username = '';
        $password = '';

        if (function_exists('current_person_id') && function_exists('db')) {
            $personId = (int)current_person_id();
            if ($personId > 0) {
                try {
                    $stmt = db()->prepare('SELECT nextcloud_username, nextcloud_password FROM people WHERE person_id = ? LIMIT 1');
                    $stmt->execute([$personId]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                    $username = trim((string)($row['nextcloud_username'] ?? ''));
                    $rawPassword = trim((string)($row['nextcloud_password'] ?? ''));
                    $password = $this->decryptIfNeeded($rawPassword);
                } catch (Throwable $e) {
                    // If columns are not migrated yet, silently fall back to env config.
                }
            }
        }

        if ($username === '') {
            $username = trim((string)($_ENV['NEXTCLOUD_USERNAME'] ?? ''));
        }
        if ($password === '') {
            $password = trim((string)($_ENV['NEXTCLOUD_APP_PASSWORD'] ?? ''));
        }

        $this->resolvedCredentials = [
            'username' => $username,
            'password' => $password,
        ];

        return $this->resolvedCredentials;
    }

    private function decryptIfNeeded(string $value): string
    {
        if ($value === '') {
            return '';
        }
        if (!str_starts_with($value, 'enc:')) {
            return $value;
        }

        $payload = base64_decode(substr($value, 4), true);
        if ($payload === false || !str_contains($payload, ':')) {
            return '';
        }

        [$iv, $ciphertext] = explode(':', $payload, 2);
        $key = trim((string)($_ENV['NEXTCLOUD_CREDENTIALS_KEY'] ?? ''));
        if ($key === '') {
            return '';
        }

        $plain = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, 0, $iv);
        return is_string($plain) ? $plain : '';
    }
}
