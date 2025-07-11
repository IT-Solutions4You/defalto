<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

global $combo_strings;

$combo_strings = [
    'duration_minutes_dom' => [
        '00' => '00',
        '15' => '15',
        '30' => '30',
        '45' => '45',
    ],
    'currency_dom' => [
        'Rupees' => 'Rupees',
        'Dollar' => 'Dollar',
        'Euro' => 'Euro',
    ],
    'visibility_dom' => [
        'Private' => 'Private',
        'Public' => 'Public',
    ],
    'taxclass_dom' => [
        'SalesTax' => 'SalesTax',
        'Vat' => 'Vat',
    ],
    'recurringtype_dom' => [
        '' => '',
        'Daily' => 'Daily',
        'Weekly' => 'Weekly',
        'Monthly' => 'Monthly',
        'Yearly' => 'Yearly',
    ],
    'status_dom' => [
        'Active' => 'Active',
        'Inactive' => 'Inactive',
    ],
    'lead_view_dom' => [
        'Today' => 'Today',
        'Last 2 Days' => 'Last 2 Days',
        'Last Week' => 'Last Week',
    ],
];

require_once('modules/Users/UserTimeZonesArray.php');
$arrayOfSupportedTimeZones = UserTimeZones::getAll();
$combo_strings['time_zone_dom'] = array_combine($arrayOfSupportedTimeZones, $arrayOfSupportedTimeZones);