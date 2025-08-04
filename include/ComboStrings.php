<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
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

global $combo_strings;

$combo_strings = [
    'duration_minutes_dom' => [
        '00' => '00',
        '15' => '15',
        '30' => '30',
        '45' => '45',
    ],
    'currency_dom'         => [
        'Rupees' => 'Rupees',
        'Dollar' => 'Dollar',
        'Euro'   => 'Euro',
    ],
    'visibility_dom'       => [
        'Private' => 'Private',
        'Public'  => 'Public',
    ],
    'taxclass_dom'         => [
        'SalesTax' => 'SalesTax',
        'Vat'      => 'Vat',
    ],
    'recurringtype_dom'    => [
        ''        => '',
        'Daily'   => 'Daily',
        'Weekly'  => 'Weekly',
        'Monthly' => 'Monthly',
        'Yearly'  => 'Yearly',
    ],
    'status_dom'           => [
        'Active'   => 'Active',
        'Inactive' => 'Inactive',
    ],
    'lead_view_dom'        => [
        'Today'       => 'Today',
        'Last 2 Days' => 'Last 2 Days',
        'Last Week'   => 'Last Week',
    ],
];

require_once('modules/Users/UserTimeZonesArray.php');
$arrayOfSupportedTimeZones = UserTimeZones::getAll();
$combo_strings['time_zone_dom'] = array_combine($arrayOfSupportedTimeZones, $arrayOfSupportedTimeZones);