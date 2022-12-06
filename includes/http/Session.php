<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

/**
 * Portions created by IT-Solutions4You s.r.o. are Copyright (C) IT-Solutions4You s.r.o.
 */

/**
 * Override default user-session storage functions if custom session connector exist.
 */
$runtime_configs = Vtiger_Runtime_Configs::getInstance();
$custom_session_handlerclass = $runtime_configs->getConnector('session');

if ($custom_session_handlerclass) {
    $handler = $custom_session_handlerclass::getInstance();
    session_set_save_handler($handler, true);
}

// Import dependencies

/**
 * Session class
 */
class Vtiger_Session
{
    /**
     * @const STARTED - The session was started with the current request
     */
    public const STARTED = 1;

    /**
     * @const CONTINUE - No new session was started with the current request
     */
    public const CONTINUED = 2;

    /**
     * Destroy session
     */
    public static function destroy(): void
    {
        session_unset();
        session_destroy();
    }

    /**
     * Initialize session
     */
    public static function init($sessionId = '')
    {
        if (empty($sessionId)) {
            self::start(null, null);
            $sessionId = self::id();
        } else {
            self::start(null, $sessionId);
        }

        if (self::isIdle() || self::isExpired()) {
            return false;
        }

        return $sessionId;
    }

    /**
     * Initializes session data
     *
     * Creates a session (or resumes the current one
     * based on the session id being passed
     * via a GET variable or a cookie).
     * You can provide your own name and/or id for a session.
     *
     * @param string $name of a session, default is 'SessionID'
     * @param string $id   of a session which will be used
     *                     only when the session is new
     *
     * @return void
     * @see    session_name()
     * @see    session_id()
     * @see    session_start()
     */
    public static function start($name = 'SessionID', $id = null)
    {
        self::name($name);

        if (is_null(self::detectID())) {
            if ($id) {
                self::id($id);
            } else {
                self::id(uniqid(dechex(mt_rand()), true));
            }
        }

        session_start();

        if (!isset($_SESSION['__CRM_Session_Info'])) {
            $_SESSION['__CRM_Session_Info'] = self::STARTED;
        } else {
            $_SESSION['__CRM_Session_Info'] = self::CONTINUED;
        }
    }

    /**
     * Sets new name of a session
     *
     * @param string $name New name of a sesion
     *
     * @return string Previous name of a session
     * @see    session_name()
     */
    public static function name($name = null)
    {
        if (isset($name)) {
            return session_name($name);
        }

        return session_name();
    }

    /**
     * Tries to find any session id in $_GET, $_POST or $_COOKIE
     *
     * @return string Session ID (if exists) or null
     */
    public static function detectID()
    {
        if (self::useCookies()) {
            if (isset($_COOKIE[self::name()])) {
                return $_COOKIE[self::name()];
            }
        } elseif (isset($_GET[self::name()])) {
            return $_GET[self::name()];
        } elseif (isset($_POST[self::name()])) {
            return $_POST[self::name()];
        }

        return null;
    }

    /**
     * Sets new ID of a session
     *
     * @param string $id New ID of a sesion
     *
     * @return string Previous ID of a session
     */
    public static function id($id = null)
    {
        if (isset($id)) {
            return session_id($id);
        }

        return session_id();
    }

    /**
     * If optional parameter is specified it indicates whether the module will
     * use cookies to store the session id on the client side in a cookie.
     *
     * By default, this cookie will be deleted when the browser is closed!
     *
     * It will throw an Exception if it's not able to set the session.use_cookie
     * property.
     *
     * It returns the previous value of this property.
     *
     * @param bool $useCookies If specified it will replace the previous value of
     *                         this property. By default, 'null', which doesn't
     *                         change any setting on your system. If you supply a
     *                         parameter, please supply 'boolean'.
     *
     * @return bool The previous value of the property
     */
    public static function useCookies($useCookies = null): bool
    {
        $return = (bool)ini_get('session.use_cookies');

        if (isset($useCookies)) {
            ini_set('session.use_cookies', $useCookies ? 1 : 0);
        }

        return $return;
    }

    /**
     * @return bool
     */
    public static function isIdle(): bool
    {
        return isset($_SESSION['__CRM_Session_Idle'])
            && $_SESSION['__CRM_Session_Idle'] > 0
            && isset($_SESSION['__CRM_Session_Idle_TS'])
            && (
                $_SESSION['__CRM_Session_Idle_TS']
                + $_SESSION['__CRM_Session_Idle']
            ) <= time();
    }

    /**
     * @return bool
     */
    public static function isExpired(): bool
    {
        return isset($_SESSION['__CRM_Session_Expire'])
            && $_SESSION['__CRM_Session_Expire'] > 0
            && isset($_SESSION['__CRM_Session_Expire_TS'])
            &&
            (
                $_SESSION['__CRM_Session_Expire_TS']
                + $_SESSION['__CRM_Session_Expire']
            ) <= time();
    }

    /**
     * @return bool true when the session was created with the current request, false otherwise
     *
     * @see  self::start()
     * @uses self::STARTED
     */
    public static function isNew()
    {
        return !isset($_SESSION['__CRM_Session_Info']) || $_SESSION['__CRM_Session_Info'] === self::STARTED;
    }

    /**
     * Check whether the key defined in session
     */
    public static function has($key): bool
    {
        $val = self::get($key, null);

        return $val === null;
    }

    /**
     * Get value for the key.
     */
    public static function get($key, $defaultValue = '')
    {
        if (!isset($_SESSION[$key]) && isset($defaultValue)) {
            $_SESSION[$key] = $defaultValue;
        }

        return $_SESSION[$key];
    }

    /**
     * Set value for the key.
     *
     * Returns previous value if it was set, null otherwise
     */
    public static function set($key, $value)
    {
        $return = $_SESSION[$key] ?? null;

        if (null === $value) {
            unset($_SESSION[$key]);
        } else {
            $_SESSION[$key] = $value;
        }

        return $return;
    }

    public static function readonly(): void
    {
        session_write_close();
    }
}