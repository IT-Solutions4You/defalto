<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class TwoFactorAuthentication_Config_Model extends Core_DatabaseData_Model
{
    private const CONFIG_ID = 1;

    protected string $table = 'df_two_factor_config';
    protected string $tableId = 'id';

    private function getConfigTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    public static function getInstance(): self
    {
        return (new self())->retrieveDB();
    }

    public function retrieve(): ?array
    {
        return $this->getConfigTable()->selectData([], ['id' => self::CONFIG_ID]);
    }

    public function retrieveAllowedMethods(): ?array
    {
        $row = $this->retrieve();

        if ($row === null) {
            return null;
        }

        return [
            'allow_email' => (int)$row['allow_email'],
            'allow_totp' => (int)$row['allow_totp'],
        ];
    }

    public function retrieveEnforceMode(): ?string
    {
        $row = $this->retrieve();

        return $row === null ? null : (string)$row['enforce_mode'];
    }

    public function updateAllowedMethods(bool $email, bool $totp): void
    {
        $this->getConfigTable()->updateData(
            [
                'allow_email' => $email ? 1 : 0,
                'allow_totp' => $totp ? 1 : 0,
                'modifiedtime' => date('Y-m-d H:i:s'),
            ],
            ['id' => self::CONFIG_ID]
        );
    }

    public function updateEnforceMode(string $mode): void
    {
        $this->getConfigTable()->updateData(
            [
                'enforce_mode' => $mode,
                'modifiedtime' => date('Y-m-d H:i:s'),
            ],
            ['id' => self::CONFIG_ID]
        );
    }
}
