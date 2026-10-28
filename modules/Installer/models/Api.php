<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'vtlib/Vtiger/Net/Client.php';

class Installer_Api_Model extends Vtiger_Net_Client
{
    public const CONNECT_TIMEOUT = 5;
    public const REQUEST_TIMEOUT = 75;
    public const CONNECTION_ERROR = 'connection_error';
    public const INVALID_LICENSE = 'invalid_license';
    public const INVALID_REQUEST = 'invalid_request';
    public const INVALID_USER_COUNT = 'invalid_user_count';
    public string $apiUrl = 'https://its4you.sk/en/api_defalto';
    protected array $lastRequestDiagnostics = [];

    public function activateLicenseInfo(string $license, mixed $userCount = null): array
    {
        return $this->sendLicenseRequest('activate', $license, $userCount);
    }

    public function checkLicenseInfo(string $license, mixed $userCount = null): array
    {
        return $this->sendLicenseRequest('check', $license, $userCount);
    }

    /**
     * @throws Exception
     */
    public function connect(): bool
    {
        $response = $this->sendHttpRequest('/connect/v1', [], self::CONNECT_TIMEOUT);

        if ('connected' === $response) {
            return true;
        }

        throw new Exception('Connection failed');
    }

    public function deactivateLicenseInfo(string $license): array
    {
        $license = trim($license);
        $siteUrl = $this->getSiteUrl();

        if ('' === $license) {
            return $this->getValidationError(self::INVALID_LICENSE);
        }

        if ('' === $siteUrl) {
            return $this->getValidationError(self::INVALID_REQUEST);
        }

        return $this->sendJsonRequest('/license/v1', [
            'action' => 'deactivate',
            'license' => $license,
            'url' => $siteUrl,
        ]);
    }

    /**
     * @throws Exception
     */
    public function getExtensionInstall(): array
    {
        // Download metadata returned without a license identifies public packages.
        $extensions = self::transformExtensionResponse($this->sendJsonRequest('/extension/v1', []));

        foreach ($extensions as &$extensionInfo) {
            $extensionInfo['installer_public_download'] = self::hasExtensionPackage($extensionInfo);
            $extensionInfo['installer_license_id'] = 0;
        }

        unset($extensionInfo);

        foreach (Installer_License_Model::getAll() as $license) {
            $license->check();

            if (!$license->isValidLicense()) {
                continue;
            }

            $params = self::getProtectedRequestParams(
                $license->getName(),
                $this->getSiteUrl(),
                Installer_UserCount_Model::getLicensedUserCount()
            );

            if (empty($params)) {
                $license->saveError(self::INVALID_REQUEST);

                continue;
            }

            $response = $this->sendJsonRequest('/extension/v1', $params);

            if (self::isConnectionError($response)) {
                $license->saveConnectionError();

                continue;
            }

            $licensedExtensions = self::transformExtensionResponse($response);

            foreach ($licensedExtensions as $moduleName => $extensionInfo) {
                if (!isset($extensions[$moduleName])) {
                    continue;
                }

                if (!empty($extensions[$moduleName]['installer_public_download'])) {
                    continue;
                }

                if (!$license->hasExtensionEntitlement($moduleName) || !self::hasExtensionPackage($extensionInfo)) {
                    continue;
                }

                $extensionInfo['installer_license_id'] = $license->getId();
                $extensionInfo['installer_public_download'] = false;
                $extensions[$moduleName] = $extensionInfo;
            }
        }

        return $extensions;
    }

    public static function getInstance(): self
    {
        return new self('');
    }

    public function getSiteUrl(): string
    {
        global $site_URL;

        $installSiteUrl = (string)($_SESSION['config_file_info']['site_URL'] ?? '');
        $configuredSiteUrl = '' !== trim($installSiteUrl) ? $installSiteUrl : (string)$site_URL;

        if (empty($configuredSiteUrl)) {
            $scheme = empty($_SERVER['HTTPS']) || 'off' === $_SERVER['HTTPS'] ? 'http' : 'https';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $configuredSiteUrl = $scheme . '://' . $host;
        }

        return self::normalizeSiteUrl($configuredSiteUrl);
    }

    /**
     * @throws Exception
     */
    public function getSystemInstall(): array
    {
        $license = Installer_License_Model::getMembershipLicense();
        $licenseKey = '';
        $isValid = false;

        if ($license) {
            $license->check();
            $licenseKey = $license->getName();
            $isValid = $license->isValidLicense();
        }

        $params = self::getSystemRequestParams(
            $licenseKey,
            $this->getSiteUrl(),
            Installer_UserCount_Model::getLicensedUserCount()
        );

        if (empty($params)) {
            if ($license) {
                $license->saveError(self::INVALID_REQUEST);
            }

            return [];
        }

        $response = $this->sendJsonRequest('/system/v1/', $params);

        if (self::isConnectionError($response)) {
            if ($license) {
                $license->saveConnectionError();
            }

            return [];
        }

        return self::transformSystemResponse($response, $isValid);
    }

    public static function isConnectionError(array $response): bool
    {
        return true === ($response['transport_error'] ?? false);
    }

    public static function transformSystemResponse(array $response, bool $isLicenseValid): array
    {
        $transformedResponse = [];

        foreach ($response as $version => $versionInfo) {
            if (!is_array($versionInfo) || empty($versionInfo['version'])) {
                continue;
            }

            if (!$isLicenseValid) {
                $versionInfo = [
                    'version' => (string)$versionInfo['version'],
                    'label' => (string)($versionInfo['label'] ?? ''),
                    'download-url' => '',
                    'download-folder' => '',
                ];
            } elseif ('' === trim((string)($versionInfo['download-url'] ?? ''))) {
                $versionInfo['download-url'] = '';
                $versionInfo['download-folder'] = '';
            }

            $transformedResponse[$version] = $versionInfo;
        }

        return $transformedResponse;
    }

    public static function transformExtensionResponse(array $response): array
    {
        return array_filter(
            $response,
            static fn (mixed $extensionInfo, mixed $moduleName): bool => is_string($moduleName)
                && 1 === preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $moduleName)
                && is_array($extensionInfo)
                && is_scalar($extensionInfo['update_version'] ?? null)
                && '' !== trim((string)$extensionInfo['update_version']),
            ARRAY_FILTER_USE_BOTH
        );
    }

    public static function hasExtensionPackage(array $extensionInfo): bool
    {
        return '' !== trim((string)($extensionInfo['download-url'] ?? ''))
            && '' !== trim((string)($extensionInfo['download-folder'] ?? ''));
    }

    public static function convertToNonNegativeInteger(mixed $value): int|false
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : false;
        }

        if (is_string($value) && preg_match('/^(0|[1-9][0-9]*)$/', $value)) {
            return (int)$value;
        }

        return false;
    }

    public static function normalizeSiteUrl(string $siteUrl): string
    {
        $parts = parse_url(trim($siteUrl));

        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return '';
        }

        $scheme = strtolower((string)$parts['scheme']);

        if (!in_array($scheme, ['http', 'https'], true) || isset($parts['user']) || isset($parts['pass'])) {
            return '';
        }

        $host = strtolower(rtrim((string)$parts['host'], '.'));
        $port = isset($parts['port']) ? (int)$parts['port'] : null;
        $path = preg_replace('#/+#', '/', (string)($parts['path'] ?? ''));
        $path = '' === $path || '/' === $path ? '' : '/' . trim($path, '/');
        $isDefaultPort = ('http' === $scheme && 80 === $port) || ('https' === $scheme && 443 === $port);
        $portPart = $port && !$isDefaultPort ? ':' . $port : '';
        $normalizedUrl = $scheme . '://' . $host . $portPart . $path;

        return false !== filter_var($normalizedUrl, FILTER_VALIDATE_URL) ? $normalizedUrl : '';
    }

    public static function getLicenseRequestParams(
        string $action,
        string $license,
        string $siteUrl,
        mixed $userCount
    ): array {
        $license = trim($license);
        $siteUrl = self::normalizeSiteUrl($siteUrl);
        $normalizedUserCount = self::convertToNonNegativeInteger($userCount);

        if ('' === $license || '' === $siteUrl || false === $normalizedUserCount) {
            return [];
        }

        return [
            'action' => $action,
            'license' => $license,
            'url' => $siteUrl,
            'user_count' => $normalizedUserCount,
        ];
    }

    public static function getProtectedRequestParams(
        string $license,
        string $siteUrl,
        mixed $userCount
    ): array {
        $siteUrl = self::normalizeSiteUrl($siteUrl);
        $normalizedUserCount = self::convertToNonNegativeInteger($userCount);

        $license = trim($license);

        if ('' === $license || '' === $siteUrl || false === $normalizedUserCount) {
            return [];
        }

        return [
            'license' => $license,
            'url' => $siteUrl,
            'user_count' => $normalizedUserCount,
        ];
    }

    public static function getSystemRequestParams(
        string $license,
        string $siteUrl,
        mixed $userCount
    ): array {
        $siteUrl = self::normalizeSiteUrl($siteUrl);
        $normalizedUserCount = self::convertToNonNegativeInteger($userCount);

        if ('' === $siteUrl || false === $normalizedUserCount) {
            return [];
        }

        return [
            'license' => trim($license),
            'url' => $siteUrl,
            'user_count' => $normalizedUserCount,
        ];
    }

    protected function sendLicenseRequest(string $action, string $license, mixed $userCount = null): array
    {
        $license = trim($license);

        if ('' === $license) {
            return $this->getValidationError(self::INVALID_LICENSE);
        }

        $siteUrl = $this->getSiteUrl();

        if ('' === $siteUrl) {
            return $this->getValidationError(self::INVALID_REQUEST);
        }

        if (null === $userCount) {
            $userCount = Installer_UserCount_Model::getLicensedUserCount();
        }

        $params = self::getLicenseRequestParams($action, $license, $siteUrl, $userCount);

        if (empty($params)) {
            return $this->getValidationError(self::INVALID_USER_COUNT);
        }

        return $this->sendJsonRequest('/license/v1', $params);
    }

    protected function sendJsonRequest(string $endpoint, array $params): array
    {
        $response = $this->sendHttpRequest($endpoint, $params);

        if (false === $response) {
            return $this->getConnectionError($endpoint, 'transport_failure', $params);
        }

        if ('' === trim((string)$response)) {
            return $this->getConnectionError($endpoint, 'empty_response', $params);
        }

        $decoded = json_decode($response, true);

        if (!is_array($decoded) || JSON_ERROR_NONE !== json_last_error()) {
            return $this->getConnectionError($endpoint, 'invalid_json', $params, $response);
        }

        return $decoded;
    }

    protected function sendHttpRequest(
        string $endpoint,
        array $params,
        int $requestTimeout = self::REQUEST_TIMEOUT
    ): string|false {
        $this->lastRequestDiagnostics = [];

        if (!function_exists('curl_init')) {
            $this->setURL($this->getEndpointUrl($endpoint));
            $this->setRequestTimeouts();

            return $this->doGet($params, self::CONNECT_TIMEOUT);
        }

        $url = $this->getEndpointUrl($endpoint);

        if (!empty($params)) {
            $url .= '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        }

        $handle = curl_init($url);

        if (false === $handle) {
            $this->lastRequestDiagnostics = [
                'failure' => 'curl_initialization_failed',
            ];

            return false;
        }

        $curlOptions = [
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => $requestTimeout,
        ];

        if (defined('CURLSSLOPT_NATIVE_CA')) {
            $curlOptions[CURLOPT_SSL_OPTIONS] = CURLSSLOPT_NATIVE_CA;
        }

        curl_setopt_array($handle, $curlOptions);
        $response = curl_exec($handle);
        $this->lastRequestDiagnostics = [
            'http_status' => curl_getinfo($handle, CURLINFO_HTTP_CODE) ?: null,
            'content_type' => curl_getinfo($handle, CURLINFO_CONTENT_TYPE) ?: '',
            'transport_code' => curl_errno($handle),
            'transport_message' => curl_error($handle),
        ];

        return is_string($response) ? $response : false;
    }

    protected function getValidationError(string $error): array
    {
        return [
            'success' => false,
            'error' => $error,
        ];
    }

    protected function setRequestTimeouts(): void
    {
        $this->client->_readTimeout = [self::REQUEST_TIMEOUT, 0];
        $this->setHeaders(['accept' => 'application/json']);
    }

    protected function getConnectionError(
        string $endpoint,
        string $failure,
        array $params,
        mixed $responseBody = false
    ): array {
        $isDebug = true === vglobal('debug') || 1 === (int)($_SESSION['debug'] ?? 0);
        $error = [
            'success' => false,
            'error' => self::CONNECTION_ERROR,
            'transport_error' => true,
        ];

        if ($isDebug) {
            $error['debug'] = $this->getConnectionDebug($endpoint, $failure, $params, $responseBody);
        }

        if (true === vglobal('debug')) {
            Core_Install_Model::logError($error);
        }

        return $error;
    }

    protected function getConnectionDebug(
        string $endpoint,
        string $failure,
        array $params,
        mixed $responseBody
    ): array {
        $debug = [
            'failure' => $failure,
            'request_method' => 'GET',
            'request_url' => $this->getEndpointUrl($endpoint),
        ];

        if (!empty($this->lastRequestDiagnostics)) {
            $debug = array_merge($debug, $this->lastRequestDiagnostics);
        } else {
            $debug['http_status'] = $this->client->getResponseCode() ?: null;
            $debug['http_reason'] = $this->client->getResponseReason() ?: '';
            $debug['content_type'] = $this->client->getResponseHeader('content-type') ?: '';
        }

        if (is_object($this->response) && method_exists($this->response, 'getMessage')) {
            $debug['transport_message'] = $this->redactLicense((string)$this->response->getMessage(), $params);
        }

        if (is_object($this->response) && method_exists($this->response, 'getCode')) {
            $debug['transport_code'] = $this->response->getCode();
        }

        if ('invalid_json' === $failure) {
            $debug['json_error'] = json_last_error_msg();
            $debug['response_body_preview'] = $this->getSafeResponsePreview($responseBody, $params);
        }

        return $debug;
    }

    protected function getSafeResponsePreview(mixed $responseBody, array $params): string
    {
        $preview = substr((string)$responseBody, 0, 2000);

        return htmlspecialchars(
            $this->redactLicense($preview, $params),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }

    protected function redactLicense(string $value, array $params): string
    {
        $license = (string)($params['license'] ?? '');

        if ('' === $license) {
            return $value;
        }

        return str_replace(
            array_unique([$license, rawurlencode($license), urlencode($license)]),
            '[redacted]',
            $value
        );
    }

    protected function getEndpointUrl(string $endpoint): string
    {
        return rtrim($this->apiUrl, '/') . '/' . trim($endpoint, '/') . '/';
    }
}
