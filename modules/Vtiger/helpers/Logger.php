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

// Include the Monolog library for logging functionality
require 'vendor/autoload.php';

// Import necessary classes from Monolog
use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;

// Define a custom Logger class
class Logger
{
    private static $logLevel;
    private static $filePath;
    private static $initialized = false;

    /**
     * Constructor to initialize the logger
     * @global type $PERFORMANCE_CONFIG [Enable LOGLEVEL_DEBUG falg in config.performance.php]
     */
    public function __construct()
    {
        // Check if the logger is not already initialized
        if (!self::$initialized) {
            global $PERFORMANCE_CONFIG;
            // Check if the performance config is set and debug logging is enabled
            if (isset($PERFORMANCE_CONFIG) && isset($PERFORMANCE_CONFIG['LOGLEVEl_DEBUG']) && $PERFORMANCE_CONFIG['LOGLEVEl_DEBUG']) {
                // Set the default log level to 100 and the log file path
                self::$logLevel = 100;
                self::$filePath = 'logs/vtigercrm.log';
                self::$initialized = true;
            }
        }
    }

    /**
     * Static method to get a logger instance
     */
    public static function getLogger(string $channel, $customFormatter = true)
    {
        // Create a new logger instance
        $log = new self();

        // Check if log level is set (logger is initialized)
        if (self::$logLevel) {
            $log = new MonologLogger($channel);
            $handler = new StreamHandler(self::$filePath, self::$logLevel);

            // Set a custom formatter if customFormatter is true
            if ($customFormatter) {
                $handler->setFormatter(new VtigerCustomFormatter());
            }
            $log->pushHandler($handler);
        }

        return $log;
    }

    // Placeholder method for logging information
    public function info($message)
    {
        // Logging info not implemented
    }

    // Placeholder method for logging debug messages
    public function debug($message)
    {
        // Logging debug not implemented
    }

    // Placeholder method for logging debug messages
    public function fatal($message)
    {
        // Logging debug not implemented
    }
}

// Define a custom log formatter
use Monolog\Formatter\FormatterInterface;

class VtigerCustomFormatter implements FormatterInterface
{

    /**
     * Format a log record
     *
     * @param Monolog\LogRecord $record
     *
     * @return string
     */
    public function format(Monolog\LogRecord $record): string
    {
        $record = $record->toArray();
        $formatted = '[' . date('Y-m-d H:i:s') . '] - ' . $record['level_name'] . ' - ' . $record['channel'] . ' - ' . $record['message'] . PHP_EOL;

        return $formatted;
    }

    /**
     *  Format a batch of log records
     *
     * @param array $records
     *
     * @return string
     */
    public function formatBatch(array $records): string
    {
        $formatted = '';
        foreach ($records as $record) {
            $formatted .= $this->format($record);
        }

        return $formatted;
    }
}