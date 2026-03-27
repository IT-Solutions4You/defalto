<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20260327180559')) {
    class Migration_20260327180559 extends AbstractMigrations
    {
        /**
         * Adds the $maxUploadSizeLimit variable to config.inc.php if not already present.
         * This variable defines the ceiling (in MB) that an administrator is allowed to set
         * for $upload_maxsize via the Settings UI.
         *
         * @param string $strFileName
         * @throws Exception
         */
        public function migrate(string $strFileName): void
        {
            $configFile = 'config.inc.php';

            if (!file_exists($configFile)) {
                return;
            }

            $content = file_get_contents($configFile);

            if ($content === false) {
                return;
            }

            // Skip if already defined
            if (strpos($content, '$maxUploadSizeLimit') !== false) {
                return;
            }

            $newLines = "\n// maximum value (in MB) that administrator is allowed to set for \$upload_maxsize via Settings UI\n"
                . "// can only be changed by manually editing this file\n"
                . "\$maxUploadSizeLimit = 50;//MB\n";

            // Insert after the $upload_maxsize line
            $content = preg_replace(
                '/(\$upload_maxsize\s*=[^;]+;[^\n]*)(\n)/',
                '$1$2' . $newLines,
                $content,
                1
            );

            file_put_contents($configFile, $content);
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
