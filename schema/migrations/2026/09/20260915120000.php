<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Migration_20260915120000 extends AbstractMigrations
{
    public function migrate(string $fileName): void
    {
        Install_InitSchema_Model::registerTrashWorkflowTask();
    }
}
