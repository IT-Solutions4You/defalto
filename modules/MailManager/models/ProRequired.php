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
 * The page a Mail Manager Pro page opens without an active Membership: what Pro brings,
 * the Pro offer, the Installer and — for the administrator — the stored Pro mail.
 */
class MailManager_ProRequired_Model
{
    /** The page in Settings (administrator) */
    public const SETTINGS_URL = 'index.php?module=MailManager&parent=Settings&view=ProRequired';

    /** The page in the module (a user's own Pro pages) */
    public const MODULE_URL = 'index.php?module=MailManager&view=ProRequired&app=TOOLS';

    /** Deleting the stored Pro mail works without Membership */
    public const PURGE_URL = 'index.php?module=MailManager&parent=Settings&view=PurgeData';

    /**
     * Everything the page shows.
     *
     * @param bool $isAdmin the administrator also sees the Installer and the stored Pro mail
     *
     * @return array template variables
     */
    public static function getViewData(bool $isAdmin): array
    {
        $installer = $isAdmin ? Vtiger_Module_Model::getInstance('Installer') : null;
        $hasProSchema = $isAdmin && MailManager_Install_Model::getInstance('module.postupdate', 'MailManager')->isProSchemaInstalled();

        return [
            'PREMIUM_ITEMS' => self::getPremiumItems(),
            'INSTALLER_URL' => $installer ? $installer->getDefaultUrl() : '',
            'STORED_MESSAGES' => $hasProSchema ? (new MailManager_ProPurge_Model())->countStoredMessages() : 0,
            'PURGE_URL' => self::PURGE_URL,
        ];
    }

    /**
     * What Pro brings — the texts of the Pro offer (LBL_PREMIUM_MODAL_ITEM_n).
     *
     * @return string[]
     */
    public static function getPremiumItems(): array
    {
        $items = [];

        for ($i = 1; ; $i++) {
            $key = 'LBL_PREMIUM_MODAL_ITEM_' . $i;
            $text = vtranslate($key, 'MailManager');

            if ($text === $key) {
                break;
            }

            $items[] = $text;
        }

        return $items;
    }
}
