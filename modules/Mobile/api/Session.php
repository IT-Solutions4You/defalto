<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

/**
 * Portions created by IT-Solutions4You s.r.o. are Copyright (C) IT-Solutions4You s.r.o.
 */
class Mobile_API_Session
{
    public static function destroy()
    {
        Vtiger_Session::destroy();
    }

    public static function init($sessionId = false)
    {
        if (empty($sessionId)) {
            Vtiger_Session::start(null, null);
            $sessionId = Vtiger_Session::id();
        } else {
            Vtiger_Session::start(null, $sessionId);
        }

        if (Vtiger_Session::isIdle() || Vtiger_Session::isExpired()) {
            return false;
        }

        return $sessionId;
    }

    public static function get($key, $defaultValue = '')
    {
        return Vtiger_Session::get($key, $defaultValue);
    }

    public static function set($key, $value): void
    {
        Vtiger_Session::set($key, $value);
    }
}