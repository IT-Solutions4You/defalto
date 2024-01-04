<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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

        if (is_file('modules/Emails/class.phpmailer.php')) {
            require_once 'modules/Emails/class.phpmailer.php';

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
            $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => 'LBL_MODULE_REQUIREMENTS',
                'linkurl' => 'index.php?module=ITS4YouInstaller&parent=Settings&view=Requirements&mode=Module&sourceModule=ITS4YouEmails',
            );
            $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => 'LBL_LICENSE',
                'linkurl' => 'index.php?module=ITS4YouInstaller&view=License&parent=Settings&sourceModule=ITS4YouEmails',
            );
            $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => 'LBL_UPGRADE',
                'linkurl' => 'index.php?module=ModuleManager&parent=Settings&view=ModuleImport&mode=importUserModuleStep1',
            );
            $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => 'LBL_UNINSTALL',
                'linkurl' => 'index.php?module=ITS4YouInstaller&view=Uninstall&parent=Settings&sourceModule=ITS4YouEmails',
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
}