<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class TwoFactorAuthentication_BackupCode_Model extends Core_DatabaseData_Model
{
    protected string $table = 'df_two_factor_backup_code';
    protected string $tableId = 'id';

    private function getBackupCodeTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    public static function getInstance(): self
    {
        return (new self())->retrieveDB();
    }

    public function countUnusedByUserId(int $userId): int
    {
        $db = $this->getDB();
        $result = $db->pquery(
            'SELECT COUNT(*) AS cnt FROM ' . $this->table . ' WHERE userid = ? AND used = 0',
            [$userId]
        );

        return (int)$db->query_result($result, 0, 'cnt');
    }

    public function deleteByUserId(int $userId): void
    {
        $this->getBackupCodeTable()->deleteData(['userid' => $userId]);
    }

    public function retrieveUnusedByUserId(int $userId): array
    {
        $db = $this->getDB();
        $result = $db->pquery(
            'SELECT id, code_hash FROM ' . $this->table . ' WHERE userid = ? AND used = 0',
            [$userId]
        );
        $codes = [];

        while ($row = $db->fetchByAssoc($result)) {
            $codes[] = $row;
        }

        return $codes;
    }

    public function saveHashes(int $userId, array $hashes): void
    {
        $this->deleteByUserId($userId);
        $table = $this->getBackupCodeTable();
        $createdTime = date('Y-m-d H:i:s');

        foreach ($hashes as $hash) {
            $table->insertData([
                'userid' => $userId,
                'code_hash' => (string)$hash,
                'used' => 0,
                'createdtime' => $createdTime,
            ]);
        }
    }

    public function updateUsed(int $id): bool
    {
        $db = $this->getDB();
        $result = $db->pquery(
            'UPDATE ' . $this->table . ' SET used = 1 WHERE id = ? AND used = 0',
            [$id]
        );

        return $db->getAffectedRowCount($result) === 1;
    }
}
