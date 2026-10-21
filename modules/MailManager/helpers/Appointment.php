<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * An Appointment made from a mail — shared by Basic (Relation view) and Pro
 * (CRM panel). Appointments hold their relations in FIELDS: an organization in
 * account_id, any other record in parent_id (when the field accepts its module),
 * people in the multi-value contact_id (ids separated by ";").
 */
class MailManager_Appointment_Helper
{
    /** The Defalto calendar module (replaces vtiger Calendar / Events) */
    public const MODULE = 'Appointments';

    /**
     * The reference fields of an Appointment about a record: a person also
     * brings the organization it works for.
     *
     * @param int $recordId the record the mail is linked to, 0 = none
     *
     * @return array field name => value (account_id, parent_id, contact_id)
     */
    public static function getReferenceValues(int $recordId): array
    {
        $module = $recordId && isRecordExists($recordId) ? getSalesEntityType($recordId) : '';

        if ('' === $module) {
            return [];
        }

        if ('Accounts' === $module) {
            return ['account_id' => $recordId];
        }

        if ('Contacts' === $module) {
            $values = ['contact_id' => $recordId];
            $accountId = (int)Vtiger_Record_Model::getInstanceById($recordId, 'Contacts')->get('account_id');

            if ($accountId && 'Accounts' === getSalesEntityType($accountId)) {
                $values['account_id'] = $accountId;
            }

            return $values;
        }

        return self::acceptsParent($module) ? ['parent_id' => $recordId] : [];
    }

    /**
     * Fills the reference fields of a saved Appointment: the target goes to
     * account_id (an organization) or parent_id (when the field accepts its
     * module), the person is added to contact_id. Filled values are kept.
     *
     * @param int $appointmentId Appointments record id
     * @param int $targetId      record the appointment is about, 0 = none
     * @param int $contactId     person of the mail, 0 = none
     *
     * @return void
     * @throws Exception
     */
    public static function relate(int $appointmentId, int $targetId, int $contactId): void
    {
        $record = Vtiger_Record_Model::getInstanceById($appointmentId, self::MODULE);
        $moduleModel = $record->getModule();
        $changed = false;

        if ($targetId) {
            $targetModule = getSalesEntityType($targetId);
            $fieldName = 'Accounts' === $targetModule ? 'account_id' : 'parent_id';
            $fieldModel = $moduleModel->getField($fieldName);

            if ($fieldModel && $record->isEmpty($fieldName)
                && ('account_id' === $fieldName || in_array($targetModule, (array)$fieldModel->getReferenceList(), true))) {
                $record->set($fieldName, $targetId);
                $changed = true;
            }
        }

        if ($contactId && $moduleModel->getField('contact_id')) {
            $contactIds = array_filter(explode(';', (string)$record->get('contact_id')));

            if (!in_array((string)$contactId, $contactIds, true)) {
                $contactIds[] = (string)$contactId;
                $record->set('contact_id', implode(';', $contactIds));
                $changed = true;
            }
        }

        if ($changed) {
            $record->set('mode', 'edit');
            $record->save();
        }
    }

    /**
     * @param string $module
     *
     * @return bool parent_id of an Appointment accepts records of the module
     */
    private static function acceptsParent(string $module): bool
    {
        $fieldModel = Vtiger_Module_Model::getInstance(self::MODULE)->getField('parent_id');

        return $fieldModel && in_array($module, (array)$fieldModel->getReferenceList(), true);
    }

    /**
     * Saves an Appointment from the Basic "create" form of a mail: the record, its
     * relations to the parent's organization and person, the mail's attachments and
     * the mail linked to it.
     *
     * @param array                     $data     the submitted form values
     * @param MailManager_Message_Model $mail     the mail it is made from
     * @param int                       $parentId the record the mail is linked to
     *
     * @return int appointment id
     * @throws Exception when the appointment cannot be saved
     */
    public static function createFromMail(array $data, MailManager_Message_Model $mail, int $parentId): int
    {
        $data['module'] = self::MODULE;
        $data['action'] = 'Save';
        $data['source'] = 'Mail Manager';
        unset($data['record']);

        $recordModel = (new Appointments_Save_Action())->saveRecord(new Vtiger_Request($data, $data));
        $appointmentId = (int)$recordModel->getId();
        $references = self::getReferenceValues($parentId);

        self::relate(
            $appointmentId,
            (int)($references['account_id'] ?? $references['parent_id'] ?? 0),
            (int)($references['contact_id'] ?? 0)
        );
        $mail->setAttachmentRelationIds($appointmentId);
        MailManager_Relate_Model::associate($mail, $appointmentId, $parentId);

        return $appointmentId;
    }

    /**
     * The description of an Appointment made from a mail — a text field: the readable
     * text of the mail, without the HTML of its body.
     *
     * @param MailManager_Message_Model $mail
     *
     * @return string
     */
    public static function getDescription(MailManager_Message_Model $mail): string
    {
        $body = $mail->getBody(false);

        // a plain text mail stays as it is ("<name@domain>" must not be taken for a tag)
        if (!preg_match('/<(html|body|div|p|br|table|span|font)\b/i', $body)) {
            return trim($body);
        }

        return MailManager_ProMailText_Model::htmlToText($body);
    }
}
