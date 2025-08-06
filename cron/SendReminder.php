<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

include_once 'libraries/ToAscii/ToAscii.php';
require_once 'include/utils/utils.php';
require_once 'include/logging.php';
require_once 'config.php';

Appointments_Reminder_Model::runCron();