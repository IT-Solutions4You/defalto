<?php
/**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Project_RelationListView_Model extends Vtiger_RelationListView_Model
{
    public static function isCreateLinkPermitted(Vtiger_LinkData $linkData): bool
    {
        $link = Vtiger_Link_Model::getInstanceFromLinkObject($linkData->getLink());
        $parameters = $link->getUrlParameters();
        $relatedModule = Vtiger_Module_Model::getInstance((string)($parameters['module'] ?? ''));

        if (!$relatedModule || !$relatedModule->isActive()) {
            return false;
        }

        if (!Users_Privileges_Model::isPermitted($relatedModule->getName(), 'CreateView')) {
            return false;
        }

        $relation = Vtiger_Relation_Model::getInstance(Vtiger_Module_Model::getInstance('Project'), $relatedModule);

        return $relation && (int)$relation->get('presence') !== 1 && $relation->isAddActionSupported();
    }

    public function getCreateViewUrl()
    {
        $createViewUrl = parent::getCreateViewUrl();

        $relationModuleModel = $this->getRelationModel()->getRelationModuleModel();
        if ($relationModuleModel->getName() == 'HelpDesk') {
            if ($relationModuleModel->getField('parent_id')->isViewable()) {
                $createViewUrl .= '&parent_id=' . $this->getParentRecordModel()->get('linktoaccountscontacts');
            }
        }

        return $createViewUrl;
    }
}
