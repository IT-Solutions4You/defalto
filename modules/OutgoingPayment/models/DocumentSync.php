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
 * Keeps documents and outgoing payments (df_outgoingpayment) consistent; logic in Core_PaymentDocumentSync_Model.
 */
class OutgoingPayment_DocumentSync_Model extends Core_PaymentDocumentSync_Model
{
    protected string $table = OutgoingPayment_Config_Helper::TABLE;
    protected string $tableId = OutgoingPayment_Config_Helper::TABLE_INDEX;
}
