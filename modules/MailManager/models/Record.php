<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class MailManager_Record_Model extends Vtiger_Record_Model
{
    public function getDetailViewUrl()
    {
        return 'index.php?module=MailManager&view=List';
    }
}