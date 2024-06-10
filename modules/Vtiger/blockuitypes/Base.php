<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Vtiger_Base_BlockUIType extends Vtiger_Base_Model
{
    /**
     * Returns the Template name for the current Block UI Type Object
     *
     * @return string
     */
    public function getTemplateName(): string
    {
        return 'blockuitypes/Base.tpl';
    }

    /**
     * Create the BlockUIType object from Vtiger Block Model
     *
     * @param Vtiger_Block_Model $blockModel
     *
     * @return Vtiger_Base_Blockuitype or BlockUIType specific object instance
     */
    public static function getInstanceFromBlock(Vtiger_Block_Model $blockModel): self
    {
        $blockUiType = $blockModel->get('blockuitype');
        $blockUiTypeName = Vtiger_BlockUiType_Model::getNameForUIType($blockUiType);
        $moduleInstance = $blockModel->getModuleInstance();
        $moduleName = $moduleInstance->getName();

        $moduleSpecificUiTypeClassName = $moduleName . '_' . $blockUiTypeName . '_BlockUIType';
        $uiTypeClassName = 'Vtiger_' . $blockUiTypeName . '_BlockUIType';
        $fallBackClassName = 'Vtiger_Base_BlockUIType';

        $moduleSpecificFileName = 'modules.' . $moduleName . '.blockuitypes.' . $blockUiTypeName;
        $uiTypeClassFileName = 'modules.Vtiger.blockuitypes.' . $blockUiTypeName;

        $moduleSpecificFilePath = Vtiger_Loader::resolveNameToPath($moduleSpecificFileName);
        $completeFilePath = Vtiger_Loader::resolveNameToPath($uiTypeClassFileName);

        if (file_exists($moduleSpecificFilePath)) {
            $instance = new $moduleSpecificUiTypeClassName();
        } elseif (file_exists($completeFilePath)) {
            $instance = new $uiTypeClassName();
        } else {
            $instance = new $fallBackClassName();
        }

        $instance->set('block', $blockModel);

        return $instance;
    }
}