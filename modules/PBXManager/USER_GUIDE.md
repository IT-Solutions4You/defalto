# PBX Manager

PBX Manager connects an Asterisk telephone exchange with Defalto CRM. Call events create or update call records so users can see the caller, direction, status, duration, responsible user, and recording link when supplied by the exchange. Callers are matched to CRM records by phone number.

Configure the exchange in **Settings, Integration, PBXManager** (Asterisk Server Details: Vtiger Asterisk App URL, Vtiger Secret Key, Outbound Context, Outbound Trunk). Each user who makes or receives calls needs a **CRM Phone Extension** on their user record. The exchange must send events to `modules/PBXManager/callbacks/PBXManager.php` and include the matching secret key as the callback signature. Requests with an invalid signature are not processed.

If calls do not appear, verify that the PBX can reach the callback URL and that both systems use the same secret key.
