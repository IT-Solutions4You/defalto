# Customer Portal

Customer Portal provides permitted customers with access to CRM records, profile images, documents, and comment attachments. Files continue to download when attachment storage is configured outside the public web directory. Access remains controlled by the portal API and CRM permissions.

Customers can download only documents related to records available in their portal. Comment attachments are available only from public comments on records that the customer can access; private comments and files belonging to other customers remain unavailable.

Customers can edit only records that are available to their portal contact or organization. Attempts to update another customer's ticket, quote, asset, or other supported record are rejected.

Record lists and exports support equality filters joined with `AND`. Text values containing apostrophes are handled normally. Invalid filter structures, unsafe values, and unsupported condition operators are rejected without exposing records outside the signed-in customer's permitted contact or account relationships.
