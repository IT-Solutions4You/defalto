<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20261016190000')) {
    class Migration_20261016190000 extends AbstractMigrations
    {
        /**
         * Mail Manager Pro I17: adds df_mailmanager_aioutage (outages of an AI provider),
         * df_mailmanager_aiagent.backup_provider / backup_model and df_mailmanager_ailog
         * provider / is_backup on installations that started Mail Manager Pro, without
         * waiting for a system update. The schema lives in
         * MailManager_Install_Model; installProTables()
         * is idempotent. A Basic installation (no Pro schema) gets nothing.
         *
         * @param string $strFileName
         *
         * @throws Exception
         */
        public function migrate(string $strFileName): void
        {
            $install = MailManager_Install_Model::getInstance('module.postupdate', 'MailManager');

            if (!$install->isProSchemaInstalled()) {
                return;
            }

            $install->installProTables();
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
