<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'vtlib/Vtiger/Cron.php';

if (!class_exists('Migration_20260624120059')) {
    class Migration_20260624120059 extends AbstractMigrations
    {
        /**
         * Set up the Country settings module:
         *  - create the local postal-code dataset tables (GeoNames) used for
         *    address (city / PSČ / state) auto-completion,
         *  - re-point the existing "Countries" settings menu item to the new
         *    Settings:Country module (List view).
         *
         * @param string $strFileName
         * @throws Exception
         */
        public function migrate(string $strFileName): void
        {
            $dataModel = new Settings_Country_Data_Model();
            $dataModel->createTables();

            $countryModel = Core_Country_Model::getInstance();
            // Seed its4you_countries on existing installs so it becomes the live
            // source of truth (idempotent — only missing ISO codes are inserted).
            $countryModel->createTables();
            // Re-point the existing 'Countries' menu row (matched by name) to the new linkto.
            $countryModel->createLinks();

            // Register the postal-code update cron. Disabled by default: it should be
            // enabled once the download mirror is configured, and so it does not fire a
            // full worldwide import on the next cron tick right after the upgrade.
            // Recommended cadence ~ every 3 months (90 days).
            $exists = $this->db->pquery('SELECT 1 FROM vtiger_cron_task WHERE name = ?', ['PostalCodesUpdate']);

            if (!$this->db->num_rows($exists)) {
                Vtiger_Cron::register(
                    'PostalCodesUpdate',
                    'cron/modules/Settings/Country/PostalCodes.php',
                    90 * 24 * 3600,
                    'Settings',
                    Vtiger_Cron::$STATUS_DISABLED,
                    0,
                    'Updates the local postal-code database from GeoNames. Recommended frequency: every 3 months.'
                );
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
