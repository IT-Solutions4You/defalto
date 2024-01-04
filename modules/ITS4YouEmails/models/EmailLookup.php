<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouEmails_EmailLookup_Model extends Vtiger_Base_Model
{
    public function delete($crmId, $fieldId = false)
    {
        $db = PearDatabase::getInstance();

        if ($fieldId) {
            $params = array($crmId, $fieldId);
            $db->pquery('DELETE FROM vtiger_emailslookup WHERE crmid=? AND fieldid=?', $params);
        } else {
            $params = array($crmId);
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
        $params = array($values['crmid'], $values['setype'], $values[$fieldId], $fieldId);
        $db->pquery(
            'INSERT INTO vtiger_emailslookup (crmid, setype, value, fieldid) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)',
            $params
        );
    }

}