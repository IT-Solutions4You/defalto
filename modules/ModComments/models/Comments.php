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

class ModComments_CommentsModel
{
    private $data;

    static $ownerNamesCache = [];

    function __construct($datarow)
    {
        $this->data = $datarow;
    }

    function author()
    {
        $authorid = $this->data['creator_user_id'];
        if (!isset(self::$ownerNamesCache[$authorid])) {
            self::$ownerNamesCache[$authorid] = getOwnerName($authorid);
        }

        return self::$ownerNamesCache[$authorid];
    }

    function timestamp()
    {
        $date = new DateTimeField($this->data['modifiedtime']);

        return $date->getDisplayDateTimeValue();
    }

    function content()
    {
        return decode_html($this->data['commentcontent']);
    }
}