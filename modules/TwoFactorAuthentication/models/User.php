<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class TwoFactorAuthentication_User_Model extends Core_DatabaseData_Model
{
    protected string $table = 'df_two_factor_user';
    protected string $tableId = 'userid';

    private function getUserTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    public static function getInstance(): self
    {
        return (new self())->retrieveDB();
    }

    public function clearFailures(int $userId): void
    {
        $this->getUserTable()->updateData(
            [
                'failed_attempts' => 0,
                'locked_until' => null,
            ],
            ['userid' => $userId]
        );
    }

    public function reset(int $userId): void
    {
        $this->getUserTable()->updateData(
            [
                'secret' => null,
                'enrolled' => 0,
                'method' => '',
                'failed_attempts' => 0,
                'locked_until' => null,
                'modifiedtime' => date('Y-m-d H:i:s'),
            ],
            ['userid' => $userId]
        );
    }

    public function retrieveByUserId(int $userId): ?array
    {
        return $this->getUserTable()->selectData([], ['userid' => $userId]);
    }

    public function retrieveUsersOverview(): array
    {
        $db = $this->getDB();
        $result = $db->pquery(
            "SELECT u.id, u.user_name, u.first_name, u.last_name, u.is_admin,
                    COALESCE(f.method, '') AS method, COALESCE(f.enrolled, 0) AS enrolled,
                    COALESCE(f.exempt, 0) AS exempt,
                    (SELECT COUNT(*) FROM df_two_factor_backup_code b WHERE b.userid = u.id AND b.used = 0) AS backup_count
             FROM vtiger_users u
             LEFT JOIN " . $this->table . " f ON f.userid = u.id
             WHERE u.status = 'Active'
             ORDER BY u.user_name",
            []
        );
        $users = [];

        while ($row = $db->fetchByAssoc($result)) {
            $users[] = $row;
        }

        return $users;
    }

    public function saveExemption(int $userId, bool $exempt): void
    {
        $table = $this->getUserTable();
        $modifiedTime = date('Y-m-d H:i:s');

        if ($this->retrieveByUserId($userId)) {
            $table->updateData(
                [
                    'exempt' => $exempt ? 1 : 0,
                    'modifiedtime' => $modifiedTime,
                ],
                ['userid' => $userId]
            );

            return;
        }

        $table->insertData([
            'userid' => $userId,
            'method' => '',
            'exempt' => $exempt ? 1 : 0,
            'createdtime' => $modifiedTime,
            'modifiedtime' => $modifiedTime,
        ]);
    }

    public function saveMethod(int $userId, string $method): void
    {
        $table = $this->getUserTable();
        $modifiedTime = date('Y-m-d H:i:s');

        if ($this->retrieveByUserId($userId)) {
            $table->updateData(
                [
                    'method' => $method,
                    'modifiedtime' => $modifiedTime,
                ],
                ['userid' => $userId]
            );

            return;
        }

        $table->insertData([
            'userid' => $userId,
            'method' => $method,
            'createdtime' => $modifiedTime,
            'modifiedtime' => $modifiedTime,
        ]);
    }

    public function saveTotpEnrollment(
        int $userId,
        string $method,
        string $encryptedSecret,
        ?int $timeSlice
    ): void {
        $table = $this->getUserTable();
        $modifiedTime = date('Y-m-d H:i:s');

        if ($this->retrieveByUserId($userId)) {
            $table->updateData(
                [
                    'method' => $method,
                    'secret' => $encryptedSecret,
                    'enrolled' => 1,
                    'totp_last_step' => $timeSlice,
                    'modifiedtime' => $modifiedTime,
                ],
                ['userid' => $userId]
            );

            return;
        }

        $table->insertData([
            'userid' => $userId,
            'method' => $method,
            'secret' => $encryptedSecret,
            'enrolled' => 1,
            'totp_last_step' => $timeSlice,
            'createdtime' => $modifiedTime,
            'modifiedtime' => $modifiedTime,
        ]);
    }

    public function updateFailureState(int $userId, int $maxAttempts, int $lockTtl): void
    {
        $maxAttempts = max(1, $maxAttempts);
        $lockTtl = max(1, $lockTtl);
        $sql =
            'INSERT INTO ' . $this->table . ' (userid, method, failed_attempts, createdtime, modifiedtime)
             VALUES (?, \'\', 1, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                locked_until = IF(failed_attempts + 1 >= ' . $maxAttempts . ', DATE_ADD(NOW(), INTERVAL ' . $lockTtl . ' SECOND), IF(locked_until < NOW(), NULL, locked_until)),
                failed_attempts = IF(failed_attempts + 1 >= ' . $maxAttempts . ', 0, failed_attempts + 1),
                modifiedtime = NOW()';

        $this->getDB()->pquery($sql, [$userId]);
    }

    public function updateSecret(int $userId, string $encryptedSecret): void
    {
        $this->getUserTable()->updateData(
            [
                'secret' => $encryptedSecret,
                'modifiedtime' => date('Y-m-d H:i:s'),
            ],
            ['userid' => $userId]
        );
    }

    public function updateTotpLastStep(int $userId, int $timeSlice): bool
    {
        $db = $this->getDB();
        $result = $db->pquery(
            'UPDATE ' . $this->table . '
             SET totp_last_step = ?
             WHERE userid = ? AND (totp_last_step IS NULL OR totp_last_step < ?)',
            [$timeSlice, $userId, $timeSlice]
        );

        return $db->getAffectedRowCount($result) === 1;
    }
}
