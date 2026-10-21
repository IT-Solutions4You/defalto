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
 * Recipients for the composer — the e-mail fields of every module that has one
 * (vtiger_emailslookup, ITS4YouEmails_Module_Model::getEmailRelatedModules())
 * plus active users. Records with "Email Opt Out" are included and flagged:
 * the opt-out serves mass mailing, a reply to a ticket or to a customer's own
 * question is not mass mail, such a person must still be answerable
 */
class MailManager_ProRecipientSearch_Model
{
    /** Rows read per lookup — the action shows fewer anyway */
    private const LIMIT = 50;

    /** Result cap of the composer picker — the user narrows the term instead of scrolling */
    public const MAX_RESULTS = 30;

    /**
     * @param string $term what the user typed
     * @return array module label => [crmid => [['value', 'label', 'name', 'optout' => bool]]]
     */
    public static function search($term)
    {
        $matches = self::searchRecords($term);

        foreach (self::searchUsers($term) as $userId => $emails) {
            $matches[vtranslate('Users', 'Users')][$userId] = $emails;
        }

        return $matches;
    }

    /**
     * Modules whose e-mail fields are searched (Users are searched apart)
     *
     * @return string[]
     */
    public static function getSearchModules()
    {
        $modules = [];
        foreach ((array)Vtiger_Module_Model::getInstance('ITS4YouEmails')->getEmailRelatedModules() as $module) {
            if ($module !== 'Users' && $module !== 'ModComments') {
                $modules[] = $module;
            }
        }

        return $modules;
    }

    /**
     * The viewable e-mail fields of the searched modules in vtiger_emailslookup,
     * every row flagged with the opt-out of the three modules the core reads it from
     *
     * @param string $term
     * @return array like search()
     */
    private static function searchRecords($term)
    {
        $modules = [];
        $fieldIds = [];
        foreach (self::getSearchModules() as $module) {
            $moduleModel = Vtiger_Module_Model::getInstance($module);
            if (!$moduleModel) {
                continue;
            }
            $modules[] = $module;
            foreach ($moduleModel->getFieldsByType('email') as $fieldModel) {
                if ($fieldModel->isViewable()) {
                    $fieldIds[] = $fieldModel->get('id');
                }
            }
        }
        if (!$modules || !$fieldIds) {
            return [];
        }

        $adb = PearDatabase::getInstance();
        $like = '%' . $term . '%';
        $result = $adb->pquery(
            'SELECT el.crmid, el.setype, el.value, ce.label,
                    (EXISTS (SELECT 1 FROM vtiger_contactdetails oc WHERE oc.contactid = el.crmid AND oc.emailoptout = 1)
                     OR EXISTS (SELECT 1 FROM vtiger_account oa WHERE oa.accountid = el.crmid AND oa.emailoptout = 1)
                     OR EXISTS (SELECT 1 FROM vtiger_leaddetails ol WHERE ol.leadid = el.crmid AND ol.emailoptout = 1)) AS optout
             FROM vtiger_emailslookup el
             INNER JOIN vtiger_crmentity ce ON ce.crmid = el.crmid AND ce.deleted = 0
             WHERE el.fieldid IN (' . generateQuestionMarks($fieldIds) . ')
               AND el.setype IN (' . generateQuestionMarks($modules) . ')
               AND (el.value LIKE ? OR ce.label LIKE ?)
             LIMIT ' . self::LIMIT,
            array_merge($fieldIds, $modules, [$like, $like])
        );

        $matches = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            if (!Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', (int)$row['crmid'])) {
                continue;
            }
            $name = decode_html((string)$row['label']);
            $matches[vtranslate($row['setype'], $row['setype'])][(int)$row['crmid']][] = [
                'value' => (string)$row['value'],
                'label' => $name . ' (' . $row['value'] . ')',
                'name' => $name,
                'optout' => !empty($row['optout']),
            ];
        }

        return $matches;
    }

    /**
     * Active users by name or primary e-mail
     *
     * @param string $term
     * @return array userid => [['value', 'label', 'name', 'optout' => false]]
     */
    private static function searchUsers($term)
    {
        $adb = PearDatabase::getInstance();
        $like = '%' . $term . '%';
        $result = $adb->pquery(
            "SELECT id, first_name, last_name, email1 FROM vtiger_users
             WHERE deleted = 0 AND status = 'Active' AND email1 <> ''
               AND (email1 LIKE ? OR CONCAT_WS(' ', first_name, last_name) LIKE ?)
             LIMIT " . self::LIMIT,
            [$like, $like]
        );

        $matches = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $name = decode_html(trim($row['first_name'] . ' ' . $row['last_name']));
            $matches[(int)$row['id']][] = [
                'value' => (string)$row['email1'],
                'label' => $name . ' (' . $row['email1'] . ')',
                'name' => $name,
                'optout' => false,
            ];
        }

        return $matches;
    }

    /**
     * Recipient groups of the composer To field picker: the matches by module, each
     * with the module icon, at most MAX_RESULTS addresses.
     *
     * @param string $term search term
     *
     * @return array [['text' => module label, 'icon', 'children' => [['email', 'name', 'label', 'crmid', 'icon', 'optout']]]]
     */
    public static function getRecipientGroups(string $term): array
    {
        $groups = [];
        $total = 0;

        try {
            // the people with "Email Opt Out" included — answering a
            // customer is not mass mail
            $matches = self::search($term);
        } catch (Exception $e) {
            // a picker that cannot search must not break the composer
            $matches = [];
        }

        $icons = self::getModuleIcons();

        foreach ($matches as $moduleLabel => $records) {
            $icon = $icons[$moduleLabel] ?? '';
            $children = [];

            foreach ($records as $crmId => $emails) {
                foreach ($emails as $email) {
                    if ($total >= self::MAX_RESULTS) {
                        break 3;
                    }

                    $children[] = [
                        'email' => $email['value'],
                        'name' => $email['name'],
                        'label' => $email['label'],
                        'crmid' => (int)$crmId,
                        'icon' => $icon,
                        'optout' => !empty($email['optout']),
                    ];
                    $total++;
                }
            }

            if ($children) {
                $groups[] = ['text' => $moduleLabel, 'icon' => $icon, 'children' => $children];
            }
        }

        return $groups;
    }

    /**
     * Module icons keyed by the TRANSLATED module label — the core search
     * groups its results by that label, so this is the only key both sides
     * share (same vtranslate call, same result)
     *
     * @return array label => icon markup
     */
    private static function getModuleIcons(): array
    {
        $modules = array_merge(self::getSearchModules(), ['Users']);
        $icons = [];

        foreach ($modules as $moduleName) {
            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

            if ($moduleModel) {
                $icons[vtranslate($moduleName, $moduleName)] = $moduleModel->getModuleIcon();
            }
        }

        return $icons;
    }
}
