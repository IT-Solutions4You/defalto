# SMS Notifier

SMS Notifier sends text messages through the active SMS provider and stores each message and its delivery status in Defalto CRM. Workflows can send SMS with the SMS workflow task.

For ClickATell delivery updates, copy the callback URL shown in the provider configuration (Settings) into ClickATell and make sure ClickATell can reach `modules/SMSNotifier/callbacks/ClickATellNew.php`. The secret in the URL must match the configured provider secret before a message status is updated.

If a delivery status remains unchanged, verify that SMSNotifier and the ClickATell provider are active, the callback URL is publicly reachable, and the URL registered with ClickATell matches the one in the provider configuration.
