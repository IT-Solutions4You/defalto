<?php

include_once 'libraries/ToAscii/ToAscii.php';
require_once 'include/utils/utils.php';
require_once 'include/logging.php';
require_once 'config.php';

Appointments_Reminder_Model::runCron();