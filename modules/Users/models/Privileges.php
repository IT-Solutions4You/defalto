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

/**
 * User Privileges Model Class
 */
#[\AllowDynamicProperties] class Users_Privileges_Model extends Users_Record_Model
{

    /**
     * Rebuild privilege and sharing files for every active user.
     */
    public static function recalculateSharingRules(): void
    {
        global $log;
        $log->debug('Entering Users_Privileges_Model::recalculateSharingRules() method ...');

        require_once 'modules/Users/CreateUserPrivilegeFile.php';
        $db = PearDatabase::getInstance();
        $result = $db->pquery(
            "SELECT id FROM vtiger_users WHERE deleted = 0 AND status = 'Active'",
            []
        );

        while ($row = $db->fetchByAssoc($result)) {
            $userId = (int)$row['id'];
            createUserPrivilegesfile($userId);
            createUserSharingPrivilegesfile($userId);
        }

        $log->debug('Exiting Users_Privileges_Model::recalculateSharingRules() method ...');
    }

    /**
     * Function to get the Global Read Permission for the user
     * @return <Number> 0/1
     */
    protected function getGlobalReadPermission()
    {
        $profileGlobalPermissions = $this->get('profile_global_permission');

        return $profileGlobalPermissions[Settings_Profiles_Module_Model::GLOBAL_ACTION_VIEW];
    }

    /**
     * Function to get the Global Write Permission for the user
     * @return <Number> 0/1
     */
    protected function getGlobalWritePermission()
    {
        $profileGlobalPermissions = $this->get('profile_global_permission');

        return $profileGlobalPermissions[Settings_Profiles_Module_Model::GLOBAL_ACTION_EDIT];
    }

    /**
     * Function to check if the user has Global Read Permission
     * @return <Boolean> true/false
     */
    public function hasGlobalReadPermission()
    {
        return ($this->isAdminUser() ||
            $this->getGlobalReadPermission() === Settings_Profiles_Module_Model::IS_PERMITTED_VALUE ||
            $this->getGlobalWritePermission() === Settings_Profiles_Module_Model::IS_PERMITTED_VALUE);
    }

    /**
     * Function to check if the user has Global Write Permission
     * @return <Boolean> true/false
     */
    public function hasGlobalWritePermission()
    {
        return ($this->isAdminUser() || $this->getGlobalWritePermission() === Settings_Profiles_Module_Model::IS_PERMITTED_VALUE);
    }

    public function hasGlobalPermission($actionId)
    {
        if ($actionId == Settings_Profiles_Module_Model::GLOBAL_ACTION_VIEW) {
            return $this->hasGlobalReadPermission();
        }
        if ($actionId == Settings_Profiles_Module_Model::GLOBAL_ACTION_EDIT) {
            return $this->hasGlobalWritePermission();
        }

        return false;
    }

    /**
     * Function to check whether the user has access to a given module by tabid
     *
     * @param <Number> $tabId
     *
     * @return <Boolean> true/false
     */
    public function hasModulePermission($tabId)
    {
        $profileTabsPermissions = $this->get('profile_tabs_permission');
        $moduleModel = Vtiger_Module_Model::getInstance($tabId);

        return (($this->isAdminUser() || $profileTabsPermissions[$tabId] === 0) && $moduleModel->isActive());
    }

    /**
     * Function to check whether the user has access to the specified action/operation on a given module by tabid
     *
     * @param <Number> $tabId
     * @param <String/Number> $action
     *
     * @return <Boolean> true/false
     */
    public function hasModuleActionPermission($tabId, $action)
    {
        if (!is_a($action, 'Vtiger_Action_Model')) {
            $action = Vtiger_Action_Model::getInstance($action);
        }
        $actionId = $action->getId();
        $profileTabsPermissions = $this->get('profile_action_permission');
        $moduleModel = Vtiger_Module_Model::getInstance($tabId);

        return (($this->isAdminUser() || $profileTabsPermissions[$tabId][$actionId] === Settings_Profiles_Module_Model::IS_PERMITTED_VALUE)
            && $moduleModel->isActive());
    }

    /**
     * Static Function to get the instance of the User Privileges model from the given list of key-value array
     *
     * @param <Array> $valueMap
     *
     * @return Users_Privilege_Model object
     */
    public static function getInstance($valueMap)
    {
        $instance = new self();
        foreach ($valueMap as $key => $value) {
            $instance->$key = $value;
        }
        $instance->setData($valueMap);

        return $instance;
    }

    /**
     * Static Function to get the instance of the User Privileges model, given the User id
     *
     * @param <Number> $userId
     *
     * @return Users_Privilege_Model object
     */
    public static function getInstanceById($userId, $module = null)
    {
        if (empty($userId)) {
            return null;
        }

        $acl = Vtiger_AccessControl::loadUserPrivileges($userId);
        require("user_privileges/sharing_privileges_$userId.php");

        $valueMap = [];
        $valueMap['id'] = $userId;
        $valueMap['is_admin'] = isset($acl->is_admin) ? (bool)$acl->is_admin : false;
        $valueMap['roleid'] = isset($acl->current_user_roles) ? $acl->current_user_roles : null;
        $valueMap['parent_role_seq'] = isset($acl->current_user_parent_role_seq) ? $acl->current_user_parent_role_seq : null;
        $valueMap['profiles'] = isset($acl->current_user_profiles) ? $acl->current_user_profiles : null;
        $valueMap['profile_global_permission'] = isset($acl->profileGlobalPermission) ? $acl->profileGlobalPermission : null;
        $valueMap['profile_tabs_permission'] = isset($acl->profileTabsPermission) ? $acl->profileTabsPermission : null;
        $valueMap['profile_action_permission'] = isset($acl->profileActionPermission) ? $acl->profileActionPermission : null;
        $valueMap['groups'] = isset($acl->current_user_groups) ? $acl->current_user_groups : null;
        $valueMap['subordinate_roles'] = isset($acl->subordinate_roles) ? $acl->subordinate_roles : null;
        $valueMap['parent_roles'] = isset($acl->parent_roles) ? $acl->parent_roles : null;
        $valueMap['subordinate_roles_users'] = isset($acl->subordinate_roles_users) ? $acl->subordinate_roles_users : null;
        $valueMap['defaultOrgSharingPermission'] = isset($defaultOrgSharingPermission) ? $defaultOrgSharingPermission : null;
        $valueMap['related_module_share'] = isset($related_module_share) ? $related_module_share : null;

        if (is_array($acl->user_info)) {
            $valueMap = array_merge($valueMap, $acl->user_info);
        }

        return self::getInstance($valueMap);
    }

    /**
     * Static function to get the User Privileges Model for the current user
     * @return Users_Privilege_Model object
     */
    public static function getCurrentUserPrivilegesModel()
    {
        //TODO : Remove the global dependency
        $currentUser = vglobal('current_user');
        $currentUserId = $currentUser->id;

        return self::getInstanceById($currentUserId);
    }

    /**
     * Check whether the current user may perform an action.
     *
     * @param string     $moduleName
     * @param string     $actionName
     * @param int|string $record
     */
    public static function isPermitted($moduleName, $actionName, $record = false): bool
    {
        return self::getPermission($moduleName, $actionName, $record) === 'yes';
    }

    /**
     * Resolve the current user's module, action, record ownership, and sharing permission.
     *
     * @param string     $module
     * @param string     $actionname
     * @param int|string $record_id
     */
    public static function getPermission($module, $actionname, $record_id = ''): string
    {
        $is_admin = null;
        $current_user_groups = null;
        $profileTabsPermission = [];
        $profileGlobalPermission = [];
        $profileActionPermission = [];
        $defaultOrgSharingPermission = [];
        $sharingPermission = 'no';

        if (!Core_Readonly_Model::isPermitted((string)$module, (int)$record_id, $actionname)) {
            return 'no';
        }

        if (!Core_RecordActionPermission_Model::isPermissionActionPermitted((string)$module, (string)$actionname, $record_id)) {
            return 'no';
        }

        global $log;
        $log->debug("Entering isPermitted(" . $module . "," . $actionname . "," . $record_id . ") method ...");

        global $adb;
        global $current_user;
        global $seclog;
        require('user_privileges/user_privileges_' . $current_user->id . '.php');
        require('user_privileges/sharing_privileges_' . $current_user->id . '.php');
        $permission = "no";
        $parenttab = isset($_REQUEST['parenttab']) ? $_REQUEST['parenttab'] : null;
        if (($module == 'Users' || $module == 'Home' || $module == 'uploads') && $parenttab != 'Settings') {
            //These modules dont have security right now
            $permission = "yes";
            $log->debug("Exiting isPermitted method ...");

            return $permission;
        }

        //Checking the Access for the Settings Module
        if ($module == 'Settings' || $module == 'Administration' || $module == 'System' || $parenttab == 'Settings') {
            if (!$is_admin) {
                $permission = "no";
            } else {
                $permission = "yes";
            }
            $log->debug("Exiting isPermitted method ...");

            return $permission;
        }

        //Retreiving the Tabid and Action Id
        $tabid = getTabid($module);
        $actionid = getActionid($actionname);
        $checkModule = $module;

        if (vtlib_isModuleActive($checkModule)) {
            //Checking whether the user is admin
            if ($is_admin) {
                $permission = "yes";
                $log->debug("Exiting isPermitted method ...");

                return $permission;
            }

            if ('' != $record_id) {
                $result = $adb->pquery(
                    'SELECT parentrole FROM vtiger_role INNER JOIN vtiger_user2role ON vtiger_user2role.roleid=vtiger_role.roleid AND userid=?',
                    [$current_user->id]
                );
                $parentRole = $adb->query_result($result, 0, 'parentrole');
                $parentRoleArr = explode('::', $parentRole);

                foreach ($parentRoleArr as $key => $value) {
                    $parentRoleArr[$key] = '\'' . $value . '\'';
                }

                $parentRole = implode(',', $parentRoleArr);

                $query = 'SELECT crmid,type FROM its4you_sharing_users WHERE userid=? AND crmid = ?';
                $params = [$current_user->id, $record_id];

                if (($current_user_groups === null ? 0 : count($current_user_groups)) > 0) {
                    $query .= ' UNION
                            SELECT crmid,type FROM its4you_sharing_groups WHERE groupid in (' . implode(',', $current_user_groups) . ') AND crmid = ?';
                    $params[] = $record_id;
                }

                $companyId = [];

                if (false !== Vtiger_Module_Model::getInstance('ITS4YouMultiCompany') && false !== Vtiger_Module_Model::getInstance('ITS4YouMultiCompany')->isActive()) {
                    $companyId = array_keys(ITS4YouMultiCompany_Module_Model::getCompaniesList());
                }

                $query .= ' UNION
                          SELECT crmid,type FROM its4you_sharing_roles
                              INNER JOIN vtiger_user2role ON vtiger_user2role.roleid=its4you_sharing_roles.roleid AND userid= ? AND crmid = ?
                          UNION
                          SELECT crmid,type FROM its4you_sharing_rolessubroles
                          INNER JOIN vtiger_role ON vtiger_role.roleid=its4you_sharing_rolessubroles.roleid
                          WHERE its4you_sharing_rolessubroles.roleid IN (' . $parentRole . ')
                          AND vtiger_role.parentrole LIKE CONCAT(\'%\', its4you_sharing_rolessubroles.roleid, \'%\') AND crmid = ?
                          ';
                array_push($params, $current_user->id, $record_id, $record_id);

                if (!empty($companyId)) {
                    $query .= ' UNION
                            SELECT crmid,type FROM its4you_sharing_multicompany WHERE companyid in(' . implode(',', $companyId) . ')
                            AND crmid = ?
                            ';
                    $params[] = $record_id;
                }

                $result = $adb->pquery($query, $params);

                if ($adb->num_rows($result)) {
                    $i = 0;

                    while ($type = $adb->query_result($result, $i, 'type')) {
                        $i++;

                        if (4 == $actionid) {
                            $sharingPermission = 'yes';
                        } elseif (2 == $type && (1 == $actionid || 0 == $actionid)) {
                            $sharingPermission = 'yes';
                        }
                    }
                }
            }

            //If no actionid, then allow action is vtiger_tab permission is available
            if ($actionid === '') {
                if ($profileTabsPermission[$tabid] == 0) {
                    $permission = "yes";
                    $log->debug("Exiting isPermitted method ...");
                } else {
                    $permission = "no";
                }

                return returnPermission($permission, $sharingPermission);
            }

            $action = getActionname($actionid);
            //Checking for view all permission
            if ($profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
                if ($actionid == 3 || $actionid == 4) {
                    $permission = "yes";
                    $log->debug("Exiting isPermitted method ...");

                    return returnPermission($permission, $sharingPermission);
                }
            }
            //Checking for edit all permission
            if ($profileGlobalPermission[2] == 0) {
                if ($actionid == 3 || $actionid == 4 || $actionid == 0 || $actionid == 1) {
                    $permission = "yes";
                    $log->debug("Exiting isPermitted method ...");

                    return returnPermission($permission, $sharingPermission);
                }
            }
            //Checking for vtiger_tab permission
            if ($profileTabsPermission[$tabid] != 0) {
                $permission = "no";
                $log->debug("Exiting isPermitted method ...");

                return returnPermission($permission, $sharingPermission);
            }
            //Checking for Action Permission
            $actionPermission = $profileActionPermission[$tabid][$actionid] ?? '';
            if ($actionPermission === '') {
                $permission = "yes";
                $log->debug("Exiting isPermitted method ...");

                return returnPermission($permission, $sharingPermission);
            }

            if ($actionPermission != 0) {
                $permission = "no";
                $log->debug("Exiting isPermitted method ...");

                return returnPermission($permission, $sharingPermission);
            }
            //Checking and returning true if recorid is null
            if ($record_id == '') {
                $permission = "yes";
                $log->debug("Exiting isPermitted method ...");

                return returnPermission($permission, $sharingPermission);
            }

            //If modules is Products,Vendors,Faq,PriceBook then no sharing
            if ($record_id != '') {
                if (getTabOwnedBy($module) == 1) {
                    $permission = "yes";
                    $log->debug("Exiting isPermitted method ...");

                    return returnPermission($permission, $sharingPermission);
                }
            }

            //Retreiving the RecordOwnerId
            $recOwnType = '';
            $recOwnId = '';
            $recordOwnerArr = getRecordOwnerId($record_id);
            if (empty($recordOwnerArr)) {
                $groupId = getRecordGroupId($record_id);
                $recordOwnerArr['Groups'] = $groupId;
            }

            foreach ($recordOwnerArr as $type => $id) {
                $recOwnType = $type;
                $recOwnId = $id;
            }
            //Retreiving the default Organisation sharing Access
            $others_permission_id = $defaultOrgSharingPermission[$tabid];

            if ($recOwnType == 'Users') {
                //Checking if the Record Owner is the current User
                if ($current_user->id == $recOwnId) {
                    $permission = "yes";
                    $log->debug("Exiting isPermitted method ...");

                    return returnPermission($permission, $sharingPermission);
                }
                //Checking if the Record Owner is the Subordinate User
                foreach ($subordinate_roles_users as $roleid => $userids) {
                    if (in_array($recOwnId, $userids)) {
                        $permission = 'yes';
                        $log->debug("Exiting isPermitted method ...");

                        return returnPermission($permission, $sharingPermission);
                    }
                }
            } elseif ($recOwnType == 'Groups') {
                //Checking if the record owner is the current user's group
                if (in_array($recOwnId, $current_user_groups)) {
                    $permission = 'yes';
                    $log->debug("Exiting isPermitted method ...");

                    return returnPermission($permission, $sharingPermission);
                }
            }

            //Checking for Default Org Sharing permission
            if ($others_permission_id == 0) {
                if ($actionid == 1 || $actionid == 0) {
                    $permission = isReadWritePermittedBySharing($module, $tabid, $actionid, $record_id);
                    $log->debug("Exiting isPermitted method ...");

                    return returnPermission($permission, $sharingPermission);
                } elseif ($actionid == 2) {
                    $permission = "no";
                    $log->debug("Exiting isPermitted method ...");

                    return returnPermission($permission, $sharingPermission);
                } else {
                    $permission = "yes";
                    $log->debug("Exiting isPermitted method ...");

                    return returnPermission($permission, $sharingPermission);
                }
            } elseif ($others_permission_id == 1) {
                if ($actionid == 2) {
                    $permission = "no";
                    $log->debug("Exiting isPermitted method ...");

                    return returnPermission($permission, $sharingPermission);
                } else {
                    $permission = "yes";
                    $log->debug("Exiting isPermitted method ...");

                    return returnPermission($permission, $sharingPermission);
                }
            } elseif ($others_permission_id == 2) {
                $permission = "yes";
                $log->debug("Exiting isPermitted method ...");

                return returnPermission($permission, $sharingPermission);
            } elseif ($others_permission_id == 3) {
                if ($actionid == 3 || $actionid == 4) {
                    $permission = isReadPermittedBySharing($module, $tabid, $actionid, $record_id);
                    $log->debug("Exiting isPermitted method ...");

                    return returnPermission($permission, $sharingPermission);
                } elseif ($actionid == 0 || $actionid == 1) {
                    $permission = isReadWritePermittedBySharing($module, $tabid, $actionid, $record_id);
                    $log->debug("Exiting isPermitted method ...");

                    return returnPermission($permission, $sharingPermission);
                } elseif ($actionid == 2) {
                    $permission = "no";

                    return $permission;
                } else {
                    $permission = "yes";
                    $log->debug("Exiting isPermitted method ...");

                    return returnPermission($permission, $sharingPermission);
                }
            } else {
                $permission = "yes";
            }
        } else {
            $permission = "no";
        }

        $log->debug("Exiting isPermitted method ...");

        return returnPermission($permission, $sharingPermission);
    }

    /**
     * Function returns non admin access control check query
     *
     * @param <String> $module
     *
     * @return <String>
     */
    public static function getNonAdminAccessControlQuery($module)
    {
        $currentUser = vglobal('current_user');

        return getNonAdminAccessControlQuery($module, $currentUser);
    }

    /**
     * Function to check permission for current user to change username
     *
     * @param <integer> $targetUserId
     *
     * @return boolean
     *
     * ::Rules::
     * 1. Admin can only change the username
     * 2. Admin shouldn't change other admin's username
     * 3. Only account owner can change other admin's username
     * 4. No one can change account owner's username
     */
    public static function isPermittedToChangeUsername($targetUserId)
    {
        $recordModel = parent::getInstanceFromPreferenceFile($targetUserId);
        $currentUserModel = parent::getCurrentUserModel();

        if (is_int($targetUserId)) {
            $targetUserId = strval($targetUserId);
        }
        if ($currentUserModel->isAdminUser() && !$recordModel->isAccountOwner()) {
            if ($targetUserId === $currentUserModel->getId() || !$recordModel->isAdminUser() || $currentUserModel->isAccountOwner()) {
                return true;
            }
        }

        return false;
    }
}
