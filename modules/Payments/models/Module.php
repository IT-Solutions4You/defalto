<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Payments has no fields of its own. Its blocks and fields are those of the payment modules, so the filter editor,
 * the column choice and the list search offer the fields every payment module shares.
 */
class Payments_Module_Model extends Vtiger_Module_Model
{
    protected string $fontIcon = 'fa-solid fa-money-bill';

    /**
     * Payments has no records, so it has no quick create form.
     *
     * @return bool
     */
    public function isQuickCreateSupported()
    {
        return false;
    }

    /**
     * Returns the blocks of the primary payment module.
     * The field names are shared by all payment modules, so one module's blocks describe the list columns.
     * Without an installed payment module (Payments installed first) there are no blocks.
     *
     * @return array<string, Vtiger_Block_Model>
     */
    public function getBlocks()
    {
        if (empty($this->blocks)) {
            $primaryModule = $this->getPrimarySourceModule();
            $this->blocks = $primaryModule ? $primaryModule->getBlocks() : [];
        }

        return $this->blocks;
    }

    /**
     * Returns the fields of all payment modules keyed by field name; a field shared by several modules appears once.
     *
     * @param Vtiger_Block_Model|false $blockInstance Unused, kept for the parent contract
     * @return array<string, Vtiger_Field_Model>
     */
    public function getFields($blockInstance = false)
    {
        if (empty($this->fields)) {
            $fields = [];

            foreach (Payments_Sources_Helper::getSourceModules() as $moduleName) {
                $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

                if (!$moduleModel) {
                    continue;
                }

                $fields += $moduleModel->getFields();
            }

            $this->fields = $fields;
        }

        return $this->fields;
    }

    /**
     * Returns a field of the payment modules by its name.
     *
     * @param string $fieldName Field name
     * @return Vtiger_Field_Model|false
     */
    public function getField($fieldName)
    {
        return $this->getFields()[$fieldName] ?? false;
    }

    /**
     * Returns the payment module that stands in for Payments where one module is required.
     *
     * @return Vtiger_Module_Model|null Null while no payment module is installed
     */
    public function getPrimarySourceModule(): ?Vtiger_Module_Model
    {
        $primaryModule = Payments_Sources_Helper::getPrimaryModule();
        $moduleModel = '' !== $primaryModule ? Vtiger_Module_Model::getInstance($primaryModule) : false;

        return $moduleModel ?: null;
    }

    /**
     * Payments only lists records; create, edit, delete and import happen in the payment modules.
     *
     * @param string $actionName Action name, e.g. index, DetailView, CreateView
     * @return bool
     */
    public function isPermitted($actionName)
    {
        if (in_array($actionName, ['CreateView', 'EditView', 'Delete', 'Import', 'DuplicatesHandling'], true)) {
            return false;
        }

        return parent::isPermitted($actionName);
    }
}
