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
 * Settings of incoming payments (df_incomingpayment_setting); logic in Core_PaymentSetting_Model.
 */
class IncomingPayment_Setting_Model extends Core_PaymentSetting_Model
{
    protected string $table = IncomingPayment_Config_Helper::TABLE_SETTING;
    protected string $tableId = 'id';
}
