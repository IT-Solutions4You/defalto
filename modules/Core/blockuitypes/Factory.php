<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_Factory_BlockUIType
{
    /**
     * Create the BlockUIType object from Vtiger Block Model
     *
     * @param Vtiger_Block_Model $blockModel
     *
     * @return self Core_Base_BlockUIType or BlockUIType specific object instance
     * @throws Exception
     */
    public static function getInstanceFromBlock(Vtiger_Block_Model $blockModel): self
    {
        $blockUiType = $blockModel->get('blockuitype');
        $blockUiTypeName = Core_BlockUiType_Model::getNameForUIType($blockUiType);
        $moduleInstance = $blockModel->getModuleInstance();
        $moduleName = $moduleInstance->getName();
        $moduleSpecificFilePath = Vtiger_Loader::getComponentClassName('BlockUiType', $blockUiTypeName, $moduleName);

        if (class_exists($moduleSpecificFilePath)) {
            $instance = new $moduleSpecificFilePath();
        } else {
            $instance = new Core_Base_BlockUIType();
        }

        $instance->set('block', $blockModel);

        return $instance;
    }
}