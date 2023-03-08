<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouCalendar_Field_Model extends Vtiger_Field_Model
{
    /**
     * @return bool
     */
    public function isEmptyPicklistOptionAllowed(): bool
    {
        if ('calendar_visibility' === $this->getFieldName()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getFieldDataType(): string
    {
        switch ($this->getName()) {
            case 'recurring_type':
                return 'recurrence';
            case 'reminder_time':
                return 'reminder';
            case 'invite_users':
                return 'InviteUsers';
            case 'contact_id':
                return 'MultiReference';
        }

        return parent::getFieldDataType();
    }

    /**
     * @return bool
     */
    public function isQuickCreateEnabled(): bool
    {
        switch ($this->getName()) {
            case 'recurring_type':
            case 'reminder_time':
                return false;
        }

        return parent::isQuickCreateEnabled();
    }
}