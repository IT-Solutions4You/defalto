<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_Debugger_Model extends Vtiger_Base_Model
{

    private static $c_request_par = "setdebug";
    private static $c_session_par = "EMAILMakerDebugging";
    private static $instance;
    private $db;

    public function __construct()
    {
        $this->db = PearDatabase::GetInstance();
    }

    public static function GetInstance()
    {
        if (self::$instance == null) {
            self::$instance = new EMAILMaker_Debugger_Model();
        }

        return self::$instance;
    }

    public function Init()
    {
        $this->handleRequest();
        $this->runDebug();
    }

    private function handleRequest()
    {
        if (isset($_REQUEST[self::$c_request_par])) {
            if ($_REQUEST[self::$c_request_par] == "true") {
                $_SESSION[self::$c_session_par] = "true";
            } elseif ($_REQUEST[self::$c_request_par] == "false") {
                unset($_SESSION[self::$c_session_par]);
            }
        }
    }

    private function runDebug()
    {
        if (isset($_SESSION[self::$c_session_par]) && $_SESSION[self::$c_session_par] == "true") {
            $this->performActions();
        }
    }

    private function performActions()
    {
        $this->db->setDebug(true);
        error_reporting(63);
    }

    public function GetDebugVal()
    {
        $val = false;
        if (isset($_SESSION[self::$c_session_par]) && $_SESSION[self::$c_session_par] == "true") {
            $val = true;
        }

        return $val;
    }

    public function SetDebugVal($i_val)
    {
        if ($i_val === true) {
            $_SESSION[self::$c_session_par] = "true";
        } else {
            unset($_SESSION[self::$c_session_par]);
        }
    }
}