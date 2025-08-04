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

vimport('~~/modules/Calendar/iCal/ical-parser-class.php');

class Import_ICSReader_Reader extends iCal
{
    /**
     * Function to get info about imported file contains header or not
     * @return <boolean>
     */
    public function hasHeader()
    {
        return true;
    }

    /**
     * Function to get info about imported file contains First Row or not
     *
     * @param <boolean> $hasHeader
     *
     * @return <boolean>
     */
    public function getFirstRowData($hasHeader = true)
    {
        return true;
    }
}