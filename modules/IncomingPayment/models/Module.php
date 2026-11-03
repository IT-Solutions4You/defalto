<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class IncomingPayment_Module_Model extends Vtiger_Module_Model
{
    protected string $fontIcon = 'fa-solid fa-money-bill-trend-up';

    /**
     * Adds the integration settings page to the list view settings menu.
     *
     * @return array
     */
    public function getSettingLinks(): array
    {
        $settingsLinks = parent::getSettingLinks();
        $settingsLinks[] = [
            'linktype' => 'LISTVIEWSETTING',
            'linklabel' => 'LBL_INCOMING_PAYMENT_INTEGRATION',
            'linkurl' => 'index.php?module=IncomingPayment&parent=Settings&view=Index',
            'linkicon' => '',
        ];

        return $settingsLinks;
    }
}
