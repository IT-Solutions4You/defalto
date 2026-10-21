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
 * CRM side panel of a conversation – linked records, suggestions by participant
 * e-mails, the comment / appointment / project task actions and the prefilled
 * "create" actions.
 */
class MailManager_ProCrmPanel_Model
{
    /**
     * Everything the CRM panel template shows. The panel shows (and acts on) only
     * records the user may view.
     *
     * @param array $thread  thread row of an accessible mailbox
     * @param array $mailbox accessible mailbox row
     *
     * @return array template variables
     */
    public static function getViewData(array $thread, array $mailbox): array
    {
        $threadId = (int)$thread['threadid'];
        $links = MailManager_ProRelate_Model::getLinks($threadId, true, true);
        $threadRows = [$thread];
        MailManager_ProThreadList_Model::attachDrivingRecords($threadRows);
        $driving = $threadRows[0]['driving'] ?? null;
        $linkedByModule = array_column($links, 'label', 'module');
        $linkedIdByModule = array_column($links, 'crmid', 'module');
        $suggestions = MailManager_ProRecordMatch_Model::getThreadSuggestions($thread, $mailbox, $links);
        $pickerParent = MailManager_ProRelate_Model::getLinkedAccount($links);

        return [
            'THREAD_ID' => $threadId,
            'THREAD' => $thread,
            // how each record came to the conversation — linked automatically or by hand
            'LINKS' => MailManager_ProRelate_Model::markAutoLinked($links, $thread),
            // linking an existing record – the picker lists the records of the linked
            // organization first (the person is left out on purpose)
            'PICKER_PARENT' => $pickerParent,
            'PICKER_MODULES' => MailManager_ProRelate_Model::getPickerModules(
                $pickerParent ? $pickerParent['crmid'] : 0,
                array_keys($linkedIdByModule),
                (int)($linkedIdByModule['Project'] ?? 0)
            ),
            'SUGGESTIONS' => $suggestions,
            // the certain next records of the conversation also show in the suggestion
            // card above the mail
            'LINK_STEPS' => array_values(array_filter($suggestions, function ($suggestion) {
                return in_array($suggestion['reason'] ?? '', ['potential', 'ticket_no'], true);
            })),
            'DRIVING' => $driving,
            'ACTIONS' => self::getActions($thread, $driving, $linkedByModule, $linkedIdByModule),
            'AUTOCOMMENT' => $driving && MailManager_ProThreadList_Model::isAutoCommentCurrent($threadId, (int)$thread['autocomment_msgid']),
            'TRIAGE' => MailManager_ProTriageBanner_Model::getBannerData($thread, $mailbox),
            'CREATE_PREFILLS' => self::getCreatePrefills($thread, $mailbox, $links),
        ];
    }

    /**
     * Comment, appointment and project task actions of the CRM panel. The comment
     * belongs to the driving record (Ticket/Opportunity/Lead); an appointment makes
     * sense with the organization or person alone as well.
     *
     * @param array      $thread           thread row
     * @param array|null $driving          driving record of the thread
     * @param array      $linkedByModule   module => record label of the linked records
     * @param array      $linkedIdByModule module => crmid of the linked records
     *
     * @return array|null
     */
    public static function getActions(array $thread, ?array $driving, array $linkedByModule, array $linkedIdByModule): ?array
    {
        $threadId = (int)$thread['threadid'];
        $eventTarget = null;

        if ($driving && 'ProjectTask' === $driving['module'] && isset($linkedIdByModule['Project'])) {
            // a meeting belongs to the project, the task is one piece of its work
            $eventTarget = ['crmid' => (int)$linkedIdByModule['Project'], 'label' => $linkedByModule['Project']];
        } elseif ($driving) {
            $eventTarget = ['crmid' => (int)$driving['crmid'], 'label' => $driving['record_label']];
        } elseif (isset($linkedIdByModule['Accounts'])) {
            $eventTarget = ['crmid' => (int)$linkedIdByModule['Accounts'], 'label' => $linkedByModule['Accounts']];
        } elseif (isset($linkedIdByModule['Contacts'])) {
            // a contact rides in contact_id, the appointment has no parent then
            $eventTarget = ['crmid' => 0, 'label' => $linkedByModule['Contacts']];
        }

        $actions = null;
        $canSchedule = null !== $eventTarget && Users_Privileges_Model::isPermitted('Appointments', 'CreateView');

        if ($driving || $canSchedule) {
            $actions = [
                'comments' => $driving
                    && Vtiger_Module_Model::getInstance($driving['module'])->isCommentEnabled()
                    && Users_Privileges_Model::isPermitted('ModComments', 'CreateView'),
                'activities' => $canSchedule,
                'event_crmid' => $eventTarget['crmid'] ?? 0,
                'event_label' => $eventTarget['label'] ?? '',
                'comment_prefill' => $driving ? MailManager_ProCreatePrefill_Model::getCommentPrefill($threadId) : '',
            ];
        }

        // a task goes straight into the linked project – whatever else is linked
        $taskPrefill = MailManager_ProCreatePrefill_Model::getTaskPrefill($thread, (int)($linkedIdByModule['Project'] ?? 0));

        if ($taskPrefill) {
            $actions = ($actions ?: ['comments' => false, 'activities' => false, 'comment_prefill' => '']) + [
                    'task_json' => json_encode($taskPrefill['params'], JSON_UNESCAPED_UNICODE),
                    'task_labels_json' => json_encode($taskPrefill['labels'], JSON_UNESCAPED_UNICODE),
                ];
        }

        return $actions;
    }

    /**
     * Prefilled "create record" definitions of the CRM panel with their JSON for the
     * quick-create forms.
     *
     * @param array $thread  thread row
     * @param array $mailbox mailbox row
     * @param array $links   linked records
     *
     * @return array module => definition
     */
    public static function getCreatePrefills(array $thread, array $mailbox, array $links): array
    {
        $prefills = MailManager_ProCreatePrefill_Model::getCreatePrefills($thread, $mailbox['email'], $links);

        foreach ($prefills as $module => $definition) {
            $prefills[$module]['json'] = json_encode($definition['params'], JSON_UNESCAPED_UNICODE);
            $prefills[$module]['labels_json'] = json_encode($definition['labels'], JSON_UNESCAPED_UNICODE);

            if (!empty($definition['projects'])) {
                $prefills[$module]['projects_json'] = json_encode($definition['projects'], JSON_UNESCAPED_UNICODE);
            }
        }

        return $prefills;
    }
}
