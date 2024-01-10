<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'modules/com_vtiger_workflow/VTTaskManager.inc';

if (!class_exists('Migration_20240109133200')) {
    class Migration_20240109133200 extends AbstractMigrations
    {
        /**
         * @param string $fileName
         * @throws AppException
         */
        public function migrate(string $fileName): void
        {
            PDFMaker_Install_Model::getInstance('module.postinstall', 'PDFMaker')->installModule();
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}