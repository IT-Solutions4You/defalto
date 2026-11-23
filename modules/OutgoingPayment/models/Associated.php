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
 * Payment to document associations of outgoing payments (df_outgoingpayment_associated); logic in
 * Core_PaymentAssociated_Model.
 */
class OutgoingPayment_Associated_Model extends Core_PaymentAssociated_Model
{
    protected string $table = OutgoingPayment_Config_Helper::TABLE_ASSOCIATED;
    protected string $tableId = 'id';
}
