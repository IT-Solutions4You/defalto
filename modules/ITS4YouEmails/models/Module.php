<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPMailer\PHPMailer\PHPMailer;

class ITS4YouEmails_Module_Model extends Vtiger_Module_Model
{
    public static $mobileIcon = 'mail';
    public static $phpMailerLibraryPath = 'vendor/phpmailer/phpmailer/src/PHPMailer.php';

    public static function isPHPMailerInstalled()
    {
        return is_file(self::$phpMailerLibraryPath);
    }

    public static function isSendMailConfigured()
    {
        global $ITS4YouEmails_Mailer, $Emails_Mailer;

        if (self::isPHPMailerInstalled()) {

            $mailer = new PHPMailer();
            $mailer->isSMTP();

            $Emails_Mailer = $mailer->Mailer;

            if ('smtp' !== $Emails_Mailer && $ITS4YouEmails_Mailer !== $Emails_Mailer) {
                return false;
            }
        }

        return true;
    }

    public function isQuickCreateSupported()
    {
        return false;
    }

    public function getSettingLinks()
    {
        $settingsLinks = parent::getSettingLinks();

        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if ($currentUserModel->isAdminUser()) {
            $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => 'LBL_INTEGRATION',
                'linkurl' => 'index.php?module=ITS4YouEmails&parent=Settings&view=Index',
            );
        }

        return $settingsLinks;
    }

    public function getModuleBasicLinks()
    {
        return [];
    }

    public function getDatabaseTables()
    {
        return [
            'its4you_emails',
            'its4you_emailscf',
            'vtiger_its4you_email_no',
            'vtiger_its4you_email_no_seq',
            'vtiger_email_flag',
            'vtiger_email_flag_seq',
        ];
    }

    public function getPicklistFields()
    {
        return [
            'email_flag',
        ];
    }

    public function isStarredEnabled()
    {
        return false;
    }

    public function getModuleIcon($height = '')
    {
        return sprintf('<i style="font-size: %s" class="fa-solid fa-envelope" title=""></i>', $height);
    }

    /**
     * Function to get emails related modules
     * @return <Array> - list of modules
     */
    public function getEmailRelatedModules()
    {
        $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        $relatedModules = vtws_listtypes(['email'], Users_Record_Model::getCurrentUserModel());
        $relatedModules = $relatedModules['types'];

        foreach ($relatedModules as $moduleName) {
            if ($moduleName === 'Users') {
                continue;
            }

            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

            if (($userPrivilegesModel->isAdminUser() || $userPrivilegesModel->hasGlobalReadPermission() || $userPrivilegesModel->hasModulePermission($moduleModel->getId())) && !$moduleModel->restrictToListInComposeEmailPopup()) {
                $emailRelatedModules[] = $moduleName;
            }
        }

        $emailRelatedModules[] = 'Users';

        return $emailRelatedModules;
    }

    /**
     * Retrieves list of entries from Contacts, Accounts and Leads with the option "Email opt out" checked
     *
     * @return array
     */
    public function getEmailOptOutRecordIds(): array
    {
        $db = PearDatabase::getInstance();
        $emailOptOutIds = [];

        $contactResult = $db->pquery(
            'SELECT crmid FROM vtiger_crmentity INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted = ? AND vtiger_contactdetails.emailoptout = ?',
            ['0', '1']
        );

        while ($contactRow = $db->fetchByAssoc($contactResult)) {
            $emailOptOutIds[] = $contactRow['crmid'];
        }

        $accountResult = $db->pquery(
            'SELECT crmid FROM vtiger_crmentity INNER JOIN vtiger_account ON vtiger_account.accountid = vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted = ? AND vtiger_account.emailoptout = ?',
            ['0', '1']
        );

        while ($accountRow = $db->fetchByAssoc($accountResult)) {
            $emailOptOutIds[] = $accountRow['crmid'];
        }

        $leadResult = $db->pquery(
            'SELECT crmid FROM vtiger_crmentity INNER JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted = ? AND vtiger_leaddetails.emailoptout = ?',
            ['0', '1']
        );

        while ($leadRow = $db->fetchByAssoc($leadResult)) {
            $emailOptOutIds[] = $leadRow['crmid'];
        }

        return $emailOptOutIds;
    }
}