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
 * Keeps documents and incoming payments (df_incomingpayment) consistent; logic in Core_PaymentDocumentSync_Model.
 */
class IncomingPayment_DocumentSync_Model extends Core_PaymentDocumentSync_Model
{
    protected string $table = IncomingPayment_Config_Helper::TABLE;
    protected string $tableId = IncomingPayment_Config_Helper::TABLE_INDEX;
}
