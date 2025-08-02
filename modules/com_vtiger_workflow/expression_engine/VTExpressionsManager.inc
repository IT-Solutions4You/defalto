<?php
/*********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ******************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once("include/events/SqlResultIterator.inc");

class VTExpressionsManager
{
    function __construct($adb)
    {
        $this->adb = $adb;
    }

    /** Caching logic **/
    private static $cache = [];

    static function addToCache($key, $value)
    {
        self::$cache[$key] = $value;
    }

    static function fromCache($key)
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        return false;
    }

    static function clearCache()
    {
        self::$cache = [];
    }

    /** END **/

    function fields($moduleName)
    {
        global $current_user;
        $result = vtws_describe($moduleName, $current_user);
        $fields = $result['fields'];
        $arr = [];
        foreach ($fields as $field) {
            $arr[$field['name']] = $field['label'];
        }

        return $arr;
    }

    function expressionFunctions()
    {
        return [
            'substring(string,start,end)'                                => 'substring(string,start,end)',
            'preg_replace_str_only(pattern_str,replacement_str,subject)' => 'preg_replace_str_only(pattern_str,replacement_str,subject)',
            'concat'                                                     => 'concat(a,b)',
            'time_diffdays(a,b)'                                         => 'time_diffdays(a,b)',
            'time_diffdays(a)'                                           => 'time_diffdays(a)',
            'time_diff(a,b)'                                             => 'time_diff(a,b)',
            'time_diff(a)'                                               => 'time_diff(a)',
            'add_days'                                                   => 'add_days(datefield, noofdays)',
            'sub_days'                                                   => 'sub_days(datefield, noofdays)',
            'add_time(timefield, minutes)'                               => 'add_time(timefield, minutes)',
            'sub_time(timefield, minutes)'                               => 'sub_time(timefield, minutes)',
            'today'                                                      => "get_date('today')",
            'tomorrow'                                                   => "get_date('tomorrow')",
            'yesterday'                                                  => "get_date('yesterday')",
            'power(base,exponential)'                                    => "power(base,exponential)"
        ];
    }
}