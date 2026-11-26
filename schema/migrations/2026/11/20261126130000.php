<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20261126130000')) {
    class Migration_20261126130000 extends AbstractMigrations
    {
        /**
         * Invoice Type values of documents created from proforma invoices. The field had only "Invoice", so saving a
         * tax document or a final invoice failed on the picklist validation (Vtiger_Util_Helper::validatePicklistFieldValue()).
         * A module update does not add picklist values of an existing field, so they are added here; existing values stay.
         *
         * @param string $fileName Migration file name
         * @return void
         * @throws Exception
         */
        public function migrate(string $fileName): void
        {
            $moduleInstance = Vtiger_Module::getInstance('Invoice');
            $field = $moduleInstance ? Vtiger_Field::getInstance('invoice_type', $moduleInstance) : false;

            if (!$field) {
                return;
            }

            $db = PearDatabase::getInstance();
            $existing = [];
            $result = $db->pquery('SELECT invoice_type FROM vtiger_invoice_type', []);

            while ($row = $db->fetchByAssoc($result, -1, false)) {
                $existing[] = $row['invoice_type'];
            }

            $missing = array_values(array_diff(['Tax Document', 'Final Invoice'], $existing));

            if ($missing) {
                $field->setPicklistValues($missing);
            }

            Core_Install_Model::logSuccess('Invoice Type values added: ' . implode(', ', $missing));
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
