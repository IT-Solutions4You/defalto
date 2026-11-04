<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

trait InventoryItem_DetailView_Trait
{
    /**
     * Returns the url of the link which creates the target record from the displayed record.
     * The url opens the item review modal when the conversion is configured so.
     *
     * @param string $targetModule
     * @param string $createUrl create view url of the target record
     *
     * @return string|false false when the conversion action is disabled
     * @throws Exception
     */
    protected function getConversionLinkUrl(string $targetModule, string $createUrl): string|false
    {
        $recordModel = $this->getRecord();

        return InventoryItem_Conversion_Model::getLinkUrl($recordModel->getModuleName(), $targetModule, (int)$recordModel->getId(), $createUrl);
    }
}
