<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_ConvertLead_Helper
{
    /**
     * Converts appointments for a given lead to accounts and/or contacts.
     *
     * @param int $leadId The ID of the lead to convert appointments from.
     * @param int $accountId The ID of the account to assign the appointments to. Set to 0 for no assignment.
     * @param int $contactId The ID of the contact to assign the appointments to. Set to 0 for no assignment.
     * @param int $potentialId
     * @return void
     * @throws Exception
     */
    public static function convertAppointments(int $leadId, int $accountId, int $contactId, int $potentialId = 0): void
    {
        $db = PearDatabase::getInstance();

        $appointmentRes = $db->pquery('SELECT its4you_calendar_id as id, contact_id FROM its4you_calendar WHERE parent_id = ?', [$leadId]);
        $table = Core_DatabaseData_Model::getTableInstance('its4you_calendar', 'its4you_calendar_id');

        while ($appointmentRow = $db->fetchByAssoc($appointmentRes)) {
            $calendarId = $appointmentRow['id'];

            foreach (array_filter([$accountId, $contactId, $potentialId]) as $id) {
                $db->pquery('INSERT INTO vtiger_crmentityrel VALUES (?, ?, ?, ?)', [$id, getSalesEntityType($id), $calendarId, 'Appointments']);
            }

            if (!empty($contactId)) {
                $db->pquery('INSERT INTO vtiger_crmentityrel VALUES (?, ?, ?, ?)', [$calendarId, 'Appointments', $contactId, 'Contacts']);
            }

            $table->updateData(['account_id' => $accountId, 'contact_id' => $contactId, 'parent_id' => $potentialId], ['its4you_calendar_id' => $calendarId]);
        }
    }
}