<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Portions created by IT-Solutions4You s.r.o. are Copyright (C) IT-Solutions4You s.r.o.
 */

global $maxWebServiceSessionLifeSpan, $maxWebServiceSessionIdleTime;

$maxWebServiceSessionLifeSpan = 86400; //Max life span of a session is a day.
$maxWebServiceSessionIdleTime = 1800; //Max life span session should be kept alive after the last transaction.

class SessionManager extends Vtiger_Session
{
    private $sessionVar = "__SessionExists";
    private $error;

    public function __construct()
    {
        global $maxWebServiceSessionLifeSpan, $maxWebServiceSessionIdleTime;

        $now = time();
        $idleLife = $now + $maxWebServiceSessionIdleTime;

        ini_set('session.use_cookies', 0); //disable cookie usage. may this could be moved out constructor?
        // only first invocation of following method, which is setExpire
        //have an effect and any further invocation will have no effect.
        $_SESSION['__CRM_Session_Expire'] = $now + $maxWebServiceSessionLifeSpan;

        if (!isset($_SESSION['__CRM_Session_Expire_TS'])) {
            $_SESSION['__CRM_Session_Expire_TS'] = time();
        }

        // this method replaces the new with old time if second params is true
        //otherwise it subtracts the time from previous time
        if (isset($_SESSION['__CRM_Session_Idle'])) {
            $_SESSION['__CRM_Session_Idle'] += $idleLife;
        } else {
            $_SESSION['__CRM_Session_Idle'] = $idleLife;
        }

        if (!isset($_SESSION['__CRM_Session_Idle_TS'])) {
            $_SESSION['__CRM_Session_Idle_TS'] = time();
        }
    }

    /**
     * @return bool
     * @throws WebServiceException
     */
    public function isValid(): bool
    {
        // expired
        if (self::isExpired()) {
            self::destroy();
            throw new WebServiceException(WebServiceErrorCode::$SESSLIFEOVER, "Session has life span over please login again");
        }

        // idled
        if (self::isIdle()) {
            self::destroy();
            throw new WebServiceException(WebServiceErrorCode::$SESSIONIDLE, "Session has been invalidated to due lack activity");
        }

        if (!self::get($this->sessionVar) && !self::isNew()) {
            self::destroy();
            throw new WebServiceException(WebServiceErrorCode::$SESSIONIDINVALID, "Session Identifier provided is Invalid");
        }

        return true;
    }

    /**
     * @throws WebServiceException
     */
    public function startSession($sid = null, $adoptSession = false)
    {
        if (!$sid || strlen($sid) === 0) {
            $sid = null;
        }

        self::start(null, $sid);

        $newSID = Vtiger_Session::id();

        if (!$sid || $adoptSession) {
            self::set($this->sessionVar, "true");
        } elseif (!self::get($this->sessionVar)) {
            self::destroy();
            throw new WebServiceException(WebServiceErrorCode::$SESSIONIDINVALID, "Session Identifier provided is Invalid");
        }

        if (!$this->isValid()) {
            $newSID = null;
        }

        return $newSID;
    }

    public function getSessionId()
    {
        return self::id();
    }

    public function getError()
    {
        return $this->error;
    }
}