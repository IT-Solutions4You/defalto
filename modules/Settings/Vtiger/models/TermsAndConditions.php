<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Vtiger_TermsAndConditions_Model extends Vtiger_Base_Model
{
    const tableName = 'vtiger_inventory_tandc';

    public function getText()
    {
        return $this->get('tandc');
    }

    public function setText($text)
    {
        return $this->set('tandc', $text);
    }

    public function getType()
    {
        return $this->get('type');
    }

    public function setType($type)
    {
        return $this->set('type', $type);
    }

    public function save()
    {
        $db = PearDatabase::getInstance();
        $type = $this->getType();

        $query = 'SELECT 1 FROM ' . self::tableName . ' WHERE type = ?';
        $result = $db->pquery($query, [$type]);
        if ($db->num_rows($result) > 0) {
            $query = 'UPDATE ' . self::tableName . ' SET tandc = ? WHERE type = ?';
            $params = [$this->getText(), $type];
        } else {
            $query = 'INSERT INTO ' . self::tableName . ' (id,type,tandc) VALUES(?,?,?)';
            $params = [$db->getUniqueID(self::tableName), $type, $this->getText()];
        }
        $result = $db->pquery($query, $params);
    }

    public static function getInstance($moduleName)
    {
        $db = PearDatabase::getInstance();

        $query = 'SELECT tandc FROM ' . self::tableName . ' WHERE type = ?';
        $result = $db->pquery($query, [$moduleName]);
        $instance = new self();
        if ($db->num_rows($result) > 0) {
            $text = $db->query_result($result, 0, 'tandc');
            $instance->setText($text);
            $instance->setType($moduleName);
        }

        return $instance;
    }
}