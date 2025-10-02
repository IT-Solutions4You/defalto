<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_ZipArchive_Model extends ZipArchive
{
    public static array $skipFiles = ['config.inc.php', 'composer.lock', 'index.php', 'update.php', 'install.php', 'parent_tabdata.php', 'tabdata.php'];
    public static array $skipFolders = ['user_privileges', 'manifest', 'update', 'icons', 'installer'];

    public static array $errors = [];

    /**
     * @param string $destination
     * @param string $zipSubDir
     *
     * @return array
     */
    public function extractSubDirTo(string $destination, string $zipSubDir): array
    {
        // Prepare dirs
        $destination = str_replace(["/", "\\"], DIRECTORY_SEPARATOR, $destination);
        $zipSubDir = str_replace(["/", "\\"], "/", $zipSubDir);

        if (substr($destination, mb_strlen(DIRECTORY_SEPARATOR, 'UTF-8') * -1) != DIRECTORY_SEPARATOR) {
            $destination .= DIRECTORY_SEPARATOR;
        }

        if (!str_ends_with($zipSubDir, '/')) {
            $zipSubDir .= '/';
        }

        // Extract files
        for ($i = 0; $i < $this->numFiles; $i++) {
            $filename = $this->getNameIndex($i);

            if (substr($filename, 0, mb_strlen($zipSubDir, 'UTF-8')) == $zipSubDir) {
                $relativePath = substr($filename, mb_strlen($zipSubDir, 'UTF-8'));
                $relativePath = str_replace(["/", "\\"], DIRECTORY_SEPARATOR, $relativePath);

                if (mb_strlen($relativePath, 'UTF-8') > 0) {
                    if (str_ends_with($filename, '/'))  // Directory
                    {
                        // New dir
                        if (!is_dir($destination . $relativePath)) {
                            if (!mkdir($destination . $relativePath, 0755, true)) {
                                self::$errors[$i] = $filename;
                            }
                        }
                    } else {
                        if (dirname($relativePath) != '.') {
                            if (!is_dir($destination . dirname($relativePath))) {
                                // New dir (for file)
                                mkdir($destination . dirname($relativePath), 0755, true);
                            }
                        }

                        $skip = false;

                        foreach (self::$skipFolders as $skipFolder) {
                            if (str_starts_with($relativePath, $skipFolder . DIRECTORY_SEPARATOR)) {
                                $skip = true;
                            }
                        }

                        if (!$skip) {
                            foreach (self::$skipFiles as $skipFile) {
                                if (str_ends_with($filename, $skipFile)) {
                                    $skip = true;
                                }
                            }
                        }

                        if ($skip) {
                            Core_Install_Model::logError('Skip: ' . $relativePath);
                        } elseif (file_put_contents($destination . $relativePath, $this->getFromIndex($i)) === false) {
                            self::$errors[$i] = $filename;
                        }
                    }
                }
            }
        }

        return self::$errors;
    }
}