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

class Users_ExportData_Action extends Vtiger_ExportData_Action
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if (!$currentUserModel->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        return true;
    }

    var $exportableFields = [
        'user_name'          => 'User Name',
        'title'              => 'Title',
        'first_name'         => 'First Name',
        'last_name'          => 'Last Name',
        'email1'             => 'Email',
        'email2'             => 'Other Email',
        'secondaryemail'     => 'Secondary Email',
        'phone_work'         => 'Office Phone',
        'phone_mobile'       => 'Mobile',
        'phone_fax'          => 'Fax',
        'address_street'     => 'Street',
        'address_city'       => 'City',
        'address_state'      => 'State',
        'address_country_id' => 'Country',
        'address_postalcode' => 'Postal Code'
    ];

    /**
     * Function exports the data based on the mode
     *
     * @param Vtiger_Request $request
     */
    function ExportData(Vtiger_Request $request)
    {
        $this->moduleCall = true;
        $db = PearDatabase::getInstance();
        $moduleName = $request->get('source_module');
        if ($moduleName) {
            $this->moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
            $this->moduleFieldInstances = $this->moduleInstance->getFields();
            $this->focus = CRMEntity::getInstance($moduleName);
            $query = $this->getExportQuery($request);
            $result = $db->pquery($query, []);
            $headers = $this->exportableFields;
            foreach ($headers as $header) {
                $translatedHeaders[] = vtranslate(html_entity_decode($header, ENT_QUOTES), $moduleName);
            }

            $entries = [];
            for ($i = 0; $i < $db->num_rows($result); $i++) {
                $entries[] = $db->fetchByAssoc($result, $i);
            }

            return $this->output($request, $translatedHeaders, $entries);
        }
    }

    /**
     * Function that generates Export Query based on the mode
     *
     * @param Vtiger_Request $request
     *
     * @return <String> export query
     */
    function getExportQuery(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $cvId = $request->get('viewname');
        $moduleName = $request->get('source_module');

        $queryGenerator = new QueryGenerator($moduleName, $currentUser);
        if (!empty($cvId)) {
            $queryGenerator->initForCustomViewById($cvId);
        }

        $acceptedFields = array_keys($this->exportableFields);
        $queryGenerator->setFields($acceptedFields);

        return $queryGenerator->getQuery();
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateReadAccess();
    }
}