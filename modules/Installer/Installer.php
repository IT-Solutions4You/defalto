<?php

Installer_ZipArchive_Model::$skipFolders = ['user_privileges', 'manifest', 'update', 'icons', 'installer'];
Installer_ZipArchive_Model::$skipFiles = ['config.inc.php', 'composer.lock', 'index.php', 'update.php', 'install.php', 'parent_tabdata.php', 'tabdata.php'];

class Installer extends CRMExtension
{
    public string $moduleLabel = 'Installer';
    public string $moduleName = 'Installer';
    public string $parentName = 'Tools';
}