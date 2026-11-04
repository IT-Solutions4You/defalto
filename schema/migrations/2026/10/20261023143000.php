<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20261023143000')) {
    class Migration_20261023143000 extends AbstractMigrations
    {
        /**
         * @throws Exception
         */
        public function migrate(string $fileName): void
        {
            Settings_Vtiger_MenuItem_Model::installLink('LBL_PRODUCTBLOCKTPL');
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
