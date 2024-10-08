<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Vtiger_Systems_Model extends Vtiger_Base_Model{

    const tableName = 'vtiger_systems';

    public function getId() {
        return $this->get('id');
    }

    public function isSmtpAuthEnabled()
    {
        $smtp_auth_value = $this->get('smtp_auth');

        return $smtp_auth_value == 'on' || $smtp_auth_value == 1 || $smtp_auth_value == 'true';
    }

    /**
     * @throws AppException
     */
    public function save($request)
    {
        $db = PearDatabase::getInstance();
        $id = $this->getId();
        $server_password = $this->get('server_password');

        if ($id) {
            if (!Vtiger_Functions::isProtectedText($server_password)) {
                $server_password = Vtiger_Functions::toProtectedText($server_password);
            }
        } else {
            $server_password = Vtiger_Functions::toProtectedText($server_password);
        }

        $params = [
            'server' => $this->get('server'),
            'server_port' => $this->get('server_port'),
            'server_username' => $this->get('server_username'),
            'server_password' => $server_password,
            'server_type' => $this->get('server_type'),
            'smtp_auth' => $this->isSmtpAuthEnabled(),
            'server_path' => $this->get('server_path'),
            'from_email_field' => $this->get('from_email_field'),
            'client_id' => $this->get('client_id'),
            'client_secret' => $this->get('client_secret'),
            'client_token' => $this->get('client_token'),
        ];
        $table = $this->getSystemTable();

        if (empty($id)) {
            $id = $db->getUniqueID(self::tableName);
            $params['id'] = $id;
            $table->insertData($params);
        } else {
            $table->updateData($params, ['id' => $id]);
        }

        return $id;
    }

    public function getSystemTable(): Core_DatabaseData_Model
    {
        return (new Core_DatabaseData_Model())->getTable(self::tableName, 'id');
    }

    public function createTables(): void
    {
        $this->getSystemTable()
            ->createTable('id')
            ->createColumn('server', 'varchar(100) DEFAULT NULL')
            ->createColumn('server_port', 'int(19) DEFAULT NULL')
            ->createColumn('server_username', 'varchar(100) DEFAULT NULL')
            ->createColumn('server_password', 'varchar(255) DEFAULT NULL')
            ->createColumn('server_type', 'varchar(20) DEFAULT NULL')
            ->createColumn('smtp_auth', 'varchar(5) DEFAULT NULL')
            ->createColumn('server_path', 'varchar(256) DEFAULT NULL')
            ->createColumn('from_email_field', 'varchar(50) DEFAULT NULL')
            ->createColumn('client_id', 'varchar(255) DEFAULT NULL')
            ->createColumn('client_secret', 'varchar(255) DEFAULT NULL')
            ->createColumn('client_token', 'text DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`id`)');
    }

    public static function getInstanceFromServerType($type, $componentName)
    {
        $db = PearDatabase::getInstance();
        $query = 'SELECT * FROM ' . self::tableName . ' WHERE server_type=?';
        $params = [$type];
        $result = $db->pquery($query, $params);

        try {
            $modelClassName = Vtiger_Loader::getComponentClassName('Model', $componentName, 'Settings:Vtiger');
        } catch (Exception $e) {
            $modelClassName = 'self';
        }

        $instance = new $modelClassName();

        if ($db->num_rows($result) > 0) {
            $rowData = $db->query_result_rowdata($result, 0);
            $instance->setData($rowData);
        }
        return $instance;
    }

    public static function getFromEmailField(): string
    {
        $fromEmail = VTCacheUtils::getOutgoingMailFromEmailAddress();

        if ($fromEmail === null) {
            $db = PearDatabase::getInstance();
            $query = 'SELECT from_email_field FROM ' . self::tableName . ' WHERE server_type = ?';
            $result = $db->pquery($query, ['email']);

            if ($db->num_rows($result) > 0) {
                $row = $db->fetchByAssoc($result);
                $fromEmail = $row['from_email_field'];
            } else {
                $fromEmail = '';
            }
        }

        return $fromEmail;
    }

}
