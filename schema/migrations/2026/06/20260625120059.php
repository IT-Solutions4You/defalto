<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20260625120059')) {
    class Migration_20260625120059 extends AbstractMigrations
    {
        /**
         * Create the per-module address field mapping table (df_address_field_map),
         * which records — per module — which address-block field is the PSČ (zip),
         * which is the city, and optionally the state/country to fill. Used by the
         * address autocomplete feature.
         *
         * @param string $strFileName
         * @throws Exception
         */
        public function migrate(string $strFileName): void
        {
            $addressMapModel = new Settings_Country_AddressMap_Model();
            $addressMapModel->createTables();

            // Seed best-effort default mappings for the standard address-bearing
            // modules. Idempotent: modules already configured are left untouched.
            Settings_Country_AddressMap_Model::setDefaultMappingForAll();
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
