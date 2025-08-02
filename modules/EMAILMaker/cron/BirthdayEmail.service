<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

global $VTIGER_BULK_SAVE_MODE;
$previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
$VTIGER_BULK_SAVE_MODE = true;

require_once 'vendorCheck.php';
require_once 'vendor/autoload.php';
require_once('includes/runtime/Controller.php');
require_once('includes/runtime/BaseModel.php');
require_once('includes/runtime/Globals.php');
require_once('include/utils/utils.php');
require_once('include/logging.php');
require_once('include/database/PearDatabase.php');
require_once('modules/EMAILMaker/EMAILMaker.php');
require_once('modules/EMAILMaker/models/EMAILMaker.php');
require_once('modules/EMAILMaker/models/EMAILContent.php');

(new EMAILMaker_BirthdayEmail_Model())->sendEmails();

$VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;