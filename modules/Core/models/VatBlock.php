<?php
/*
 *
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_VatBlock_Model extends Core_InventoryItemsBlock_Model
{
    /**
     * @param string $moduleName
     *
     * @return self
     * @throws Exception
     */
    public static function getInstance(string $moduleName): self
    {
        $instance = self::getCleanInstance($moduleName);
        $instance->set('module_name', $moduleName);

        return $instance;
    }

    /**
     * @param string $moduleName
     *
     * @return self
     * @throws Exception
     */
    public static function getCleanInstance(string $moduleName): self
    {
        $className = Vtiger_Loader::getComponentClassName('Model', 'VatBlock', $moduleName);

        if (class_exists($className)) {
            $instance = new $className();
        } else {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * @param Vtiger_Record_Model $recordModel
     * @param string $content
     * @param string $templateModule
     * @return string
     * @throws Exception
     */
    public static function replaceAll(Vtiger_Record_Model $recordModel, string $content, string $templateModule = 'EMAILMaker'): string
    {
        $moduleName = $recordModel->getModuleName();
        $blocks = [
            '#VATBLOCK_START#',
            '#VATBLOCK_END#',
        ];

        $content = self::replaceBlockTags($content, $blocks);

        $relatedBlock = self::getInstance($moduleName);
        $relatedBlock->setSourceRecord($recordModel);
        $relatedBlock->setSourceRecordId($recordModel->getId());
        $relatedBlock->setTemplateModule($templateModule);

        return $relatedBlock->replaceVatBlocks($content);
    }

    public function replaceRecords(string $content): string
    {
        $this->retrieveNumberUsers();

        $relatedModule = $this->getRelatedModule();

        if (!$relatedModule) {
            return $content;
        }

        $relatedModuleName = $relatedModule->getName();
        $this->retrieveDB();
        $query = $this->getQuery();

        $result = $this->getDB()->pquery($query);

        /** @var InventoryItem_Record_Model $relatedRecord */
        $relatedRecord = Vtiger_Record_Model::getCleanInstance($relatedModuleName);
        $vatBlocks = [];

        while ($row = $this->getDB()->fetchByAssoc($result)) {
            $relatedRecord->setData($row);

            $itemId = $row['id'];
            $taxRecord = Core_TaxRecord_Model::getInstance($itemId);
            $taxModel = null;

            foreach ($taxRecord->getTaxes() as $tax) {
                /** @var Core_Tax_Model $tax */
                if($tax->isActiveForRecord()) {
                    $taxModel = $tax;
                    break;
                }
            }

            if(empty($taxModel)) {
                continue;
            }

            $taxId = $taxModel->getId();
            $taxPercent = (float)$row['tax'];
            $taxKey = $taxId . '_' . $taxPercent;
            $taxAmount = (float)$row['tax_amount'] + (float)$vatBlocks[$taxKey]['vat'];
            $nettoAmount = (float)$row['price_after_overall_discount'] + (float)$vatBlocks[$taxKey]['netto'];

            $vatBlocks[$taxKey] = [
                'id' => $taxId,
                'label' => $taxModel->getLabel(),
                'value' => $taxPercent . ' %',
                'percent' => $taxPercent,
                'netto' => $nettoAmount,
                'vat' => $taxAmount,
            ];
        }

        $newContent = '';

        foreach ($vatBlocks as $vatBlock) {
            $vatContent = $content;

            foreach ($vatBlock as $key => $value) {
                $vatContent = str_replace(strtoupper('$VATBLOCK_' . $key . '$'), $value, $vatContent);
            }

            $newContent .= $vatContent;
        }

        return $newContent;
    }

    public function getRelatedFields(): array
    {
        return [
            'id',
            'price_after_overall_discount',
            'tax',
            'tax_amount',
        ];
    }

    public function replaceLabels($content): string
    {
        return $content;
    }

    /**
     * @throws Exception
     */
    public function replaceVatBlocks(string $content): string
    {
        [$beforeContent, $blockContent] = explode('#VATBLOCK_START#', $content, 2);
        [$blockContent, $afterContent] = explode('#VATBLOCK_END#', $blockContent, 2);

        $blockContent = $this->replaceRecords($blockContent);
        $blockContent = $this->replaceLabels($blockContent);

        $content = $beforeContent . $blockContent . $afterContent;

        if (str_contains($content, '#VATBLOCK_START#')) {
            $this->replaceVatBlocks($content);
        }

        return $content;
    }
}
