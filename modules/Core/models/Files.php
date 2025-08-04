<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Files_Model extends Vtiger_Base_Model
{
    /**
     * @var string
     */
    public string $moduleName;

    /**
     * @param string $fileName
     *
     * @return void
     */
    public function deleteLayoutFile(string $fileName): void
    {
        $fileName = ltrim($fileName, '/');
        $file = $this->getLayoutDirectory() . $fileName;

        if (file_exists($file) && is_writable($file)) {
            unlink($file);
        }
    }

    /**
     * @param string $fileName
     *
     * @return void
     */
    public function deleteModuleFile(string $fileName): void
    {
        $fileName = ltrim($fileName, '/');
        $file = $this->getModuleDirectory() . $fileName;

        if (file_exists($file) && is_writable($file)) {
            unlink($file);
        }
    }

    /**
     * @throws Exception
     */
    public static function getInstance(string $module)
    {
        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Files', $module);

        if (class_exists($modelClassName)) {
            $instance = new $modelClassName();
        } else {
            $instance = new self();
        }

        $instance->moduleName = $module;

        return $instance;
    }

    /**
     * @return string
     */
    public function getLayoutDirectory(): string
    {
        return sprintf('%s/layouts/%s/modules/%s/', $this->getSystemDirectory(), Vtiger_Viewer::getLayoutName(), $this->moduleName);
    }

    /**
     * @return string
     */
    public function getModuleDirectory(): string
    {
        return sprintf('%s/modules/%s/', $this->getSystemDirectory(), $this->moduleName);
    }

    /**
     * @return string
     */
    public function getSystemDirectory(): string
    {
        global $root_directory;

        return rtrim($root_directory, '/');
    }
}