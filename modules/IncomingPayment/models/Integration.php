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
 * Integration of incoming payments per document module (df_incomingpayment_integration); logic in
 * Core_PaymentIntegration_Model.
 */
class IncomingPayment_Integration_Model extends Core_PaymentIntegration_Model
{
    protected string $table = IncomingPayment_Config_Helper::TABLE_INTEGRATION;
    protected string $tableId = 'id';
}
