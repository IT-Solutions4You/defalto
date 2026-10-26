# SMS Notifier

SMS Notifier sends text messages through the active SMS provider and stores each message and its delivery status in Defalto CRM.

For ClickATell delivery updates, enable the callback URL in the provider configuration and make sure ClickATell can reach `modules/SMSNotifier/callbacks/ClickATellNew.php`. The callback secret in the request must match the configured provider secret before a message status is updated.

If a delivery status remains unchanged, verify that SMSNotifier and the ClickATell provider are active, the callback URL is publicly reachable, and the configured secret matches the URL registered with ClickATell.
