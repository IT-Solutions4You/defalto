<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

trait MigrationsTrait
{
    /**
     * @param $message
     *
     * @return void
     */
    public function makeAborting($message = null): void
    {
        $this->showMsg($message);
        $this->showMsg('ABORTING!');
        exit;
    }

    /**
     * @param $message
     *
     * @return void
     */
    public function showMsg($message = null): void
    {
        if (!is_null($message)) {
            echo '____>>> ' . $message;
        }

        echo PHP_EOL;
    }
}