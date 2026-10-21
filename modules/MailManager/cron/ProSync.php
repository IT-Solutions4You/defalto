<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/*
 * Mail Manager Pro background synchronization (registered as MailManagerProSync by
 * MailManager_Install_Model::initializePro()). This file runs in the global scope of the
 * cron runner, so it must not define variables such as $log, $adb or $current_user – the
 * long names below avoid replacing the core logger. Without an active Membership (stored
 * license state only) or with the module disabled the run ends immediately.
 */
if (!vtlib_isModuleActive('MailManager') || !MailManager_Module_Model::isProEnabled()) {
    return;
}

// A run that dies (fatal error, exit) never returns to vtigercron; the shutdown handler
// records the reason and frees the scheduler task.
$mailManagerProCronEnded = false;
register_shutdown_function(function () use (&$mailManagerProCronEnded) {
    if ($mailManagerProCronEnded) {
        return;
    }

    $error = error_get_last();
    MailManager_ProSync_Model::finishCronRun($error ? $error['message'] . ' in ' . $error['file'] . ':' . $error['line'] : 'The run ended without finishing');
});

MailManager_ProSync_Model::traceCron('run starts, PHP ' . PHP_VERSION);
MailManager_ProSync_Model::releaseStaleCronTask();

try {
    $mailManagerProCronLines = MailManager_ProSync_Model::run(MailManager_ProSync_Model::RUN_BUDGET);
    MailManager_ProSync_Model::finishCronRun();
} catch (Throwable $mailManagerProCronError) {
    // vtigercron catches Exception only – a PHP Error would stop the whole scheduler run
    $mailManagerProCronLines = ['ERROR: ' . $mailManagerProCronError->getMessage() . ' in ' . $mailManagerProCronError->getFile() . ':' . $mailManagerProCronError->getLine()];
    MailManager_ProSync_Model::finishCronRun($mailManagerProCronLines[0]);
}

$mailManagerProCronEnded = true;
MailManager_ProSync_Model::traceCron('run ends');

echo $mailManagerProCronLines ? implode("\n", $mailManagerProCronLines) . "\n" : "MailManagerProSync: nothing to sync\n";
