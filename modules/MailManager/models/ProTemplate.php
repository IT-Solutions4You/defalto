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
 * EMAIL Maker templates in the composer: the templates the user may use for the
 * record the conversation is about (permissions, sharing, the user's active
 * templates and the display conditions are EMAIL Maker's own), and one template
 * filled for that record and the recipient.
 */
class MailManager_ProTemplate_Model
{
    /** The template module */
    public const MODULE = 'EMAILMaker';

    /**
     * @return bool EMAIL Maker is installed and active
     */
    public static function isAvailable(): bool
    {
        return vtlib_isModuleActive(self::MODULE) && class_exists('EMAILMaker_EMAILMaker_Model');
    }

    /**
     * The record the template variables come from — the conversation's end record
     * when the user may see it.
     *
     * @param int $threadId 0 = a new mail without a conversation
     *
     * @return array ['module' => string, 'crmid' => int]
     */
    public static function getSource(int $threadId): array
    {
        $target = $threadId ? MailManager_ProEmailFiler_Model::getEndRecord($threadId) : null;

        if ($target && Users_Privileges_Model::isPermitted($target['module'], 'DetailView', (int)$target['crmid'])) {
            return ['module' => (string)$target['module'], 'crmid' => (int)$target['crmid']];
        }

        return ['module' => '', 'crmid' => 0];
    }

    /**
     * The templates for the conversation's record and those without a module.
     *
     * @param int $threadId
     *
     * @return array of ['label' => category ('' = none), 'templates' => [templateid => name]]
     */
    public static function getTemplates(int $threadId): array
    {
        if (!self::isAvailable()) {
            return [];
        }

        $source = self::getSource($threadId);
        $list = EMAILMaker_EMAILMaker_Model::getInstance()->getAvailableTemplatesArray(
            $source['module'],
            false,
            $source['crmid'] ?: false,
            false,
            true
        );
        $groups = [];

        if (!empty($list[0])) {
            $groups[] = ['label' => '', 'templates' => array_column($list[0], 'label', 'value')];
        }

        foreach ((array)($list[1] ?? []) as $category => $options) {
            $groups[] = ['label' => (string)$category, 'templates' => array_column($options, 'label', 'value')];
        }

        return $groups;
    }

    /**
     * One template filled for the conversation's record and the recipient. Only a
     * template the user is offered for this record can be filled.
     *
     * @param int    $templateId
     * @param int    $threadId
     * @param string $recipientEmail the first recipient, '' = none
     *
     * @return array|null ['subject', 'body', 'documents' => [['id', 'name']]], null when not offered
     * @throws Exception
     */
    public static function fill(int $templateId, int $threadId, string $recipientEmail): ?array
    {
        if (!self::isOffered($templateId, $threadId)) {
            return null;
        }

        $source = self::getSource($threadId);
        $recipientIds = '' !== $recipientEmail
            ? MailManager_ProEmailFiler_Model::resolveRecipientRecords([$recipientEmail])
            : [];
        $recipientId = (int)($recipientIds[0] ?? 0);
        $language = (string)Users_Record_Model::getCurrentUserModel()->get('language');

        // EMAIL Maker keeps its state in static properties — one template per request
        $content = EMAILMaker_EMAILContent_Model::getInstanceById(
            $templateId,
            $language,
            $source['module'],
            $source['crmid'],
            $recipientId ?: null,
            $recipientId ? getSalesEntityType($recipientId) : null
        );

        if ($source['crmid'] || $recipientId) {
            $content->getContent((bool)$recipientId, (bool)$source['crmid']);
        }

        $documents = [];

        foreach (array_unique(array_map('intval', (array)$content->getAttachments())) as $documentId) {
            if ($documentId && Users_Privileges_Model::isPermitted('Documents', 'DetailView', $documentId)) {
                $documents[] = ['id' => $documentId, 'name' => decode_html((string)Vtiger_Functions::getCRMRecordLabel($documentId))];
            }
        }

        return [
            'subject' => decode_html((string)$content->getSubject()),
            'body' => (string)$content->getBody(),
            'documents' => $documents,
        ];
    }

    /**
     * @param int $templateId
     * @param int $threadId
     *
     * @return bool the template is among getTemplates() of the conversation
     */
    private static function isOffered(int $templateId, int $threadId): bool
    {
        foreach (self::getTemplates($threadId) as $group) {
            if (isset($group['templates'][$templateId])) {
                return true;
            }
        }

        return false;
    }
}
