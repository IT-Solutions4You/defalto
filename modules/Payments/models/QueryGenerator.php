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
 * Query generator the list framework creates for Payments (Vtiger_ListView_Model::getQueryGeneratorForModule()).
 * Payments has no table or webservice entity, so it is built on the primary payment module. It keeps the chosen
 * filter and the list columns; Payments_ListView_Model builds the real list from one query per source.
 */
class Payments_QueryGenerator_Model extends Core_QueryGenerator_Model
{
    protected int $paymentsViewId = 0;

    /**
     * Builds the generator on the primary payment module instead of Payments.
     *
     * @param string $module Requested module (Payments)
     * @param object $user   Current user
     */
    public function __construct($module, $user)
    {
        parent::__construct(Payments_Sources_Helper::getPrimaryModule(), $user);
    }

    /**
     * Loads the default filter of Payments, not the one of the primary payment module.
     * CustomView is created without a module, because with one it loads webservice metadata Payments does not have.
     *
     * @return void
     */
    public function initForDefaultCustomView()
    {
        $customView = new CustomView();
        $this->initForCustomViewById($customView->getViewId(Payments_Sources_Helper::MODULE));
    }

    /**
     * Loads the columns and conditions of a filter and remembers its id for the source queries.
     *
     * @param int|string $viewId Filter id
     * @return void
     */
    public function initForCustomViewById($viewId)
    {
        $this->paymentsViewId = (int)$viewId;
        parent::initForCustomViewById($viewId);
    }

    /**
     * Returns the id of the Payments filter the list uses.
     *
     * @return int
     */
    public function getPaymentsViewId(): int
    {
        return $this->paymentsViewId;
    }
}
