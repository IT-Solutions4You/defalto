<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_License_Model extends Core_DatabaseData_Model
{
    public const API_VERSION = 2;
    public const CHECK_INTERVAL = 900;
    public const REQUEST_LOCK_WAIT_SECONDS = 5;
    public const MEMBERSHIP_PACKAGE = 'Membership Package';
    public const LIFETIME_EXPIRE_VALUE = 'lifetime';
    public const USER_LIMIT_EXCEEDED = 'user_limit_exceeded';
    protected static array $checkedRequests = [];
    protected static self|false|null $currentLicense = null;
    protected ?array $decodedInfoCache = null;
    protected mixed $requestLockHandle = null;
    protected array $columns = [
        'name',
        'info',
    ];
    protected string $table = 'df_licenses';
    protected string $tableId = 'id';
    protected string $tableName = 'name';
    protected const LICENSE_METADATA_KEYS = [
        'error',
        'expires',
        'extensions',
        'item_id',
        'item_name',
        'item_user_limit',
        'license',
        'price_id',
        'success',
        'user_count',
        'user_limit',
        'users_valid',
    ];

    /**
     * @throws Exception
     */
    public static function getCurrent(): self|false
    {
        if (null !== self::$currentLicense) {
            return self::$currentLicense;
        }

        $storedLicenseModels = self::getAll();

        if (empty($storedLicenseModels)) {
            self::$currentLicense = false;

            return false;
        }

        $membershipLicenses = array_filter(
            $storedLicenseModels,
            static fn (self $license): bool => $license->isMembershipLicense()
        );
        self::$currentLicense = self::selectPreferredLicense($membershipLicenses ?: $storedLicenseModels);

        return self::$currentLicense;
    }

    /**
     * @throws Exception
     */
    public static function getAll(): array
    {
        $cache = self::getCache('all');

        if (!$cache->has()) {
            $cache->set(self::retrieveStoredLicenses());
        }

        return $cache->get();
    }

    /**
     * @throws Exception
     */
    public static function getMembershipLicense(): self|false
    {
        $licenses = array_filter(
            self::getAll(),
            static fn (self $license): bool => $license->isMembershipLicense()
        );

        return self::selectPreferredLicense($licenses);
    }

    /**
     * @throws Exception
     */
    public static function getInstanceForActivation(string $licenseKey): self
    {
        return self::getInstance(trim($licenseKey));
    }

    public function getExpireDate(): string
    {
        return (string)$this->getInfo('expires');
    }

    public function getDisplayExpireDate(): string
    {
        if (!$this->hasExpireDate()) {
            return '';
        }

        if ($this->isLifetimeLicense()) {
            return vtranslate('Unlimited', 'Installer');
        }

        return Vtiger_Functions::currentUserDisplayDate($this->getExpireDate());
    }

    public function getInfo($key = ''): mixed
    {
        if (null === $this->decodedInfoCache) {
            $info = json_decode(base64_decode((string)$this->get('info')), true);
            $this->decodedInfoCache = is_array($info) ? $info : [];
        }

        if ('' !== $key) {
            return array_key_exists($key, $this->decodedInfoCache) ? $this->decodedInfoCache[$key] : null;
        }

        return $this->decodedInfoCache;
    }

    /**
     * @throws Exception
     */
    public static function getInstance($name): self
    {
        $instance = new self();
        $instance->set('name', trim((string)$name));
        $instance->retrieveDataByName();

        return $instance;
    }

    /**
     * @throws Exception
     */
    public static function getInstanceById(int $id): self|false
    {
        $cache = self::getCache('id', $id);

        if ($cache->has()) {
            return $cache->get();
        }

        $license = new self();
        $data = $license->getLicenseTable()->selectData(array_merge(['id'], $license->columns), ['id' => $id]);

        if (empty($data['id'])) {
            $cache->set(false);

            return false;
        }

        $license->setData($data);
        $cache->set($license);

        return $license;
    }

    public function getItemName(): string
    {
        return (string)($this->getInfo('item_name') ?: 'Defalto');
    }

    public function getUsersLimit(): int
    {
        $limit = $this->getInfo('user_limit');

        if (null === $limit) {
            $limit = $this->getInfo('item_user_limit');
        }

        $normalizedLimit = Installer_Api_Model::convertToNonNegativeInteger($limit);

        return false === $normalizedLimit ? 0 : $normalizedLimit;
    }

    public function getDisplayUsersLimit(): string
    {
        return $this->getUsersLimit() > 0 ? (string)$this->getUsersLimit() : vtranslate('Unlimited', 'Installer');
    }

    public function getUsersCount(): int
    {
        return Installer_UserCount_Model::getLicensedUserCount();
    }

    public function getLicenseTable(): object
    {
        return $this->getTable($this->table, $this->tableId);
    }

    public function getLicenseUrl(): string
    {
        return 'index.php?module=Installer&view=Index';
    }

    public function getSaveParams(): array
    {
        return [
            'name' => $this->get('name'),
            'info' => $this->get('info'),
        ];
    }

    /**
     * @throws Exception
     */
    public static function isActiveExtension(string $extension): bool
    {
        return false !== self::getLicenseForExtension($extension);
    }

    /**
     * @throws Exception
     */
    public static function getLicenseForExtension(string $extension): self|false
    {
        foreach (self::getAll() as $license) {
            if ($license->isValidLicense() && $license->hasExtensionEntitlement($extension)) {
                return $license;
            }
        }

        return false;
    }

    /**
     * @throws Exception
     */
    public static function isActive(): bool
    {
        return self::isMembershipActive();
    }

    /**
     * @throws Exception
     */
    public static function isMembershipActive(): bool
    {
        $license = self::getMembershipLicense();

        return $license && $license->isValidLicense();
    }

    public function isMembershipLicense(): bool
    {
        $extensions = array_filter((array)$this->getInfo('extensions'), 'is_string');

        return in_array('Installer', $extensions, true) || empty($extensions);
    }

    public function hasExtensionEntitlement(string $extension): bool
    {
        return in_array($extension, $this->getExtensionEntitlements(), true);
    }

    public function getExtensionEntitlements(): array
    {
        $extensions = array_filter(
            (array)$this->getInfo('extensions'),
            static fn (mixed $extension): bool => is_string($extension) && '' !== trim($extension)
        );

        return array_values(array_unique(array_map('trim', $extensions)));
    }

    public function isValidLicense(): bool
    {
        return $this->isValid() && !$this->isUserLimitReached();
    }

    /**
     * @throws Exception
     */
    public static function getUserLimitExceededLicense(string $extension = ''): self|false
    {
        foreach (self::getAll() as $license) {
            if ('' !== $extension && !$license->hasExtensionEntitlement($extension)) {
                continue;
            }

            if ($license->isUserLimitReached()) {
                return $license;
            }
        }

        return false;
    }

    public function isValid(): bool
    {
        return true === $this->getInfo('local_active')
            && 'valid' === $this->getInfo('license')
            && true === $this->getInfo('users_valid');
    }

    public function isExpired(): bool
    {
        if (!$this->hasExpireDate() || $this->isLifetimeLicense()) {
            return false;
        }

        return $this->getExpireDate() < date('Y-m-d H:i:s');
    }

    public function isUserLimitReached(): bool
    {
        $limit = $this->getUsersLimit();

        return $limit > 0 && $this->getUsersCount() > $limit;
    }

    public function setInfo(array $info): void
    {
        $this->decodedInfoCache = $info;
        $this->set('info', base64_encode(json_encode($info, JSON_UNESCAPED_SLASHES)));
    }

    public function hasExpireDate(): bool
    {
        return '' !== $this->getExpireDate();
    }

    public function isLifetimeLicense(): bool
    {
        return self::LIFETIME_EXPIRE_VALUE === $this->getExpireDate();
    }

    /**
     * @throws Exception
     */
    public function activate(): static
    {
        $userCount = $this->getUsersCount();
        $isStored = 0 < $this->getId();
        $this->acquireRequestLock(true);

        try {
            $this->setRequestStarted('activate', $userCount);

            if ($isStored) {
                $this->save();
            }

            $response = $this->getApiModel()->activateLicenseInfo($this->getName(), $userCount);
            $this->setInfoFromLicenseResponse($response, 'activate', $userCount);

            if ($this->isValidLicense()) {
                $this->setSelected();
                $this->save();
            } elseif ($isStored) {
                $this->save();
            }

            self::$checkedRequests[$this->getRequestCheckKey($userCount)] = true;
        } finally {
            $this->releaseRequestLock();
        }

        self::clearRuntimeCaches();

        return $this;
    }

    /**
     * @throws Exception
     */
    public function check(bool $force = false): static
    {
        $userCount = $this->getUsersCount();
        $requestCheckKey = $this->getRequestCheckKey($userCount);

        if (isset(self::$checkedRequests[$requestCheckKey])) {
            return $this;
        }

        if ($this->requiresReactivation($userCount)) {
            return $this->activate();
        }

        if (!$force && !$this->requiresCheck($userCount)) {
            return $this;
        }

        if (!$this->acquireRequestLock($force)) {
            return $this;
        }

        try {
            $this->setRequestStarted('check', $userCount);
            $this->save();
            $response = $this->getApiModel()->checkLicenseInfo($this->getName(), $userCount);
            $this->setInfoFromLicenseResponse($response, 'check', $userCount);
            $this->save();
            self::$checkedRequests[$requestCheckKey] = true;
        } finally {
            $this->releaseRequestLock();
        }

        self::clearRuntimeCaches();

        return $this;
    }

    /**
     * @throws Exception
     */
    public static function initialize(): void
    {
        $license = self::getCurrent();

        if ($license) {
            $license->check(false);
        }
    }

    /**
     * @throws Exception
     */
    public function deactivate(): static
    {
        $this->acquireRequestLock(true);

        try {
            $response = $this->getApiModel()->deactivateLicenseInfo($this->getName());
            $info = $this->getInfo();
            $info['last_attempt_at'] = date('Y-m-d H:i:s');

            if (Installer_Api_Model::isConnectionError($response)) {
                $info['last_error'] = Installer_Api_Model::CONNECTION_ERROR;
                $info['last_deactivation_success'] = false;
            } elseif (true === ($response['success'] ?? false)) {
                foreach (self::LICENSE_METADATA_KEYS as $metadataKey) {
                    unset($info[$metadataKey]);
                }

                $info['local_active'] = false;
                $info['users_valid'] = false;
                $info['last_error'] = '';
                $info['last_deactivation_success'] = true;
                $info['reactivation_required'] = false;
            } else {
                $info = array_merge($info, $response);
                $info['last_error'] = (string)($response['error'] ?? 'invalid_request');
                $info['last_deactivation_success'] = false;
            }

            $this->setInfo($info);
            $this->save();
        } finally {
            $this->releaseRequestLock();
        }

        self::clearRuntimeCaches();

        return $this;
    }

    public function getErrorCode(): string
    {
        $info = $this->getInfo();

        if (array_key_exists('last_error', $info)) {
            return (string)$info['last_error'];
        }

        return (string)($info['error'] ?? '');
    }

    public function isLastDeactivationSuccessful(): bool
    {
        return true === $this->getInfo('last_deactivation_success');
    }

    /**
     * @throws Exception
     */
    public function saveConnectionError(): void
    {
        $this->saveError(Installer_Api_Model::CONNECTION_ERROR);
    }

    /**
     * @throws Exception
     */
    public function saveError(string $error): void
    {
        $info = $this->getInfo();
        $info['last_error'] = $error;
        $info['last_attempt_at'] = date('Y-m-d H:i:s');
        $info['local_active'] = false;
        $info['users_valid'] = false;
        $this->setInfo($info);
        $this->save();
    }

    public function getErrorMessage(): string
    {
        $error = $this->getErrorCode();

        if ($this->isUserLimitReached() || self::USER_LIMIT_EXCEEDED === $error) {
            return vtranslate('LBL_LICENSE_ERROR_USER_LIMIT_EXCEEDED', 'Installer');
        }

        if ('' === $error) {
            return '';
        }

        return vtranslate('LBL_LICENSE_ERROR_' . strtoupper($error), 'Installer');
    }

    public function getDisplayLastSuccessfulCheck(): string
    {
        $dateTime = (string)$this->getInfo('last_successful_check');

        if ('' === $dateTime) {
            return vtranslate('LBL_NEVER', 'Installer');
        }

        [$date, $time] = array_pad(explode(' ', $dateTime, 2), 2, '');

        return Vtiger_Util_Helper::formatDateIntoStrings($date, $time);
    }

    public function getLogData(): array
    {
        $lastResponse = (array)$this->getInfo('last_response');
        unset($lastResponse['user_count'], $lastResponse['user_limit'], $lastResponse['item_user_limit']);

        return [
            'last_response' => $lastResponse,
            'active' => $this->isValidLicense(),
            'success' => $this->getInfo('success'),
            'license' => $this->getInfo('license'),
            'item_id' => $this->getInfo('item_id'),
            'item_name' => $this->getInfo('item_name'),
            'price_id' => $this->getInfo('price_id'),
            'expires' => $this->getInfo('expires'),
            'users_valid' => $this->getInfo('users_valid'),
            'extensions' => (array)$this->getInfo('extensions'),
            'error' => $this->getInfo('error'),
            'last_error' => $this->getErrorCode(),
            'last_attempt_at' => $this->getInfo('last_attempt_at'),
            'last_successful_check' => $this->getInfo('last_successful_check'),
        ];
    }

    /**
     * @throws Exception
     */
    public static function updateAll(): void
    {
        foreach (self::getAll() as $license) {
            $license->check(true);
        }
    }

    /**
     * @throws Exception
     */
    public function save(): void
    {
        parent::save();
        self::$currentLicense = null;
        self::clearLicenseCache($this->getId());
    }

    /**
     * @throws Exception
     */
    public function delete(): void
    {
        $info = $this->getInfo();
        $info['api_version'] = self::API_VERSION;
        $info['selected_at'] = (new DateTimeImmutable())->format('Y-m-d H:i:s.u');
        $info['key_deleted'] = true;
        $info['local_active'] = false;
        $info['users_valid'] = false;
        $info['last_error'] = '';
        $this->set('name', '');
        $this->setInfo($info);
        parent::save();
        self::$currentLicense = null;
        self::clearLicenseCache($this->getId());
        self::clearRuntimeCaches();
    }

    protected function setInfoFromLicenseResponse(
        array $response,
        string $requestAction = 'check',
        ?int $requestedUserCount = null
    ): void
    {
        $info = $this->getInfo();
        $requestedUserCount ??= $this->getUsersCount();
        $info['api_version'] = self::API_VERSION;
        $info['selected_at'] = $info['selected_at'] ?? (new DateTimeImmutable())->format('Y-m-d H:i:s.u');
        $info['last_attempt_at'] = date('Y-m-d H:i:s');
        $info['last_request_action'] = $requestAction;
        $info['last_requested_user_count'] = $requestedUserCount;
        if (Installer_Api_Model::isConnectionError($response)) {
            $info['last_response'] = self::getResponseLogData($response);
            $info['last_error'] = Installer_Api_Model::CONNECTION_ERROR;
            $info['local_active'] = false;
            $info['users_valid'] = false;
            $this->setInfo($info);

            return;
        }

        $userLimit = $response['user_limit'] ?? $response['item_user_limit'] ?? null;
        $normalizedLimit = Installer_Api_Model::convertToNonNegativeInteger($userLimit);

        if (false === $normalizedLimit && true === ($response['success'] ?? false)) {
            $response['success'] = false;
            $response['error'] = 'user_limit_missing';
        } elseif (false !== $normalizedLimit) {
            $response['user_limit'] = $normalizedLimit;
        }

        if (true === ($response['success'] ?? false) && '' === trim((string)($response['expires'] ?? ''))) {
            $response['success'] = false;
            $response['error'] = 'missing_expiration';
        }

        $responseUserCount = Installer_Api_Model::convertToNonNegativeInteger($response['user_count'] ?? null);
        $response['user_count'] = false === $responseUserCount ? $requestedUserCount : $responseUserCount;
        $isSuccessful = true === ($response['success'] ?? false)
            && 'valid' === ($response['license'] ?? null)
            && true === ($response['users_valid'] ?? false);

        if ($isSuccessful && $normalizedLimit > 0 && $requestedUserCount > $normalizedLimit) {
            $response['success'] = false;
            $response['license'] = 'invalid';
            $response['users_valid'] = false;
            $response['error'] = self::USER_LIMIT_EXCEEDED;
            $isSuccessful = false;
        }

        $info['last_response'] = self::getResponseLogData($response);
        $info = array_merge($info, $response);

        if ($isSuccessful) {
            $info['local_active'] = true;
            $info['last_error'] = '';
            $info['error'] = '';
            $info['last_successful_check'] = date('Y-m-d H:i:s');
            $info['reactivation_required'] = false;
        } else {
            $info['local_active'] = false;
            $info['users_valid'] = false;
            $info['last_error'] = (string)($response['error'] ?? 'invalid_license');

            if (self::USER_LIMIT_EXCEEDED === $info['last_error']) {
                $info['reactivation_required'] = true;
            } elseif (Installer_Api_Model::CONNECTION_ERROR !== $info['last_error']) {
                $info['reactivation_required'] = false;
            }
        }

        $this->setInfo($info);
    }

    protected static function getResponseLogData(array $response): array
    {
        $allowedKeys = [
            'success',
            'license',
            'users_valid',
            'item_id',
            'item_name',
            'price_id',
            'expires',
            'user_count',
            'user_limit',
            'item_user_limit',
            'extensions',
            'error',
            'transport_error',
        ];

        return array_intersect_key($response, array_flip($allowedKeys));
    }

    protected function setSelected(): void
    {
        $info = $this->getInfo();
        $info['api_version'] = self::API_VERSION;
        $info['selected_at'] = (new DateTimeImmutable())->format('Y-m-d H:i:s.u');
        $this->setInfo($info);
    }

    protected function requiresCheck(int $userCount): bool
    {
        $lastRequestedUserCount = Installer_Api_Model::convertToNonNegativeInteger(
            $this->getInfo('last_requested_user_count')
        );

        if (false === $lastRequestedUserCount || $lastRequestedUserCount !== $userCount) {
            return true;
        }

        $lastAttempt = strtotime((string)$this->getInfo('last_attempt_at'));

        return !$lastAttempt || time() - $lastAttempt >= self::CHECK_INTERVAL;
    }

    protected function requiresReactivation(int $userCount): bool
    {
        if (true !== $this->getInfo('reactivation_required')) {
            return false;
        }

        $userLimit = $this->getUsersLimit();

        return $userLimit > 0 && $userCount <= $userLimit;
    }

    protected function setRequestStarted(string $action, int $userCount): void
    {
        $info = $this->getInfo();
        $info['last_attempt_at'] = date('Y-m-d H:i:s');
        $info['last_request_action'] = $action;
        $info['last_requested_user_count'] = $userCount;
        $this->setInfo($info);
    }

    protected function acquireRequestLock(bool $wait): bool
    {
        if (is_resource($this->requestLockHandle)) {
            return true;
        }

        $lockDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'defalto-installer-license-locks';

        if (!is_dir($lockDirectory) && !mkdir($lockDirectory, 0700, true) && !is_dir($lockDirectory)) {
            if ($wait) {
                throw new RuntimeException('Unable to create the Installer license lock directory');
            }

            return false;
        }

        $installation = dirname(__DIR__, 3);
        $lockName = hash('sha256', $installation . "\0" . $this->getName()) . '.lock';
        $this->requestLockHandle = @fopen($lockDirectory . DIRECTORY_SEPARATOR . $lockName, 'c');

        if (!is_resource($this->requestLockHandle)) {
            if ($wait) {
                throw new RuntimeException('Unable to open the Installer license lock');
            }

            $this->requestLockHandle = null;

            return false;
        }

        $deadline = microtime(true) + ($wait ? self::REQUEST_LOCK_WAIT_SECONDS : 0);

        do {
            if (flock($this->requestLockHandle, LOCK_EX | LOCK_NB)) {
                return true;
            }

            if (!$wait || microtime(true) >= $deadline) {
                break;
            }

            usleep(100000);
        } while (true);

        fclose($this->requestLockHandle);
        $this->requestLockHandle = null;

        if ($wait) {
            throw new RuntimeException('Another Installer license request is already running');
        }

        return false;
    }

    protected function releaseRequestLock(): void
    {
        if (is_resource($this->requestLockHandle)) {
            flock($this->requestLockHandle, LOCK_UN);
            fclose($this->requestLockHandle);
        }

        $this->requestLockHandle = null;
    }

    protected function getRequestCheckKey(?int $userCount = null): string
    {
        $userCount ??= $this->getUsersCount();

        return hash('sha256', $this->getName()) . ':' . $userCount;
    }

    protected function getApiModel(): Installer_Api_Model
    {
        return Installer_Api_Model::getInstance();
    }

    protected function getLegacyMigrationScore(): int
    {
        $score = 0;

        if ('valid' === $this->getInfo('license')) {
            $score += 100;
        }

        if ($this->hasExpireDate() && !$this->isExpired()) {
            $score += 50;
        }

        if (in_array('Installer', (array)$this->getInfo('extensions'), true)) {
            $score += 25;
        }

        return $score;
    }

    /**
     * @throws Exception
     */
    protected static function retrieveStoredLicenses(): array
    {
        $license = new self();
        $result = $license->getLicenseTable()->selectResult(['id'], []);
        $storedLicenseModels = [];

        while ($row = $license->getLicenseTable()->getDB()->fetchByAssoc($result)) {
            $storedLicense = self::getInstanceById((int)$row['id']);

            if ($storedLicense
                && '' !== $storedLicense->getName()
                && true !== $storedLicense->getInfo('key_deleted')
            ) {
                $storedLicenseModels[$storedLicense->getId()] = $storedLicense;
            }
        }

        return $storedLicenseModels;
    }

    protected static function selectPreferredLicense(array $licenses): self|false
    {
        if (empty($licenses)) {
            return false;
        }

        usort($licenses, static function (self $first, self $second): int {
            $validComparison = (int)$second->isValidLicense() <=> (int)$first->isValidLicense();

            if (0 !== $validComparison) {
                return $validComparison;
            }

            $firstApiVersion = (int)$first->getInfo('api_version');
            $secondApiVersion = (int)$second->getInfo('api_version');

            if ($firstApiVersion !== $secondApiVersion) {
                return $secondApiVersion <=> $firstApiVersion;
            }

            $selectionComparison = strcmp(
                (string)$second->getInfo('selected_at'),
                (string)$first->getInfo('selected_at')
            );

            if (0 !== $selectionComparison) {
                return $selectionComparison;
            }

            $scoreComparison = $second->getLegacyMigrationScore() <=> $first->getLegacyMigrationScore();

            return $scoreComparison ?: $second->getId() <=> $first->getId();
        });

        return reset($licenses);
    }

    protected static function getCache(string|int ...$keyParts): Installer_Cache_Model
    {
        return Installer_Cache_Model::getInstance(self::class, ...$keyParts);
    }

    protected static function clearLicenseCache(int $licenseId = 0): void
    {
        self::getCache('all')->delete();

        if ($licenseId > 0) {
            self::getCache('id', $licenseId)->delete();
        }
    }

    protected static function clearRuntimeCaches(): void
    {
        Installer_Cache_Model::clear();
        Installer_ExtensionInstall_Model::clearCache();
        Installer_SystemInstall_Model::clearCache();
    }
}
