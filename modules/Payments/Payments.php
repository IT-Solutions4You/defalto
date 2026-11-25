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
 * Payments has no records, fields or tables. It only lists the records of the payment direction modules.
 */
class Payments extends CRMExtension
{
    public string $moduleVersion = '1.0';
    public string $moduleName = 'Payments';
    public string $moduleLabel = 'Payments';
    public string $parentName = 'SALES';
    public $list_fields = [];
    public $list_fields_name = [];
    public $def_basicsearch_col = 'payment_name';

    /**
     * Delegates vtlib lifecycle events (enable, disable, update, uninstall) to the install model.
     *
     * @param string $moduleName Module name
     * @param string $eventType  vtlib event type, e.g. module.enabled
     * @return void
     * @throws Exception
     */
    public function vtlib_handler($moduleName, $eventType): void
    {
        Core_Install_Model::getInstance((string)$eventType, (string)$moduleName)->install();
    }
}
