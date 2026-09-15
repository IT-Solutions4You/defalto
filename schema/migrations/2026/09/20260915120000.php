<?php
/**
 * This file is part of Defalto, licensed under the GNU AGPL v3 License.
 * Copyright (c) IT-Solutions4You s.r.o.
 */

class Migration_20260915120000 extends AbstractMigrations
{
    public function migrate(string $fileName): void
    {
        Install_InitSchema_Model::registerTrashWorkflowTask();
    }
}
