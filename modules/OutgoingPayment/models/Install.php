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
 * Installation of outgoing payments. Fields, tables and registrations are shared by the payment modules
 * (Core_PaymentInstall_Model); a new module has no earlier installations to migrate.
 */
class OutgoingPayment_Install_Model extends Core_PaymentInstall_Model
{
    /**
     * Nothing to migrate: the module was created with the current fields and statuses.
     *
     * @return void
     */
    public function migrate(): void
    {
    }
}
