<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!class_exists('Migration_20240301093200')) {
    class Migration_20240301093200 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         * @throws AppException
         */
        public function migrate(string $strFileName): void
        {
            EMAILMaker_Install_Model::getInstance('module.postinstall', 'EMAILMaker')->installTables();
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}