<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouCalendar_InviteUsers_UIType extends Vtiger_Base_UIType
{
    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return 'uitypes/InviteUsers.tpl';
    }

    /**
     * @param mixed $value
     * @param int|bool $record
     * @param int|object $recordInstance
     * @return string
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        if (empty($record)) {
            return '';
        }

        $invitedUsers = ITS4YouCalendar_InvitedUsers_Model::getInstance($record);
        $invitedUsers->retrieveUsers();

        return implode(', ', array_intersect_key($invitedUsers::getAccessibleUsers(), $invitedUsers->getUsersInfo()));
    }

    public function getAccessibleUsers()
    {
        return ITS4YouCalendar_InvitedUsers_Model::getAccessibleUsers();
    }

    public function getDBInsertValue($value): string
    {
        return !empty($value) && is_array($value) ? implode(',', $value) : $value; // TODO: Change the autogenerated stub
    }

    public function getInvitedUsers($record): array
    {
        if (empty($record)) {
            return [];
        }

        $invitedUsers = ITS4YouCalendar_InvitedUsers_Model::getInstance($record);
        $invitedUsers->retrieveUsers();

        return $invitedUsers->getUsers();
    }
}