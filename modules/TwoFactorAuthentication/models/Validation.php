<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class TwoFactorAuthentication_Validation_Model extends Vtiger_Base_Model
{
    private const STATUS_SUCCESS = 'success';
    private const STATUS_INFO = 'info';
    private const STATUS_WARNING = 'warning';
    private const STATUS_DANGER = 'danger';

    private function checkConfiguration(array $config): array
    {
        $valid = $config['ready']
            && in_array((string)$config['row']['enforce_mode'], ['all', 'off'], true);

        return $this->createCheck(
            $valid ? self::STATUS_SUCCESS : self::STATUS_DANGER,
            'LBL_VALIDATION_CONFIGURATION',
            $valid ? 'LBL_VALIDATION_OK' : 'LBL_VALIDATION_CONFIG_INVALID',
            $config['ready'] ? 'enforce_mode=' . (string)$config['row']['enforce_mode'] : ''
        );
    }

    private function checkLoginModifier(): array
    {
        if (!Vtiger_Utils::checkTable('df_modifiers')) {
            return $this->createCheck(
                self::STATUS_DANGER,
                'LBL_VALIDATION_LOGIN_MODIFIER',
                'LBL_VALIDATION_LOGIN_MODIFIER_INVALID'
            );
        }

        $modifiers = Core_Modifiers_Model::getAll('Users');
        $valid = in_array(
            'TwoFactorAuthentication_LoginAction_Modifier',
            $modifiers['LoginAction'] ?? [],
            true
        );

        return $this->createCheck(
            $valid ? self::STATUS_SUCCESS : self::STATUS_DANGER,
            'LBL_VALIDATION_LOGIN_MODIFIER',
            $valid ? 'LBL_VALIDATION_OK' : 'LBL_VALIDATION_LOGIN_MODIFIER_INVALID'
        );
    }

    private function checkLoginRuntime(): array
    {
        $files = [
            'modules/Users/actions/Login.php' => ['modifyVariableForClass', 'Users_Authentication_Model::completeLogin'],
            'modules/Users/models/Authentication.php' => ['class Users_Authentication_Model', 'completeLogin'],
        ];
        $valid = true;

        foreach ($files as $file => $needles) {
            if (!$this->fileContainsAll($file, $needles)) {
                $valid = false;
                break;
            }
        }

        return $this->createCheck(
            $valid ? self::STATUS_SUCCESS : self::STATUS_DANGER,
            'LBL_VALIDATION_LOGIN_RUNTIME',
            $valid ? 'LBL_VALIDATION_OK' : 'LBL_VALIDATION_LOGIN_RUNTIME_INVALID',
            implode(', ', array_keys($files))
        );
    }

    private function checkMailer(array $config): array
    {
        if (!$config['ready'] || (int)$config['row']['allow_email'] !== 1) {
            return $this->createCheck(
                self::STATUS_INFO,
                'LBL_VALIDATION_EMAILS_EXTENSION',
                'LBL_VALIDATION_NOT_REQUIRED'
            );
        }

        $valid = false;

        try {
            $valid = vtlib_isModuleActive('ITS4YouEmails')
                && ITS4YouEmails_Module_Model::isPHPMailerInstalled();
        } catch (Throwable $e) {
            $valid = false;
        }

        return $this->createCheck(
            $valid ? self::STATUS_SUCCESS : self::STATUS_WARNING,
            'LBL_VALIDATION_EMAILS_EXTENSION',
            $valid ? 'LBL_VALIDATION_OK' : 'LBL_VALIDATION_EMAILS_EXTENSION_INVALID'
        );
    }

    private function checkMethods(array $config): array
    {
        $methods = [];

        if ($config['ready']) {
            if ((int)$config['row']['allow_email'] === 1) {
                $methods[] = TwoFactorAuthentication_Service_Helper::METHOD_EMAIL;
            }
            if ((int)$config['row']['allow_totp'] === 1) {
                $methods[] = TwoFactorAuthentication_Service_Helper::METHOD_TOTP;
            }
        }

        return $this->createCheck(
            $methods ? self::STATUS_SUCCESS : self::STATUS_DANGER,
            'LBL_VALIDATION_METHODS',
            $methods ? 'LBL_VALIDATION_OK' : 'LBL_VALIDATION_METHODS_MISSING',
            implode(', ', $methods)
        );
    }

    private function checkModule(): array
    {
        $active = false;

        try {
            $active = function_exists('vtlib_isModuleActive')
                && vtlib_isModuleActive('TwoFactorAuthentication');
        } catch (Throwable $e) {
            $active = false;
        }

        return $this->createCheck(
            $active ? self::STATUS_SUCCESS : self::STATUS_DANGER,
            'LBL_VALIDATION_MODULE',
            $active ? 'LBL_VALIDATION_OK' : 'LBL_VALIDATION_NOT_ACTIVE'
        );
    }

    private function checkOutgoingMail(array $config): array
    {
        if (!$config['ready'] || (int)$config['row']['allow_email'] !== 1) {
            return $this->createCheck(
                self::STATUS_INFO,
                'LBL_VALIDATION_MAIL',
                'LBL_VALIDATION_NOT_REQUIRED'
            );
        }

        $configured = TwoFactorAuthentication_Service_Helper::isOutgoingMailConfigured();

        return $this->createCheck(
            $configured ? self::STATUS_SUCCESS : self::STATUS_WARNING,
            'LBL_VALIDATION_MAIL',
            $configured ? 'LBL_VALIDATION_OK' : 'LBL_VALIDATION_MAIL_MISSING'
        );
    }

    private function checkSchema(array $schema): array
    {
        return $this->createCheck(
            $schema['ready'] ? self::STATUS_SUCCESS : self::STATUS_DANGER,
            'LBL_VALIDATION_SCHEMA',
            $schema['ready'] ? 'LBL_VALIDATION_OK' : 'LBL_VALIDATION_MISSING',
            implode(', ', $schema['missing'])
        );
    }

    private function checkTotpRuntime(array $config): array
    {
        if (!$config['ready'] || (int)$config['row']['allow_totp'] !== 1) {
            return $this->createCheck(
                self::STATUS_INFO,
                'LBL_VALIDATION_TOTP_RUNTIME',
                'LBL_VALIDATION_NOT_REQUIRED'
            );
        }

        $available = TwoFactorAuthentication_Service_Helper::isTotpMethodAvailable();
        $hasEmailFallback = TwoFactorAuthentication_Service_Helper::isEmailMethodAvailable();

        return $this->createCheck(
            $available ? self::STATUS_SUCCESS : ($hasEmailFallback ? self::STATUS_WARNING : self::STATUS_DANGER),
            'LBL_VALIDATION_TOTP_RUNTIME',
            $available ? 'LBL_VALIDATION_OK' : 'LBL_VALIDATION_TOTP_RUNTIME_INVALID'
        );
    }

    private function createCheck(
        string $status,
        string $label,
        string $message,
        string $details = ''
    ): array {
        return [
            'status' => $status,
            'label' => $label,
            'message' => $message,
            'details' => $details,
        ];
    }

    private function fileContainsAll(string $path, array $needles): bool
    {
        if (!is_file($path)) {
            return false;
        }

        $content = (string)file_get_contents($path);

        foreach ($needles as $needle) {
            if (!str_contains($content, $needle)) {
                return false;
            }
        }

        return true;
    }

    public static function getInstance(): self
    {
        return new self();
    }

    private function retrieveConfigState(): array
    {
        $row = TwoFactorAuthentication_Config_Model::getInstance()->retrieve();

        if ($row === null) {
            return ['ready' => false, 'row' => []];
        }

        return ['ready' => true, 'row' => $row];
    }

    private function retrieveSchemaState(): array
    {
        $schema = [
            'df_two_factor_config' => ['id', 'enforce_mode', 'allow_email', 'allow_totp', 'modifiedtime'],
            'df_two_factor_user' => ['userid', 'method', 'secret', 'exempt', 'enrolled', 'totp_last_step', 'failed_attempts', 'locked_until', 'createdtime', 'modifiedtime'],
            'df_two_factor_backup_code' => ['id', 'userid', 'code_hash', 'used', 'createdtime'],
            'df_two_factor_email_template' => ['template_key', 'subject', 'body', 'modifiedtime'],
        ];
        $missing = [];

        foreach ($schema as $table => $columns) {
            if (!Vtiger_Utils::checkTable($table)) {
                $missing[] = $table;
                continue;
            }

            foreach ($columns as $column) {
                if (!columnExists($column, $table)) {
                    $missing[] = $table . '.' . $column;
                }
            }
        }

        return ['ready' => !$missing, 'missing' => $missing];
    }

    public function retrieveValidation(): array
    {
        $schema = $this->retrieveSchemaState();
        $config = $schema['ready'] ? $this->retrieveConfigState() : ['ready' => false, 'row' => []];
        $checks = [
            $this->checkModule(),
            $this->checkSchema($schema),
            $this->checkConfiguration($config),
            $this->checkMethods($config),
            $this->checkLoginRuntime(),
            $this->checkLoginModifier(),
            $this->checkTotpRuntime($config),
            $this->checkOutgoingMail($config),
            $this->checkMailer($config),
        ];
        $errors = 0;
        $warnings = 0;

        foreach ($checks as $check) {
            if ($check['status'] === self::STATUS_DANGER) {
                $errors++;
            } elseif ($check['status'] === self::STATUS_WARNING) {
                $warnings++;
            }
        }

        return [
            'checks' => $checks,
            'errors' => $errors,
            'warnings' => $warnings,
            'schema_ready' => $schema['ready'],
            'config_ready' => $config['ready'],
        ];
    }
}
