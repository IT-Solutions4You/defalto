<?php
/*********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Vtiger specific custom config startup for CSRF
 */
function csrf_startup()
{
    //Override the default expire time of token 
    $GLOBALS['csrf']['expires'] = 259200;

    /**if an ajax request initiated, then if php serves content with <html> tags
     * as a response, then unnecessarily we are injecting csrf magic javascipt
     * in the response html at <head> and <body> using csrf_ob_handler().
     * So, to overwride above rewriting we need following config.
     */
    if (isAjax()) {
        $GLOBALS['csrf']['frame-breaker'] = false;
        $GLOBALS['csrf']['rewrite-js'] = null;
    }
}

function isAjax()
{
    if (!empty($_SERVER['HTTP_X_PJAX']) && $_SERVER['HTTP_X_PJAX'] == true) {
        return true;
    } elseif (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        return true;
    }

    return false;
}