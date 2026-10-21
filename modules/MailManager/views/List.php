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

require_once 'modules/MailManager/runtime/Response.php';

class MailManager_List_View extends MailManager_Abstract_View
{
    static $controllers = [
        'mainui'   => ['class' => 'MailManager_MainUI_View'],
        'folder'   => ['class' => 'MailManager_Folder_View'],
        'mail'     => ['class' => 'MailManager_Mail_View'],
        'relation' => ['class' => 'MailManager_Relation_View'],
        'settings' => ['class' => 'MailManager_Settings_View'],
        'search'   => ['class' => 'MailManager_Search_View'],
    ];

    /**
     * Pro index view that renders the menu entry when the Pro edition is enabled.
     *
     * @var MailManager_ProIndex_View|null
     */
    protected ?MailManager_ProIndex_View $proView = null;

    /**
     * Edition decision for the current root request, computed once so all lifecycle
     * steps render the same edition.
     *
     * @var bool|null
     */
    protected ?bool $proRootRequest = null;

    /**
     * Returns true when the root page (no _operation) should render the Pro edition.
     * Basic AJAX operations are never delegated.
     *
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function isProRootRequest(Vtiger_Request $request): bool
    {
        if (!$request->isEmpty('_operation')) {
            return false;
        }

        if (null === $this->proRootRequest) {
            $this->proRootRequest = MailManager_Module_Model::isProEnabled();
        }

        return $this->proRootRequest;
    }

    /**
     * Returns the Pro index view used for delegation, created once per request.
     *
     * @return MailManager_ProIndex_View
     */
    protected function getProView(): MailManager_ProIndex_View
    {
        if (null === $this->proView) {
            $this->proView = new MailManager_ProIndex_View();
        }

        return $this->proView;
    }

    /**
     * Delegates the permission check to the Pro view for a Pro root request.
     *
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        if ($this->isProRootRequest($request)) {
            return $this->getProView()->checkPermission($request);
        }

        return parent::checkPermission($request);
    }

    /**
     * Delegates the page header to the Pro view for a Pro root request.
     *
     * @param Vtiger_Request $request
     * @param bool           $display
     *
     * @return void
     * @throws Exception
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        if ($this->isProRootRequest($request)) {
            $this->getProView()->preProcess($request, $display);

            return;
        }

        // Basic shows a small "Pro" button that explains Mail Manager Pro (T12, draft)
        $this->getViewer($request)->assign('SHOW_PRO_BUTTON', $request->isEmpty('_operation') && !MailManager_Module_Model::isProEnabled());

        parent::preProcess($request, $display);
    }

    /**
     * Delegates the page footer to the Pro view for a Pro root request.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function postProcess(Vtiger_Request $request): void
    {
        if ($this->isProRootRequest($request)) {
            $this->getProView()->postProcess($request);

            return;
        }

        parent::postProcess($request);
    }

    /**
     * Delegates the page styles to the Pro view for a Pro root request.
     *
     * @param Vtiger_Request $request
     *
     * @return array
     * @throws Exception
     */
    public function getHeaderCss(Vtiger_Request $request): array
    {
        if ($this->isProRootRequest($request)) {
            return $this->getProView()->getHeaderCss($request);
        }

        return parent::getHeaderCss($request);
    }

    /**
     * Loads the Basic page scripts, or the Pro page scripts for a Pro root request.
     *
     * @param Vtiger_Request $request
     *
     * @return array
     * @throws Exception
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        if ($this->isProRootRequest($request)) {
            return $this->getProView()->getHeaderScripts($request);
        }

        $headerScriptInstances = parent::getHeaderScripts($request);

        $jsFileNames = [
            "libraries.jquery.ckeditor.ckeditor",
            "libraries.jquery.ckeditor.adapters.jquery",
            "modules.Vtiger.resources.CkEditor",
            "modules.MailManager.resources.List"
        ];

        if (!MailManager_Module_Model::isProEnabled()) {
            $jsFileNames[] = 'modules.Installer.resources.PremiumModal';
            $jsFileNames[] = 'modules.MailManager.resources.ProButton';
        }

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }

    /**
     * Renders the root page (Basic or, when enabled, Pro) or dispatches a Basic AJAX operation.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        if ($this->isProRootRequest($request)) {
            $this->getProView()->process($request);

            return;
        }

        $request = MailManager_Request::getInstance($request);

        if (!$request->has('_operation')) {
            return $this->processRoot($request);
        }
        $operation = $request->getOperation();

        if (!isset(self::$controllers[$operation])) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $controllerInfo = self::$controllers[$operation];
        $controller = new $controllerInfo['class'];

        // Making sure to close the open connection
        if ($controller) {
            $controller->closeConnector();
        }
        if ($controller->validateRequest($request)) {
            $response = $controller->process($request);
            if ($response) {
                $response->emit();
            }
        }
        unset($request);
        unset($response);
    }

    public function processRoot(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $moduleName);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'processRoot', $request->getModule(), $viewer, $request);

        $viewer->view('index.tpl', $moduleName);
    }
}