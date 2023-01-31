<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (PHP_SAPI != 'cli') {
    die('File execution only allowed from CLI');
}

require_once('config.php');
include_once 'includes/main/WebUI.php';

set_include_path($root_directory);

require_once('include/Migrations/Migrations.php');

$migrationObj = new Migrations($argv);