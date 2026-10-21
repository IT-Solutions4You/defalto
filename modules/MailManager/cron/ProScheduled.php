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
 * Mail Manager Pro scheduled sending (cron task MailManagerProScheduled, I12): sends the
 * "Send later" mail that is due. Runs in the global scope of the cron runner — long
 * variable names only. Mail already scheduled goes out also without an active Membership
 * (MailManager_ProScheduled_Model::isSendingAllowed()); with the module disabled or without
 * the Pro schema the run ends immediately.
 */
if (!MailManager_ProScheduled_Model::isSendingAllowed()) {
    return;
}

try {
    $mailManagerProScheduledLines = MailManager_ProScheduled_Model::processDue();
} catch (Throwable $mailManagerProScheduledError) {
    // vtigercron catches Exception only – a PHP Error would stop the whole scheduler run
    $mailManagerProScheduledLines = ['ERROR: ' . $mailManagerProScheduledError->getMessage()];
}

echo $mailManagerProScheduledLines ? implode("\n", $mailManagerProScheduledLines) . "\n" : "MailManagerProScheduled: nothing due\n";
