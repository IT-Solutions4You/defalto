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

class Users_UI5_Action extends Core_Controller_Action
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        return [];
    }

    function process(Vtiger_Request $request)
    {
        setcookie('vtigerui', 5, time() + 60 * 60 * 24 * 30, '/');
        $HTTP_REFERER = $_SERVER['HTTP_REFERER'];
        $switchURL = explode('?', $HTTP_REFERER);

        $params = [];
        if (isset($switchURL[1])) {
            parse_str($switchURL[1], $params);
        }

        $module = $action = $record = $mode = '';
        if (isset($params['module'])) {
            $module = $params['module'];
            if ($module == 'Vtiger') {
                $module = '';
            }
        }

        if (isset($params['view'])) {
            if ($params['view'] == 'Detail') {
                if ($module == 'Portal') {
                    $action = 'ListView';
                } else {
                    $action = 'DetailView';
                }
            } elseif ($params['view'] == 'List') {
                $action = 'ListView';
            } elseif ($params['view'] == 'Edit') {
                $action = 'EditView';
            } elseif ($params['view'] == 'Import') {
                $action = 'Import';
            } elseif ($params['view'] == 'PreferenceDetail' || $params['view'] == 'PreferenceEdit') {
                $action = 'DetailView';
            } else {
                $action = 'index';
            }
        }

        if (isset($params['action'])) {
            $action = $params['action'];
        }

        if (isset($params['record'])) {
            $record = $params['record'];
        }

        if (isset($params['mode'])) {
            $mode = $params['mode'];
        }

        if (isset($params['parent'])) {
            //redirect to settings index page
            $module = 'Settings';
            $action = 'index';
            //empty if any record or mode
            $record = '';
            $mode = '';
        }

        $url = '../index.php?';
        if ($module) {
            $url .= '&module=' . $module;
        }
        if ($action) {
            $url .= '&action=' . $action;
        }
        if ($record) {
            $url .= '&record=' . $record;
        }
        if ($mode) {
            $url .= '&mode=' . $mode;
        }

        header('Location: ' . $url);
    }
}