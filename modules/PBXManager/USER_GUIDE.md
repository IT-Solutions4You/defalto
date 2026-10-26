# PBX Manager

PBX Manager connects a configured telephone exchange with Defalto CRM. Incoming callback events create or update call records so users can see the caller, direction, status, duration, responsible user, and recording link when supplied by the exchange.

Configure the PBX connector and its shared secret in the PBX settings. The exchange must send events to `modules/PBXManager/callbacks/PBXManager.php` and include the matching callback signature. Requests with an invalid signature are not processed.

If calls do not appear, verify that the PBX can reach the callback URL, that the connector is active, and that both systems use the same secret.
