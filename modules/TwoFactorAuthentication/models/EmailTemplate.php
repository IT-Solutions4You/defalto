<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class TwoFactorAuthentication_EmailTemplate_Model extends Core_DatabaseData_Model
{
    protected string $table = 'df_two_factor_email_template';
    protected string $tableId = 'template_key';

    private function getEmailTemplateTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    public static function getInstance(): self
    {
        return (new self())->retrieveDB();
    }

    public function deleteByKey(string $key): void
    {
        $this->getEmailTemplateTable()->deleteData(['template_key' => $key]);
    }

    public function retrieveByKey(string $key): ?array
    {
        $db = $this->getDB();
        $result = $db->pquery(
            'SELECT subject, body FROM ' . $this->table . ' WHERE template_key = ?',
            [$key]
        );

        if (!$result || !$db->num_rows($result)) {
            return null;
        }

        return [
            'subject' => (string)$db->query_result($result, 0, 'subject'),
            'body' => (string)$db->query_result($result, 0, 'body'),
        ];
    }

    public function saveTemplate(string $key, string $subject, string $body): void
    {
        $table = $this->getEmailTemplateTable();
        $modifiedTime = date('Y-m-d H:i:s');

        if ($this->retrieveByKey($key)) {
            $table->updateData(
                [
                    'subject' => $subject,
                    'body' => $body,
                    'modifiedtime' => $modifiedTime,
                ],
                ['template_key' => $key]
            );

            return;
        }

        $table->insertData([
            'template_key' => $key,
            'subject' => $subject,
            'body' => $body,
            'modifiedtime' => $modifiedTime,
        ]);
    }
}
