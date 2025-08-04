<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ITS4YouEmails_EmailLookup_Model extends Vtiger_Base_Model
{
    public function delete($crmId, $fieldId = false)
    {
        $db = PearDatabase::getInstance();

        if ($fieldId) {
            $params = [$crmId, $fieldId];
            $db->pquery('DELETE FROM vtiger_emailslookup WHERE crmid=? AND fieldid=?', $params);
        } else {
            $params = [$crmId];
            $db->pquery('DELETE FROM vtiger_emailslookup WHERE crmid=?', $params);
        }
    }

    public static function getInstance()
    {
        return new self();
    }

    public function recieve($fieldId, $values)
    {
        $db = PearDatabase::getInstance();
        $params = [$values['crmid'], $values['setype'], $values[$fieldId], $fieldId];
        $db->pquery(
            'INSERT INTO vtiger_emailslookup (crmid, setype, value, fieldid) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)',
            $params
        );
    }
}