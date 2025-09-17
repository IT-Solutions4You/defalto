<?php
/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

abstract class Core_TemplateModel_Helper extends Vtiger_Module_Model
{
    /**
     * @throws Exception
     */
    abstract public function checkPermissions($actionKey);

    /**
     * @param string $selectedModule
     * @param int $templateId
     * @param bool $die
     * @return mixed
     */
    abstract public function checkTemplatePermissions($selectedModule, $templateId = 0, $die = true);

    /**
     * @param string $currModule
     * @param bool $forListView
     * @param int $recordId
     * @return mixed
     */
    abstract public function getAvailableTemplates($currModule, $forListView = false, $recordId = 0);

    /**
     * @param int $templateId
     * @return mixed
     */
    abstract public function isTemplateDeleted($templateId);
}