<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Appointments_ConvertLead_Helper
{
    /**
     * Converts appointments for a given lead to accounts and/or contacts.
     *
     * @param int $leadId    The ID of the lead to convert appointments from.
     * @param int $accountId The ID of the account to assign the appointments to. Set to 0 for no assignment.
     * @param int $contactId The ID of the contact to assign the appointments to. Set to 0 for no assignment.
     *
     * @return void
     */
    public static function convertAppointments(int $leadId, int $accountId, int $contactId): void
    {
        $db = PearDatabase::getInstance();

        $appointmentRes = $db->pquery('SELECT its4you_calendar_id, contact_id FROM its4you_calendar WHERE parent_id = ?', [$leadId]);

        while ($appointmentRow = $db->fetchByAssoc($appointmentRes)) {
            if ($accountId) {
                $db->pquery('INSERT INTO vtiger_crmentityrel VALUES (?, ?, ?, ?)', [$accountId, 'Accounts', $appointmentRow['its4you_calendar_id'], 'Appointments']);
                $db->pquery('UPDATE its4you_calendar SET account_id = ? WHERE its4you_calendar_id = ?', [$accountId, $appointmentRow['its4you_calendar_id']]);
            }

            if ($contactId) {
                $db->pquery('INSERT INTO vtiger_crmentityrel VALUES (?, ?, ?, ?)', [$contactId, 'Contacts', $appointmentRow['its4you_calendar_id'], 'Appointments']);
                $contacts = array_unique(explode(';', $appointmentRow['contact_id']));
                $contacts[] = $contactId;
                $db->pquery('UPDATE its4you_calendar SET account_id = ? WHERE its4you_calendar_id = ?', [implode(';', $contacts), $appointmentRow['its4you_calendar_id']]);
            }
        }
    }
}