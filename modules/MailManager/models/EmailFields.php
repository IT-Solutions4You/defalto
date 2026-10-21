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
 * The e-mail fields of records selected for a mail from the Basic composer (the
 * "select e-mail fields" step of the mass action): the viewable e-mail fields of each
 * module, the preferred ones and the addresses of the records without an opt-out.
 */
class MailManager_EmailFields_Model
{
    /**
     * The modules of the selected records.
     *
     * @param bool   $linkToModule  the records come from a related list (selected_ids)
     * @param mixed  $selectedIds   selected record ids
     * @param string $requestModule module of the list
     *
     * @return string[]
     */
    public static function getSourceModules(bool $linkToModule, $selectedIds, string $requestModule): array
    {
        $sourceModules = [];

        if ($linkToModule) {
            foreach ($selectedIds as $id) {
                if ($id) {
                    $sourceModules[] = getSalesEntityType($id);
                }
            }
        } else {
            $sourceModules[] = $requestModule;
        }

        return $sourceModules;
    }

    /**
     * Reads the e-mail fields and the addresses of the selected records.
     *
     * @param string[] $sourceModules modules of the selected records
     * @param callable $getRecordIds  returns the selected record ids (asked only for a module with e-mail fields)
     *
     * @return array ['email_fields_info' => module => record id => e-mail => field model (null without
     *               a module), 'records_count',
     *               'recipient_pref_enabled' => bool, 'email_fields' => field models of the last module]
     */
    public static function getEmailFieldsInfo(array $sourceModules, callable $getRecordIds): array
    {
        $totalRecordCount = 0;
        // without a source module nothing is read — the dialog gets no fields at all
        $emailFieldsInfo = null;
        $emailFields = null;
        $recipientPrefModel = null;
        $recipientPrefs = null;
        $recordIds = null;

        foreach ($sourceModules as $sourceModule) {
            $emailFieldsInfo = [];
            $moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
            $recipientPrefModel = Vtiger_RecipientPreference_Model::getInstance($sourceModule);

            if ($recipientPrefModel) {
                $recipientPrefs = $recipientPrefModel->getPreferences();
            }

            $moduleEmailPrefs = $recipientPrefs[$moduleModel->getId()];
            $emailFields = $moduleModel->getFieldsByType('email');
            $accesibleEmailFields = [];

            foreach ($emailFields as $index => $emailField) {
                $fieldName = $emailField->getName();

                if ($emailField->isViewable()) {
                    if ($moduleEmailPrefs && in_array($emailField->getId(), $moduleEmailPrefs)) {
                        $emailField->set('isPreferred', true);
                    }

                    $accesibleEmailFields[$fieldName] = $emailField;
                }
            }

            $emailFields = $accesibleEmailFields;

            if (php7_count($emailFields) > 0) {
                $recordIds = $getRecordIds();
                global $current_user;
                $baseTableId = $moduleModel->get('basetableid');
                $queryGen = new QueryGenerator($moduleModel->getName(), $current_user);
                $selectFields = array_keys($emailFields);
                array_push($selectFields, 'id');
                $queryGen->setFields($selectFields);
                $query = $queryGen->getQuery();
                $query = $query . ' AND crmid IN (' . generateQuestionMarks($recordIds) . ')';
                $emailOptout = $moduleModel->getField('emailoptout');

                if ($emailOptout) {
                    $query .= ' AND ' . $emailOptout->get('column') . ' = 0';
                }

                $db = PearDatabase::getInstance();
                $result = $db->pquery($query, $recordIds);
                $num_rows = $db->num_rows($result);

                if ($num_rows > 0) {
                    for ($i = 0; $i < $num_rows; $i++) {
                        $emailFieldsList = [];

                        foreach ($emailFields as $emailField) {
                            $emailValue = $db->query_result($result, $i, $emailField->get('column'));

                            if (!empty($emailValue)) {
                                $emailFieldsList[$emailValue] = $emailField;
                            }
                        }

                        if (!empty($emailFieldsList)) {
                            $recordId = $db->query_result($result, $i, $baseTableId);
                            $emailFieldsInfo[$moduleModel->getName()][$recordId] = $emailFieldsList;
                        }
                    }
                }
            }

            $totalRecordCount = $totalRecordCount + php7_count($recordIds);
        }

        return [
            'email_fields_info' => $emailFieldsInfo,
            'records_count' => $totalRecordCount,
            'recipient_pref_enabled' => $recipientPrefModel && !empty($recipientPrefs),
            'email_fields' => $emailFields,
        ];
    }
}
