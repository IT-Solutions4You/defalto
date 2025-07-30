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