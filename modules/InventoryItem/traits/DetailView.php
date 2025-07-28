<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o <info@its4you.sk>
 *
 * This file is licensed under the GNU AGPL v3 License.
 * For the full copyright and license information, please view the LICENSE-AGPLv3.txt
 * file that was distributed with this source code.
 */

trait InventoryItem_DetailView_Trait
{
    /**
     * @inheritDoc
     */
    public function getWidgets()
    {
        $inventoryItemWidget = [
            'linktype'  => 'DETAILVIEWWIDGET',
            'linklabel' => 'Items',
            'linkurl'   => 'module=InventoryItem&view=ItemsWidget&for_module=' . $this->getModuleName() . '&for_record=' . $this->getRecord()->getId(),
        ];
        $counter = 0;
        $widgets = [];

        foreach (parent::getWidgets() as $widget) {
            $widgets[] = $widget;

            if (!$counter) {
                $widgets[] = Vtiger_Link_Model::getInstanceFromValues($inventoryItemWidget);
                $widgets[] = $this->getPlaceholderWidgetInfo();
            }

            $counter++;
        }

        return $widgets;
    }

}