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

class Project_Relation_Model extends Vtiger_Relation_Model
{
    /**
     * Function that deletes Project related records information
     *
     * @param int $sourceRecordId  - Project Id
     * @param int $relatedRecordId - Related Record Id
     *
     * @throws Exception
     */
    public function deleteRelation($sourceRecordId, $relatedRecordId)
    {
        $sourceModule = $this->getParentModuleModel();
        $sourceModuleName = $sourceModule->get('name');
        $destinationModuleName = $this->getRelationModuleModel()->get('name');
        $sourceModuleFocus = CRMEntity::getInstance($sourceModuleName);
        $sourceModuleFocus->delete_related_module($sourceModuleName, $sourceRecordId, $destinationModuleName, $relatedRecordId);
        $sourceModuleFocus->trackUnLinkedInfo($sourceModuleName, $sourceRecordId, $destinationModuleName, $relatedRecordId);

        return true;
    }
}