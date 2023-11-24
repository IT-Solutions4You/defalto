<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

include_once 'libraries/ToAscii/ToAscii.php';
require_once 'include/utils/utils.php';
require_once 'include/logging.php';
require_once 'config.php';

global $VTIGER_BULK_SAVE_MODE;
$previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
$VTIGER_BULK_SAVE_MODE = true;

Appointments_Reminder_Model::runCron();

$VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;