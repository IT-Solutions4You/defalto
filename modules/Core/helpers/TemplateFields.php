<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_TemplateFields_Helper extends Vtiger_Base_Model
{
    public string $labelModule = 'EMAILMaker';
    public array $allRelatedModules = [];
    public array $moduleFields = [];

    /**
     * @throws Exception
     */
    public function getInventoryItemsBlockFields($moduleName = ''): array
    {
        $block = Core_InventoryItemsBlock_Model::getInstance($moduleName);

        return [
            'PRODUCT_BLOC_TPL' => $block->getAllOptions(),
            'ARTICLE_STRINGS' => $block->getArticleOptions(),
            'SELECT_PRODUCT_FIELD' => $block->getVariableOptions(),
            'PRODUCTS_FIELDS' => $block->getProductVariableOptions(),
            'SERVICES_FIELDS' => $block->getServiceVariableOptions(),
        ];
    }


    public function retrieveSelectedModuleFieldByFieldName(&$fields, $fieldName): void
    {
        if ('currency_id' === $fieldName) {
            $fields['CURRENCYSYMBOL'] = vtranslate('LBL_CURRENCY_SYMBOL', $this->labelModule);
            $fields['CURRENCYCODE'] = vtranslate('LBL_CURRENCY_CODE', $this->labelModule);
        }
    }

    public function setModuleFields($module, $module_id, $skip_related = false)
    {
        if (isset($this->moduleFields[$module])) {
            return false;
        }

        $adb = PearDatabase::getInstance();

        if ($module == 'Users') {
            $sql1 = 'SELECT blockid, blocklabel FROM vtiger_blocks INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_blocks.tabid WHERE vtiger_tab.name = ? AND (blocklabel = ? OR blocklabel = ? ) ORDER BY sequence ASC';
            $params = ['Users', 'LBL_USERLOGIN_ROLE', 'LBL_ADDRESS_INFORMATION'];
        } else {
            $sql1 = 'SELECT blockid, blocklabel FROM vtiger_blocks WHERE tabid = ? ORDER BY sequence ASC';
            $params = [$module_id];
        }

        $res1 = $adb->pquery($sql1, $params);
        $block_info_arr = [];
        while ($row = $adb->fetch_array($res1)) {
            $sql2 = 'SELECT fieldid, uitype, columnname, fieldlabel FROM vtiger_field WHERE block= ? AND presence != ? ORDER BY sequence ASC';
            $res2 = $adb->pquery($sql2, [$row['blockid'], '1']);
            $num_rows2 = $adb->num_rows($res2);

            if ($num_rows2 > 0) {
                $field_id_array = [];

                while ($row2 = $adb->fetch_array($res2)) {
                    $field_id_array[] = $row2['fieldid'];
                    $tmpArr = [$row2['columnname'], vtranslate($row2['fieldlabel'], $module)];

                    if (!$skip_related) {
                        switch (intval($row2['uitype'])) {
                            case 73:
                            case 51:
                                $this->allRelatedModules[$module][] = array_merge($tmpArr, [vtranslate('Accounts', 'Accounts'), 'Accounts']);
                                break;
                            case 57:
                                $this->allRelatedModules[$module][] = array_merge($tmpArr, [vtranslate('Contacts', 'Contacts'), 'Contacts']);
                                break;
                            case 58:
                                $this->allRelatedModules[$module][] = array_merge($tmpArr, [vtranslate('Campaigns', 'Campaigns'), 'Campaigns']);
                                break;
                            case 59:
                                $this->allRelatedModules[$module][] = array_merge($tmpArr, [vtranslate('Products', 'Products'), 'Products']);
                                break;
                            case 81:
                            case 75:
                                $this->allRelatedModules[$module][] = array_merge($tmpArr, [vtranslate('Vendors', 'Vendors'), 'Vendors']);
                                break;
                            case 76:
                                $this->allRelatedModules[$module][] = array_merge($tmpArr, [vtranslate('Potentials', 'Potentials'), 'Potentials']);
                                break;
                            case 78:
                                $this->allRelatedModules[$module][] = array_merge($tmpArr, [vtranslate('Quotes', 'Quotes'), 'Quotes']);
                                break;
                            case 80:
                                $this->allRelatedModules[$module][] = array_merge($tmpArr, [vtranslate('SalesOrder', 'SalesOrder'), 'SalesOrder']);
                                break;
                            case 101:
                                $this->allRelatedModules[$module][] = array_merge($tmpArr, [vtranslate('Users', 'Users'), 'Users']);
                                $this->setModuleFields('Users', '', true);
                                break;
                            case 68:
                                $this->allRelatedModules[$module][] = array_merge($tmpArr, [vtranslate('Accounts', 'Accounts'), 'Accounts']);
                                $this->allRelatedModules[$module][] = array_merge($tmpArr, [vtranslate('Contacts', 'Contacts'), 'Contacts']);
                                break;
                            case 10:
                                $fmrs = $adb->pquery('SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid = ?', [$row2['fieldid']]);
                                while ($rm = $adb->fetch_array($fmrs)) {
                                    $this->allRelatedModules[$module][] = array_merge($tmpArr, [vtranslate($rm['relmodule'], $rm['relmodule']), $rm['relmodule']]);
                                }
                                break;
                        }
                    }
                }
                // ITS4YOU MaJu
                //$block_info_arr[$row['blocklabel']] = $field_id_array;
                if (!empty($block_info_arr[$row['blocklabel']])) {
                    foreach ($field_id_array as $field_id_array_value) {
                        $block_info_arr[$row['blocklabel']][] = $field_id_array_value;
                    }
                } else {
                    $block_info_arr[$row['blocklabel']] = $field_id_array;
                }
                // ITS4YOU-END
            }
        }

        $this->moduleFields[$module] = $block_info_arr;
    }

    public function getRelatedModules(string $module)
    {
        return $this->allRelatedModules[$module];
    }

    public function getModuleFields(string $module)
    {
        if (!isset($this->moduleFields[$module])) {
            $module_id = getTabid($module);
            $this->setModuleFields($module, $module_id);
        }

        return $this->moduleFields[$module];
    }

    /**
     * @param array $moduleIds
     * @return void
     */
    public function getAllModuleFields(array $moduleIds): void
    {
        foreach ($moduleIds as $module => $moduleId) {
            $this->setModuleFields($module, $moduleId);
        }
    }
}