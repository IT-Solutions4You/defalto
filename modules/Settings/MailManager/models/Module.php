<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_MailManager_Module_Model extends Settings_Vtiger_Module_Model
{
    public $name = 'MailManager';

    /**
     * @param string $name
     * @return Settings_MailManager_Module_Model
     */
    public static function getInstance($name = '')
    {
        return new self();
    }

    /**
     * Delegates to the base entity-module model
     * (Settings_Vtiger_Module_Model has no getSettingLinks)
     *
     * @return array
     */
    public function getSettingLinks()
    {
        return Vtiger_Module_Model::getInstance($this->name)->getSettingLinks();
    }
}
