<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Installer_Requirements_Model extends Vtiger_Base_Model
{
    protected string $buttonType = 'success';
    protected array $dbRequirements = [
        'DieOnError' => [
            'type' => 'DieOnError',
            'minimum' => 'no',
            'recommended' => 'no',
        ],
        'MysqlStrictMode' => [
            'type' => 'MysqlStrictMode',
            'minimum' => 'no',
            'recommended' => 'no',
        ],
        'SqlMode' => [
            'type' => 'SqlMode',
            'minimum' => 'yes',
            'recommended' => 'no',
            'recommended_description' => 'LBL_EMPTY_OR_NO_ENGINE_SUBSTITUTION',
        ],
        'SqlCharset' => [
            'type' => 'SqlCharset',
            'minimum' => 'utf8_general_ci',
            'recommended' => 'utf8_general_ci',
            'recommended_description' => 'LBL_CHARSET_DATABASE_TABLE_COLUMN',
        ],
    ];
    protected array $filePermissions = [
        'cache' => [
            'type' => 'Folder',
            'recommended' => 'yes',
        ],
        'cron/modules' => [
            'type' => 'Folder',
            'recommended' => 'yes',
        ],
        'cron/language' => [
            'type' => 'Folder',
            'recommended' => 'yes',
        ],
        'languages' => [
            'type' => 'Folder',
            'minimum' => 'yes',
            'recommended' => 'yes',
        ],
        'layouts' => [
            'type' => 'Folder',
            'minimum' => 'yes',
            'recommended' => 'yes',
        ],
        'logs' => [
            'type' => 'Folder',
            'recommended' => 'yes',
        ],
        'modules' => [
            'type' => 'Folder',
            'minimum' => 'yes',
            'recommended' => 'yes',
        ],
        'storage' => [
            'type' => 'Folder',
            'recommended' => 'yes',
        ],
        'test' => [
            'type' => 'Folder',
            'recommended' => 'yes',
        ],
        'test/logo' => [
            'type' => 'Folder',
            'recommended' => 'yes',
        ],
        'test/templates_c' => [
            'type' => 'Folder',
            'recommended' => 'yes',
        ],
        'test/upload' => [
            'type' => 'Folder',
            'recommended' => 'yes',
        ],
        'test/user' => [
            'type' => 'Folder',
            'recommended' => 'yes',
        ],
        'test/vtlib' => [
            'type' => 'Folder',
            'recommended' => 'yes',
        ],
        'user_privileges' => [
            'type' => 'Folder',
            'recommended' => 'yes',
        ],
        'config.inc.php' => [
            'type' => 'File',
            'recommended' => 'yes',
        ],
        'parent_tabdata.php' => [
            'type' => 'File',
            'recommended' => 'yes',
        ],
        'tabdata.php' => [
            'type' => 'File',
            'recommended' => 'yes',
        ],
        'user_privileges/default_module_view.php' => [
            'type' => 'File',
            'recommended' => 'yes',
        ],
        'user_privileges/enable_backup.php' => [
            'type' => 'File',
            'recommended' => 'yes',
        ],
    ];
    protected array $phpRequirements = [
        'error_reporting' => [
            'type' => 'ErrorReporting',
            'minimum' => '0, 1, E_ERROR',
            'recommended' => '0, 1, E_ERROR',
        ],
        'crm_version' => [
            'type' => 'CRMVersion',
            'minimum' => '',
            'recommended' => '',
        ],
        'php_version' => [
            'type' => 'PHPVersion',
            'minimum' => '',
            'recommended' => '',
        ],
        'max_execution_time' => [
            'type' => 'Number',
            'minimum' => '60',
            'recommended' => '600',
        ],
        'max_input_time' => [
            'type' => 'Number',
            'minimum' => '60',
            'recommended' => '120',
        ],
        'max_input_vars' => [
            'type' => 'Number',
            'minimum' => '10000',
            'recommended' => '10000',
        ],
        'memory_limit' => [
            'type' => 'Memory',
            'minimum' => '64M',
            'recommended' => '256M',
        ],
        'post_max_size' => [
            'type' => 'Memory',
            'minimum' => '12M',
            'recommended' => '50M',
        ],
        'upload_max_filesize' => [
            'type' => 'Memory',
            'minimum' => '2M',
            'recommended' => '5M',
        ],
        'SimpleXML' => [
            'type' => 'Extension',
            'minimum' => 'yes',
            'recommended' => 'yes',
        ],
        'gd' => [
            'type' => 'Extension',
            'recommended' => 'yes',
            'minimum' => 'yes',
        ],
        'curl' => [
            'type' => 'Extension',
            'recommended' => 'yes',
            'minimum' => 'yes',
        ],
        'imap' => [
            'type' => 'Extension',
            'minimum' => 'yes',
            'recommended' => 'yes',
        ],
        'mysql' => [
            'type' => 'Mysql',
            'minimum' => 'yes',
            'recommended' => 'yes',
        ],
        'mbstring' => [
            'type' => 'Extension',
            'info' => 'LBL_REQUIRED_PDFMAKER',
            'minimum' => 'yes',
            'recommended' => 'yes',
        ],
        'bcmath' => [
            'type' => 'Extension',
            'minimum' => 'yes',
            'recommended' => 'yes',
        ],
        'layout' => [
            'type' => 'Layout',
            'minimum' => 'd1',
            'recommended' => 'd1',
        ],
        'charset' => [
            'type' => 'Charset',
            'minimum' => 'utf-8',
            'recommended' => 'utf-8',
        ],
    ];
    protected $sqlCharset = false;
    protected $sqlMode;
    protected array $userRequirements = [
        'invalid_id' => [
            'minimum' => '0',
            'recommended' => '0',
        ],
        'invalid_role' => [
            'minimum' => '0',
            'recommended' => '0',
        ],
        'sharing_file' => [
            'minimum' => '0',
            'recommended' => '0',
        ],
        'user_file' => [
            'minimum' => '0',
            'recommended' => '0',
        ],
    ];

    /**
     * @return string
     */
    public function getButtonType(): string
    {
        return $this->buttonType;
    }

    /**
     * @param array $data
     */
    public function setButtonType(array $data): void
    {
        if ('yes' === $data['error']) {
            $this->buttonType = 'danger';
        } elseif ('danger' !== $this->buttonType && 'yes' === $data['warning']) {
            $this->buttonType = 'warning';
        }
    }

    public function getCRMVersion(): string
    {
        return Vtiger_Version::current();
    }

    /**
     * @return array
     */
    public function getDBSettings(): array
    {
        return $this->dbRequirements;
    }

    /**
     * @return array
     */
    public function getFilePermissions(): array
    {
        return $this->filePermissions;
    }

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        $self = new self();
        $self->retrievePHPSettings();
        $self->retrieveDBSettings();
        $self->retrieveFilePermissions();
        $self->retrieveUserSettings();

        return $self;
    }

    /**
     * @return array
     */
    public function getPHPSettings(): array
    {
        return $this->phpRequirements;
    }

    public function getPHPVersion(): float
    {
        return floatval(phpversion());
    }

    /**
     * @param mixed $vtVersion
     * @return array
     */
    public function getPHPVersionMap(mixed $vtVersion = false): array
    {
        $versions = [
            '8.2' => [
                'error' => [5.0, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 7, 7.0, 7.1, 7.2, 7.3, 7.4, 8, 8.0,],
                'warning' => [],
                'recommended' => [8.1, 8.2],
            ],
            '8.1' => [
                'error' => [5.0, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 7, 7.0, 7.1, 7.2, 7.3, 7.4, 8, 8.0,],
                'warning' => [],
                'recommended' => [8.1, 8.2],
            ],
            8 => [
                'error' => [5.6, 5.0, 5.1, 5.2, 5.3, 5.4, 5.5, 7, 7.0, 7.1],
                'warning' => [7.2, 7.3],
                'recommended' => [7.4, 8, 8.0, 8.1, 8.2],
            ],
            '7.5' => [
                'error' => [5.0, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 7, 7.0,],
                'warning' => [7.1],
                'recommended' => [7.2, 7.3, 7.4, 8, 8.0, 8.1],
            ],
            '7.4' => [
                'error' => [5.0, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 7, 7.0,],
                'warning' => [7.1],
                'recommended' => [7.2, 7.3, 7.4],
            ],
            7 => [
                'error' => [5.0, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 7, 7.0,],
                'warning' => [7.1],
                'recommended' => [7.2, 7.3],
            ],
            6 => [
                'recommended' => [5.6],
            ],
        ];

        if (!empty($versions[(string)(float)$vtVersion])) {
            return $versions[(string)(float)$vtVersion];
        }

        if (!empty($versions[(int)$vtVersion])) {
            return $versions[(int)$vtVersion];
        }

        return [
            'error' => [5.0, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 7, 7.0, 7.1, 7.2, 7.3, 7.4, 8, 8.0],
            'warning' => [8.1, 8.2],
            'recommended' => [8.3, 8.4],
        ];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getSQLMode(): array
    {
        if (!$this->sqlMode) {
            $adb = PearDatabase::getInstance();
            $result = $adb->query('SELECT @@GLOBAL.sql_mode AS global, @@SESSION.sql_mode AS session');
            $row = $adb->query_result_rowdata($result);

            $this->sqlMode = array_filter(
                array_unique(
                    array_merge(
                        explode(',', $row['global']),
                        explode(',', $row['session']),
                    ),
                ),
            );
        }

        return $this->sqlMode;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getSqlCharset(): string
    {
        if (!$this->sqlCharset) {
            $adb = PearDatabase::getInstance();
            $result = $adb->query('SELECT @@collation_database as charset');
            $row = $adb->query_result_rowdata($result);

            $this->sqlCharset = $row['charset'];
        }

        return $this->sqlCharset;
    }

    /**
     * @return array
     */
    public function getUserSettings(): array
    {
        return $this->userRequirements;
    }

    /**
     * @param array $data
     * @return string
     */
    public function getValue(array &$data): string
    {
        $function = sprintf('getValue%s', $data['type']);

        if (method_exists($this, $function)) {
            return $this->$function($data);
        }

        return ini_get($data['name']);
    }

    public function getValueCRMVersion(&$data): string
    {
        $data['minimum'] = '1.0';
        $data['recommended'] = '1.0';

        return $this->getCRMVersion();
    }

    public function getValueCharset(): string
    {
        global $default_charset;

        return strtolower($default_charset);
    }

    /**
     * @param array $data
     * @return string
     */
    public function getValueDieOnError(array $data): string
    {
        return PearDatabase::getInstance()->dieOnError ? 'yes' : 'no';
    }

    public function getValueErrorReporting(array $data): int
    {
        return intval(ini_get('error_reporting'));
    }

    /**
     * @param array $data
     * @return string
     */
    public function getValueExtension(array $data): string
    {
        $extensions = get_loaded_extensions();

        return in_array($data['name'], $extensions) ? 'yes' : 'no';
    }

    /**
     * @param array $data
     * @return string
     */
    public function getValueFile(array $data): string
    {
        return is_writable($data['name']) ? 'yes' : 'no';
    }

    /**
     * @param array $data
     * @return string
     */
    public function getValueFolder(array $data): string
    {
        return $this->isWritableFolder($data['name']) ? 'yes' : 'no';
    }

    public function getValueLayout(): string
    {
        return Vtiger_Viewer::getDefaultLayoutName();
    }

    /**
     * @param array $data
     * @return string
     */
    public function getValueMysql(array $data): string
    {
        $extensions = get_loaded_extensions();

        return (in_array('mysql', $extensions) || in_array('mysqli', $extensions)) ? 'yes' : 'no';
    }

    /**
     * @param array $data
     * @return string
     * @throws Exception
     */
    public function getValueMysqlStrictMode(array $data): string
    {
        return in_array('STRICT_TRANS_TABLES', $this->getSQLMode()) ? 'yes' : 'no';
    }

    public function getValuePHPVersion(array &$data): float
    {
        $versions = $this->getPHPVersionMap(Vtiger_Version::current());
        $minimum = array_merge((array)$versions['recommended'], (array)$versions['warning']);

        sort($minimum);

        $data['minimum'] = implode(', ', $minimum);
        $data['recommended'] = implode(', ', $versions['recommended']);

        return $this->getPHPVersion();
    }

    /**
     * @param array $data
     * @throws Exception
     */
    public function getValueSqlCharset(array $data): string
    {
        return $this->getSqlCharset();
    }

    /**
     * @param array $data
     * @return string
     * @throws Exception
     */
    public function getValueSqlMode(array $data): string
    {
        return implode(',', $this->getSQLMode());
    }

    /**
     * @return array
     */
    public function getValueUsers(): array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT id,roleid FROM vtiger_users LEFT JOIN vtiger_user2role ON vtiger_user2role.userid=vtiger_users.id WHERE status=?', ['Active']);
        $data = [
            'invalid_id' => 0,
            'invalid_role' => 0,
            'sharing_file' => 0,
            'user_file' => 0,
        ];

        while ($row = $adb->fetchByAssoc($result)) {
            $userId = $row['id'];
            $userFile = 'user_privileges/user_privileges_' . $userId . '.php';
            $sharingFile = 'user_privileges/sharing_privileges_' . $userId . '.php';

            if (empty($row['roleid'])) {
                $data['invalid_role']++;
            }
            if (!is_file($sharingFile)) {
                $data['sharing_file']++;
            }
            if (!is_file($userFile)) {
                $data['user_file']++;
            } else {
                require $userFile;

                if (isset($user_info) && $user_info['id'] != $userId) {
                    $data['invalid_id']++;
                }
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @return string
     */
    public function isError(array &$data): string
    {
        $function = sprintf('isError%s', $data['type']);

        if (method_exists($this, $function)) {
            return $this->$function($data);
        }

        return $this->isPHPError($data);
    }

    public function isErrorCRMVersion(array $data): string
    {
        return 'no';
    }

    public function isErrorErrorReporting(array $data): string
    {
        return $this->isWarningErrorReporting($data);
    }

    public function isErrorPHPVersion(array $data): string
    {
        $phpVersion = $this->getPHPVersion();
        $versions = $this->getPHPVersionMap(Vtiger_Version::current());

        if (in_array($phpVersion, $versions['error'])) {
            return 'yes';
        }

        return 'no';
    }

    public function isErrorSqlCharset(array $data): false
    {
        return false;
    }

    /**
     * @param array $data
     * @return string
     * @throws Exception
     */
    public function isErrorSqlMode(array $data): string
    {
        $sqlMode = $this->getValueSqlMode($data);

        return !(empty($sqlMode) || 'NO_ENGINE_SUBSTITUTION' === trim($sqlMode, ',')) ? 'yes' : 'no';
    }

    /**
     * @param array $data
     * @return bool
     */
    public function isFileError(array $data): string
    {
        if (isset($data['minimum'])) {
            return $this->isLessThan($data['current'], $data['minimum'], $data['type']);
        }

        return 'no';
    }

    /**
     * @param array $data
     * @return string
     */
    public function isFileWarning(array $data): string
    {
        if (isset($data['recommended'])) {
            return $this->isLessThan($data['current'], $data['recommended'], $data['type']);
        }

        return 'no';
    }

    /**
     * @param string $val1
     * @param string $val2
     * @param string $type
     * @return string
     */
    public function isLessThan($val1, $val2, $type): string
    {
        if ('Number' === $type) {
            $result = $val1 < $val2;
        } elseif ('Memory' === $type) {
            $val1 = $this->toBytes($val1);
            $val2 = $this->toBytes($val2);

            $result = $val1 < $val2;
        } elseif (!empty($val2)) {
            $result = $val1 !== $val2;
        } else {
            $result = false;
        }

        return $result ? 'yes' : 'no';
    }

    /**
     * @param array $data
     * @return string
     */
    public function isPHPError(array $data): string
    {
        if ($this->isUnlimited($data)) {
            return 'no';
        }

        return $this->isLessThan($data['current'], $data['minimum'], $data['type']);
    }

    /**
     * @param array $data
     * @return string
     */
    public function isPHPWarning(array $data): string
    {
        if ($this->isUnlimited($data)) {
            return 'no';
        }

        return $this->isLessThan($data['current'], $data['recommended'], $data['type']);
    }

    public function isUnlimited(array $data): bool
    {
        return 'Number' === $data['type'] && 0 >= $data['current'];
    }

    /**
     * @param array $data
     * @return string
     */
    public function isWarning(array &$data): string
    {
        $function = sprintf('isWarning%s', $data['type']);

        if (method_exists($this, $function)) {
            return $this->$function($data);
        }

        return $this->isPHPWarning($data);
    }

    public function isWarningCRMVersion(array $data): string
    {
        return (float)$this->getCRMVersion() < $data['minimum'] ? 'yes' : 'no';
    }

    public function isWarningErrorReporting(array $data): string
    {
        $errorLevel = $this->getValueErrorReporting($data);

        return !in_array($errorLevel, [0, 1]) ? 'yes' : 'no';
    }

    public function isWarningPHPVersion(array $data): string
    {
        $phpVersion = $this->getPHPVersion();
        $versions = $this->getPHPVersionMap(Vtiger_Version::current());

        if (in_array($phpVersion, $versions['warning'])) {
            return 'yes';
        }

        if (!in_array($phpVersion, $versions['recommended'])) {
            return 'yes';
        }

        return 'no';
    }

    public function isWarningSqlCharset(array $data): string
    {
        if (in_array($this->sqlCharset, ['utf8mb4_general_ci', 'utf8mb3_general_ci', 'utf8_general_ci'])) {
            return 'no';
        }

        return 'yes';
    }

    /**
     * @param array $data
     * @return string
     * @throws Exception
     */
    public function isWarningSqlMode(array $data): string
    {
        return $this->isErrorSqlMode($data);
    }

    public function isWritableFolder(string $dir): bool
    {
        if (!is_writable($dir)) {
            return false;
        }

        if ('SubFolders' === $_REQUEST['scan']) {
            $files = scandir($dir);

            foreach ($files as $file) {
                if (!in_array($file, [".", ".."])) {
                    $newDir = $dir . DIRECTORY_SEPARATOR . $file;

                    if (is_dir($newDir)) {
                        if (!$this->isWritableFolder($newDir)) {
                            return false;
                        }
                    }

                    if (!is_writable($newDir)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function retrieveDBSettings(): void
    {
        foreach ($this->getDBSettings() as $key => $data) {
            $data['name'] = $key;
            $data['current'] = $this->getValue($data);
            $data['error'] = $this->isError($data);
            $data['warning'] = $this->isWarning($data);

            $this->setButtonType($data);
            $this->setDBSettings($key, $data);
        }
    }

    public function retrieveFilePermissions(): void
    {
        foreach ($this->getFilePermissions() as $name => $data) {
            $data['name'] = $name;
            $data['current'] = $this->getValue($data);
            $data['error'] = $this->isFileError($data);
            $data['warning'] = $this->isFileWarning($data);

            $this->setButtonType($data);
            $this->setFilePermission($name, $data);
        }
    }

    public function retrievePHPSettings(): void
    {
        foreach ($this->getPHPSettings() as $key => $data) {
            $data['name'] = $key;
            $data['current'] = $this->getValue($data);
            $data['error'] = $this->isError($data);
            $data['warning'] = $this->isWarning($data);

            $this->setButtonType($data);
            $this->setPHPSettings($key, $data);
        }
    }

    public function retrieveUserSettings(): void
    {
        $usersData = $this->getValueUsers();

        foreach ($this->getUserSettings() as $key => $data) {
            $value = $usersData[$key];
            $data['name'] = $key;
            $data['current'] = $value ? $value : '0';
            $data['error'] = $value > 0 ? 'yes' : 'no';

            $this->setButtonType($data);
            $this->setUserSettings($key, $data);
        }
    }

    /**
     * @param string $key
     * @param array $data
     */
    public function setDBSettings(string $key, array $data): void
    {
        $this->dbRequirements[$key] = $data;
    }

    /**
     * @param string $key
     * @param array $data
     */
    public function setFilePermission(string $key, array $data): void
    {
        $this->filePermissions[$key] = $data;
    }

    /**
     * @param string $key
     * @param array $data
     */
    public function setPHPSettings(string $key, array $data): void
    {
        $this->phpRequirements[$key] = $data;
    }

    /**
     * @param string $key
     * @param array $data
     */
    public function setUserSettings(string $key, array $data): void
    {
        $this->userRequirements[$key] = $data;
    }

    /**
     * @param string $str
     * @return int
     */
    public function toBytes(string $str): int
    {
        $val = trim($str);
        $last = strtolower($str[strlen($str) - 1]);

        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }
}
