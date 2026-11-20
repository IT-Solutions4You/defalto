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
 * Payment to document associations of incoming payments (df_incomingpayment_associated); logic in
 * Core_PaymentAssociated_Model.
 */
class IncomingPayment_Associated_Model extends Core_PaymentAssociated_Model
{
    protected string $table = IncomingPayment_Config_Helper::TABLE_ASSOCIATED;
    protected string $tableId = 'id';
}
