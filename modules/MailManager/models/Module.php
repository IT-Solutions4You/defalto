<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class MailManager_Module_Model extends Vtiger_Module_Model
{
    /**
     * Returns true when the Pro edition of Mail Manager is available. Pro is a Membership
     * benefit; the license state is owned and refreshed by the Installer module, this
     * method only reads the stored state and never calls the license server.
     *
     * @return bool
     * @throws Exception
     */
    public static function isProEnabled(): bool
    {
        return Installer_License_Model::isMembershipActive();
    }

    /**
     * Adds the Mail Manager Pro settings link while Pro is enabled, and the link to delete
     * stored Pro mail data on installations that used Pro before (also without Membership,
     * decision D8). Basic installations that never used Pro get the parent links only.
     *
     * @return array
     * @throws Exception
     */
    public function getSettingLinks(): array
    {
        $links = parent::getSettingLinks();

        if (self::isProEnabled()) {
            $links[] = [
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_MAIL_MANAGER_PRO', $this->getName()),
                'linkurl' => 'index.php?module=MailManager&parent=Settings&view=Index',
                'linkicon' => '',
            ];
        } elseif (MailManager_Install_Model::getInstance('module.postupdate', $this->getName())->isProSchemaInstalled()) {
            $links[] = [
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_PRO_STORED_DATA', $this->getName()),
                'linkurl' => 'index.php?module=MailManager&parent=Settings&view=PurgeData',
                'linkicon' => '',
            ];
        }

        return $links;
    }
}
