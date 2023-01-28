<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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