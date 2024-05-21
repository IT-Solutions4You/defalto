<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class Vtiger_PhpLogHandler
{
    /**
     * Enable strict mode.
     */
    public static function enableStrictLogging($basedir, $logfile)
    {
        ini_set('display_errors', 'off');
        error_reporting(E_ALL);
        ini_set('log_errors', 'on');
        ini_set('error_log', $logfile);
        set_error_handler(Vtiger_PhpLogHandler::getErrorHandler($basedir));
        set_exception_handler(Vtiger_PhpLogHandler::getExceptionHandler($basedir));
    }

    /**
     * Capture context of request in Log to review later.
     */
    public static function getRequestContextToLog()
    {
        $ctx = '';

        if (isset($_SERVER)) {
            $ctx = $_SERVER['REQUEST_METHOD'] . ' ' . str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
            $params = [];

            foreach (['module', 'view', 'action', 'mode', 'record'] as $key) {
                if (isset($_REQUEST[$key])) {
                    $params[$key] = $_REQUEST[$key];
                }
            }

            $ctx .= '?' . http_build_query($params);
        }

        return $ctx;
    }

    /**
     * Redacted PHP error handler.
     * - Retains only relative reference to the source file.
     * - Logs to file if provided always (or) displays to console only when display_errors is on.
     */
    public static function getErrorHandler($basedir, $logfile = null)
    {
        $display_errors = filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $log_errors = filter_var(ini_get('log_errors'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if (!$logfile) {
            $logfile = ini_get('error_log');
        }

        $logctx = Vtiger_PhpLogHandler::getRequestContextToLog();
        $reqtm = date('[Y-m-d H:i:s]');

        return function ($errno, $errstr, $errfile, $errline) use ($display_errors, $log_errors, $basedir, $logfile, $logctx, $reqtm) {
            if (!$display_errors && !$log_errors && ($log_errors && !$logfile)) {
                return;
            }

            switch ($errno) {
                case E_DEPRECATED:
                    $errtype = 'Deprecated';
                    break;
                case E_ERROR:
                    $errtype = 'Error';
                    break;
                case E_WARNING:
                    $errtype = 'Warning';
                    break;
                case E_PARSE:
                    $errtype = 'Parse Error';
                    break;
                case E_NOTICE:
                    $errtype = 'Notice';
                    break;
                case E_CORE_ERROR:
                    $errtype = 'Core Error';
                    break;
                case E_CORE_WARNING:
                    $errtype = 'Core Warning';
                    break;
                case E_COMPILE_ERROR:
                    $errtype = 'Compile Error';
                    break;
                case E_COMPILE_WARNING:
                    $errtype = 'Compile Warning';
                    break;
                case E_USER_ERROR:
                    $errtype = 'User Error';
                    break;
                case E_USER_WARNING:
                    $errtype = 'User Warning';
                    break;
                case E_USER_NOTICE:
                    $errtype = 'User Notice';
                    break;
                case E_STRICT:
                    $errtype = 'Strict Notice';
                    break;
                case E_RECOVERABLE_ERROR:
                    $errtype = 'Recoverable Error';
                    break;
                default:
                    $errtype = "Unknown error ($errno)";
                    break;
            }

            $errfilerel = str_replace(rtrim($basedir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, '', $errfile);

            // format message same as default PHP
            $msg = sprintf("%s: %s in %s on line %s\n", $errtype, $errstr, $errfilerel, $errline);

            if ($logfile) {
                $tmstamp = $reqtm . '@' . date('[H:i:s]');
                $fullmsg = $tmstamp . ' ' . $logctx . "\n" . $tmstamp . ' ' . $msg;
                file_put_contents($logfile, $fullmsg, FILE_APPEND | LOCK_EX);
            }

            // if errors are logged then don't display even when asked for security.
            // php does not enforce this and when mis-configured leaks info to user/attacker.
            if (!$log_errors && $display_errors) {
                echo "\n$msg";
            }
        };
    }

    /**
     * Redacted PHP exception handler.
     * - Retains only relative reference to the source file.
     * - Logs to file if provided always (or) displays to console only when display_errors is on.
     */
    public static function getExceptionHandler($basedir, $logfile = null)
    {
        $display_errors = filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $log_errors = filter_var(ini_get('log_errors'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if (!$logfile) {
            $logfile = ini_get('error_log');
        }

        $logctx = Vtiger_PhpLogHandler::getRequestContextToLog();
        $reqtm = date('[Y-m-d H:i:s]');

        return function (Throwable $e) use ($display_errors, $log_errors, $basedir, $logfile, $logctx, $reqtm) {
            if (!$display_errors && !$log_errors && ($log_errors && !$logfile)) {
                return;
            }

            $basedir = rtrim($basedir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $errfile = str_replace($basedir, '', $e->getFile());
            $errstack = str_replace($basedir, '', $e->getTraceAsString());

            // format message same as default PHP
            $msg = sprintf(
                "Fatal error: %s in %s:%d\nStack trace:\n%s\n    thrown in %s on line %d\n",
                $e->getMessage(),
                $errfile,
                $e->getLine(),
                $errstack,
                $errfile,
                $e->getLine()
            );

            if ($logfile) {
                $tmstamp = $reqtm . '@' . date('[H:i:s]');
                $fullmsg = $tmstamp . ' ' . $logctx . "\n" . $tmstamp . ' ' . $msg;
                file_put_contents($logfile, $fullmsg, FILE_APPEND | LOCK_EX);
            }

            // if errors are logged then don't display even when asked for security.
            // php does not enforce this and when mis-configured leaks info to user/attacker.
            if (!$log_errors && $display_errors) {
                echo "\n$msg";
            }
        };
    }
}