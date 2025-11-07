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

vimport('~~/vtlib/Vtiger/Net/Client.php');

class Users_Login_View extends Core_Controller_View
{
    /**
     * @inheritDoc
     */
    public function isLoginRequired(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        global $current_user;

        $viewer = $this->getViewer($request);
        $viewer->assign('PAGETITLE', $this->getPageTitle($request));
        $viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
        $viewer->assign('STYLES', $this->getHeaderCss($request));
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('VIEW', $request->get('view'));
        $viewer->assign('LANGUAGE_STRINGS', []);

        $viewer->assign('INVENTORY_MODULES', []);
        $viewer->assign('QUALIFIED_MODULE', '');
        $viewer->assign('PARENT_MODULE', '');
        $viewer->assign('NOTIFIER_URL', '');
        $viewer->assign('EXTENSION_MODULE', '');
        $viewer->assign('CURRENT_USER_MODEL', $current_user);
        $viewer->assign('LANGUAGE', '');

        Core_Modifiers_Model::modifyForClass(get_class($this), 'preProcess', $request->getModule(), $viewer, $request);

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    function process(Vtiger_Request $request)
    {
        $finalJsonData = [];
        $jsonData = [];

        $viewer = $this->getViewer($request);
        $viewer->assign('DATA_COUNT', php7_count($jsonData));
        $viewer->assign('JSON_DATA', $finalJsonData);

        $mailStatus = $request->get('mailStatus');
        $error = $request->get('error');
        $message = '';
        if ($error) {
            switch ($error) {
                case 'login'        :
                    $message = 'Invalid credentials';
                    break;
                case 'fpError'        :
                    $message = 'Invalid Username or Email address';
                    break;
                case 'statusError'    :
                    $message = 'Outgoing mail server was not configured';
                    break;
            }
        } elseif ($mailStatus) {
            $message = 'Mail has been sent to your inbox, please check your e-mail';
        }

        $viewer->assign('ERROR', $error);
        $viewer->assign('MESSAGE', $message);
        $viewer->assign('MAIL_STATUS', $mailStatus);
        $viewer->view('Login.tpl', 'Users');
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'postProcess', $request->getModule(), $viewer, $request);

        $viewer->view('Footer.tpl', $moduleName);
    }

    /**
     * @inheritDoc
     */
    public function getPageTitle(Vtiger_Request $request): string
    {
        $companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();

        return $companyDetails->get('organizationname');
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);

        $jsFileNames = [
            '~libraries/jquery/boxslider/jquery.bxslider.min.js',
            'modules.Vtiger.resources.List',
            'modules.Vtiger.resources.Popup',
        ];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($jsScriptInstances, $headerScriptInstances);
    }
}