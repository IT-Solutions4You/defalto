<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_Field_Model extends Vtiger_Field_Model
{
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
    public function isEmptyPicklistOptionAllowed(): bool
    {
        if (in_array($this->getFieldName(), ['calendar_visibility', 'calendar_status'])) {
            return false;
        }

        return true;
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