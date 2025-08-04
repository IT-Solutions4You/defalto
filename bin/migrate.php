<?php
/**
 * This file is part of the Defalto CRM Software.
 *
 * (c) IT-Solutions4You s.r.o <info@its4you.sk>
 *
 * This file is licensed under the GNU AGPL v3 License.
 * For the full copyright and license information, please view the LICENSE-AGPLv3.txt
 * file that was distributed with this source code.
 */

if (PHP_SAPI != 'cli') {
    die('File execution only allowed from CLI');
}

require_once('config.php');
require_once 'vendor/autoload.php';
include_once 'includes/main/WebUI.php';

global $current_user;

if (!$current_user) {
    $current_user = Users::getActiveAdminUser();
}

set_include_path($root_directory);
require_once('include/Migrations/Migrations.php');

$migrationObj = new Migrations();
$migrationObj->setArguments($argv);
$migrationObj->run();