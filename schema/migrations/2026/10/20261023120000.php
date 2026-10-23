<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20261023120000')) {
    class Migration_20261023120000 extends AbstractMigrations
    {
        /**
         * Creates df_template_rel (Core_TemplateRelation_Model: relations of templates — EMAIL
         * Maker → PDF Maker now, more modules later). An installation that ran the first version
         * of the EMAIL Maker PDF templates has vtiger_emakertemplates_pdftemplates: its rows move
         * to df_template_rel (module EMAILMaker, related_module PDFMaker, same order) and the old
         * table is dropped. Idempotent.
         *
         * @param string $strFileName
         *
         * @throws Exception
         */
        public function migrate(string $strFileName): void
        {
            (new Core_TemplateRelation_Model())->createTables();
            EMAILMaker_PDFTemplate_Model::moveLegacyLinks();
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
