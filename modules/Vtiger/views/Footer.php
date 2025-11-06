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

abstract class Vtiger_Footer_View extends Vtiger_Header_View
{
    function __construct()
    {
        parent::__construct();
    }

    //Note: To get the right hook for immediate parent in PHP,
    // specially in case of deep hierarchy
    /*function preProcessParentTplName(Vtiger_Request $request) {
        return parent::preProcessTplName($request);
    }*/

    /*function postProcess(Vtiger_Request $request) {
        parent::postProcess($request);
    }*/

    /**
     * @inheritDoc
     */
    public function getHeaderCss(Vtiger_Request $request): array
    {
        $headerCssInstances = parent::getHeaderCss($request);
        $cssFileNames = [
            '~vendor/bootstrap-switch/bootstrap-switch/dist/css/bootstrap4/bootstrap-switch.min.css',
            '~layouts/' . Vtiger_Viewer::getDefaultLayoutName() . '/lib/jquery/timepicker/jquery.timepicker.css',
            '~/libraries/jquery/lazyYT/lazyYT.min.css'
        ];
        $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);

        return array_merge($headerCssInstances, $cssInstances);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $jsFileNames= [
            '~vendor/bootstrap-switch/bootstrap-switch/dist/js/bootstrap-switch.min.js',
        ];

        return array_merge(parent::getHeaderScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
    }
}