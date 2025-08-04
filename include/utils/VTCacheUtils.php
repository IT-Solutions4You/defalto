<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Class to handle Caching Mechanism and re-use information.
 */
require_once 'includes/runtime/Cache.php';

class VTCacheUtils
{
    /** All tab information caching */
    static $_alltabrows_cache = false;

    static function lookupAllTabsInfo()
    {
        return self::$_alltabrows_cache;
    }

    static function updateAllTabsInfo($tabrows)
    {
        self::$_alltabrows_cache = $tabrows;
    }

    /** Block information caching */
    static $_blocklabel_cache = [];

    static function updateBlockLabelWithId($label, $id)
    {
        self::$_blocklabel_cache[$id] = $label;
    }

    static function lookupBlockLabelWithId($id)
    {
        if (isset(self::$_blocklabel_cache[$id])) {
            return self::$_blocklabel_cache[$id];
        }

        return false;
    }

    /** Field information caching */
    static $_fieldinfo_cache = [];

    static function updateFieldInfo(
        $tabid,
        $fieldname,
        $fieldid,
        $fieldlabel,
        $columnname,
        $tablename,
        $uitype,
        $typeofdata,
        $presence
    ) {
        self::$_fieldinfo_cache[$tabid][$fieldname] = [
            'tabid'      => $tabid,
            'fieldid'    => $fieldid,
            'fieldname'  => $fieldname,
            'fieldlabel' => $fieldlabel,
            'columnname' => $columnname,
            'tablename'  => $tablename,
            'uitype'     => $uitype,
            'typeofdata' => $typeofdata,
            'presence'   => $presence,
        ];
        Vtiger_Cache::set('fieldInfo', $tabid, self::$_fieldinfo_cache[$tabid]);
    }

    static $lookupModuleFieldInfo = [];

    static function lookupFieldInfo_Module($module, $presencein = ['0', '2'])
    {
        $tabid = getTabid($module);
        if (isset(self::$lookupModuleFieldInfo[$tabid][implode('-', $presencein)])) {
            return self::$lookupModuleFieldInfo[$tabid][implode('-', $presencein)];
        }
        $modulefields = [];
        $fieldInfo = Vtiger_Cache::get('fieldInfo', $tabid);
        $fldcache = null;
        if ($fieldInfo) {
            $fldcache = $fieldInfo;
        } elseif (isset(self::$_fieldinfo_cache[$tabid])) {
            $fldcache = self::$_fieldinfo_cache[$tabid];
        }

        if ($fldcache) {
            foreach ($fldcache as $fieldname => $fieldinfo) {
                if (in_array($fieldinfo['presence'], $presencein)) {
                    $modulefields[] = $fieldinfo;
                }
            }
        }

        // If modulefields are already loaded then no need of this again
        if (!$modulefields) {
            $fieldInfo = Vtiger_Cache::get('ModuleFields', $tabid);
            if ($fieldInfo) {
                foreach ($fieldInfo as $block => $blockFields) {
                    foreach ($blockFields as $field) {
                        if (in_array($field->get('presence'), $presencein)) {
                            $cacheField = [
                                'tabid'      => $tabid,
                                'fieldid'    => $field->getId(),
                                'fieldname'  => $field->getName(),
                                'fieldlabel' => $field->get('label'),
                                'columnname' => $field->get('column'),
                                'tablename'  => $field->get('table'),
                                'uitype'     => $field->get('uitype'),
                                'typeofdata' => $field->get('typeofdata'),
                                'presence'   => $field->get('presence'),
                            ];
                            $modulefields[] = $cacheField;
                        }
                    }
                }
            }
        }
        if ($modulefields) {
            self::$lookupModuleFieldInfo[$tabid][implode('-', $presencein)] = $modulefields;
        }

        return $modulefields;
    }

    static function lookupFieldInfoByColumn($tabid, $columnname)
    {
        if (isset(self::$_fieldinfo_cache[$tabid])) {
            foreach (self::$_fieldinfo_cache[$tabid] as $fieldname => $fieldinfo) {
                if ($fieldinfo['columnname'] == $columnname) {
                    return $fieldinfo;
                }
            }
        }

        $fieldInfo = Vtiger_Cache::get('ModuleFields', $tabid);
        if ($fieldInfo) {
            foreach ($fieldInfo as $block => $blockFields) {
                foreach ($blockFields as $field) {
                    if ($field->get('column') == $columnname) {
                        $cacheField = [
                            'tabid'      => $tabid,
                            'fieldid'    => $field->getId(),
                            'fieldname'  => $field->getName(),
                            'fieldlabel' => $field->get('label'),
                            'columnname' => $field->get('column'),
                            'tablename'  => $field->get('table'),
                            'uitype'     => $field->get('uitype'),
                            'typeofdata' => $field->get('typeofdata'),
                            'presence'   => $field->get('presence'),
                        ];

                        return $cacheField;
                    }
                }
            }
        }

        return false;
    }

    /** Role information */
    static $_subroles_roleid_cache = [];

    static function lookupRoleSubordinates($roleid)
    {
        if (isset(self::$_subroles_roleid_cache[$roleid])) {
            return self::$_subroles_roleid_cache[$roleid];
        }

        return false;
    }

    static function updateRoleSubordinates($roleid, $roles)
    {
        self::$_subroles_roleid_cache[$roleid] = $roles;
    }

    static function clearRoleSubordinates($roleid = false)
    {
        if ($roleid === false) {
            self::$_subroles_roleid_cache = [];
        } elseif (isset(self::$_subroles_roleid_cache[$roleid])) {
            unset(self::$_subroles_roleid_cache[$roleid]);
        }
    }

    /** Record Owner Id */
    static $_record_ownerid_cache = [];

    static function lookupRecordOwner($record)
    {
        if (isset(self::$_record_ownerid_cache[$record])) {
            return self::$_record_ownerid_cache[$record];
        }

        return false;
    }

    static function updateRecordOwner($record, $ownerId)
    {
        self::$_record_ownerid_cache[$record] = $ownerId;
    }

    /** Record Owner Type */
    static $_record_ownertype_cache = [];

    static function lookupOwnerType($ownerId)
    {
        if (isset(self::$_record_ownertype_cache[$ownerId])) {
            return self::$_record_ownertype_cache[$ownerId];
        }

        return false;
    }

    static function updateOwnerType($ownerId, $count)
    {
        self::$_record_ownertype_cache[$ownerId] = $count;
    }

    static $_outgoingMailFromEmailAddress;

    public static function getOutgoingMailFromEmailAddress()
    {
        return self::$_outgoingMailFromEmailAddress;
    }

    static $_userSignature = [];

    public static function setUserSignature($userName, $signature)
    {
        self::$_userSignature[$userName] = $signature;
    }

    public static function getUserSignature($userName)
    {
        return self::$_userSignature[$userName];
    }

    static $_userFullName = [];

    public static function setUserFullName($userName, $fullName)
    {
        self::$_userFullName[$userName] = $fullName;
    }

    public static function getUserFullName($userName)
    {
        return self::$_userFullName[$userName];
    }

    static $_report_field_bylabel = [];

    public static function getReportFieldByLabel($module, $label)
    {
        return self::$_report_field_bylabel[$module][$label];
    }

    public static function setReportFieldByLabel($module, $label, $fieldInfo)
    {
        self::$_report_field_bylabel[$module][$label] = $fieldInfo;
    }

    /** Record group Id */
    static $_record_groupid_cache = [];

    static function lookupRecordGroup($record)
    {
        if (isset(self::$_record_groupid_cache[$record])) {
            return self::$_record_groupid_cache[$record];
        }

        return false;
    }

    static function updateRecordGroup($record, $groupId)
    {
        self::$_record_groupid_cache[$record] = $groupId;
    }
}