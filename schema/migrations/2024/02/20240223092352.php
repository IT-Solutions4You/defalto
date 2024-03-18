<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!class_exists('Migration_20240223092352')) {
    class Migration_20240223092352 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            $this->db->setDebug(true);

            $checkActivityTableRes = $this->db->pquery('SHOW TABLES LIKE "vtiger_activity"');

            if (!$this->db->num_rows($checkActivityTableRes)) {
                return;
            }

            $existingEmailsRes = $this->db->pquery('SELECT crmid FROM vtiger_crmentity WHERE setype = ? AND deleted = 0', ['Emails']);

            if ($this->db->num_rows($existingEmailsRes)) {
                $its4youEmailsInsert = 'INSERT INTO its4you_emails (its4you_emails_id, from_email, to_email, cc_email, bcc_email, subject, email_flag, body) 
                SELECT vtiger_crmentity.crmid, from_email, to_email, cc_email, bcc_email, vtiger_activity.subject, email_flag, vtiger_crmentity.description
                FROM vtiger_crmentity
                    INNER JOIN vtiger_emaildetails ON vtiger_emaildetails.emailid = vtiger_crmentity.crmid
                    INNER JOIN vtiger_activity ON vtiger_activity.activityid = vtiger_crmentity.crmid
                WHERE vtiger_crmentity.deleted = 0
                    AND vtiger_crmentity.setype= ?
                ';
                $its4youEmailsInsertParams = ['Emails'];
                $this->db->pquery($its4youEmailsInsert, $its4youEmailsInsertParams);

                $its4youEmailsAccessInsert = 'INSERT INTO its4you_emails_access
                SELECT vtiger_email_access.*
                FROM vtiger_email_access
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_email_access.mailid';
                $this->db->query($its4youEmailsAccessInsert);

                $relationsSql = 'SELECT vtiger_seactivityrel.*, vtiger_crmentity.setype 
                FROM vtiger_seactivityrel
                    INNER JOIN its4you_emails ON its4you_emails.its4you_emails_id = vtiger_seactivityrel.activityid
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_seactivityrel.crmid AND vtiger_crmentity.deleted = 0
                ';
                $relationRes = $this->db->query($relationsSql);

                while ($relationRow = $this->db->fetchByAssoc($relationRes)) {
                    switch ($relationRow['setype']) {
                        case 'Accounts':
                            $columnName = 'account_id';
                            break;
                        case 'Contacts':
                            $columnName = 'contact_id';
                            break;
                        case 'Leads':
                            $columnName = 'lead_id';
                            break;
                        case 'Vendors':
                            $columnName = 'vendor_id';
                            break;
                        default:
                            $columnName = 'parent_id';
                            break;
                    }

                    $updateEmailsSql = 'UPDATE its4you_emails SET ' . $columnName . ' = ? WHERE its4you_emails_id = ?';
                    $updateEmailsParams = [$relationRow['crmid'], $relationRow['activityid']];
                    $this->db->pquery($updateEmailsSql, $updateEmailsParams);
                }

                $updateEmailsContactsSql = 'UPDATE its4you_emails
                INNER JOIN vtiger_cntactivityrel ON vtiger_cntactivityrel.activityid = its4you_emails.its4you_emails_id
                SET its4you_emails.contact_id = vtiger_cntactivityrel.contactid';
                $this->db->pquery($updateEmailsContactsSql);

                $updateEmailsUsersSql = 'UPDATE its4you_emails
                INNER JOIN vtiger_salesmanactivityrel ON vtiger_salesmanactivityrel.activityid = its4you_emails.its4you_emails_id
                SET its4you_emails.contact_id = vtiger_salesmanactivityrel.smid';
                $this->db->pquery($updateEmailsUsersSql);

                $updateAttachmentsSql = 'UPDATE vtiger_crmentity SET setype = ? WHERE setype = ?';
                $this->db->pquery($updateAttachmentsSql, ['ITS4YouEmails Attachment', 'Emails Attachment']);

                $updateEmailsSql = 'UPDATE vtiger_crmentity SET setype = ? WHERE setype = ?';
                $this->db->pquery($updateEmailsSql, ['ITS4YouEmails', 'Emails']);
            }

            // Remove modules EmailTemplates and Emails
            $this->db->pquery('DELETE FROM vtiger_profile2tab WHERE tabid IN (SELECT tabid FROM vtiger_tab WHERE name IN (?, ?))', ['EmailTemplates', 'Emails']);
            $this->db->pquery('DELETE FROM vtiger_tab WHERE name IN (?, ?)', ['EmailTemplates', 'Emails']);

            // Delete the related lists
            $this->db->pquery('DELETE FROM vtiger_relatedlists WHERE label IN (?, ?)', ['EmailTemplates', 'Emails']);

            // Remove from webservices
            $this->db->pquery('DELETE FROM vtiger_ws_entity WHERE name IN (?, ?)', ['EmailTemplates', 'Emails']);

            // Regenerate tabdata.php and parent_tabdata.php
            require_once('vtlib/Vtiger/Deprecated.php');
            Vtiger_Deprecated::createModuleMetaFile();
            Vtiger_Deprecated::createModuleGroupMetaFile();

            $tableNames = [
                'vtiger_cntactivityrel',
                'vtiger_salesmanactivityrel',
                'vtiger_seactivityrel',
                'vtiger_activityproductrel',
                'vtiger_recurringevents',
                'vtiger_activity_recurring_info',
                'vtiger_activity_reminder',
                'vtiger_activity_reminder_popup',
                'vtiger_activitycf',
                'vtiger_activity',
            ];

            foreach ($tableNames as $tableName) {
                $this->db->pquery('DROP TABLE IF EXISTS ' . $tableName);
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}