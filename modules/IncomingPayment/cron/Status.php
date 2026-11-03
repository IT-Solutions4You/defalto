<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

// Runs without bulk save mode on purpose: the payment handlers must run so the status, the history
// and the paid amounts of the documents are updated.
IncomingPayment_DocumentSync_Model::getInstance()->updateOpenStatuses();
