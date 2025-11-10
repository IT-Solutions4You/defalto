<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

vimport('includes.http.Request');
vimport('includes.http.Response');
vimport('includes.http.Session');
vimport('includes.runtime.Globals');
vimport('includes.runtime.Viewer');
vimport('includes.runtime.Theme');
vimport('includes.runtime.BaseModel');
vimport('includes.runtime.JavaScript');
vimport('includes.runtime.LanguageHandler');
vimport('includes.runtime.Cache');
vimport('vtlib.Vtiger.Runtime');

abstract class Vtiger_EntryPoint
{
    /**
     * Login data
     */
    protected $login = false;

    /**
     * Get login data.
     */
    function getLogin()
    {
        return $this->login;
    }

    /**
     * Set login data.
     */
    function setLogin($login)
    {
        if ($this->login) {
            throw new Exception('Login is already set.');
        }
        $this->login = $login;
    }

    /**
     * Check if login data is present.
     */
    function hasLogin()
    {
        return $this->getLogin() ? true : false;
    }

    abstract function process(Vtiger_Request $request);
}