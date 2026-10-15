# Import

Import creates or updates records from an uploaded file using your field mapping. Review successful, failed and skipped rows before retrying.

Imports containing up to 5,000 records start immediately and continue in batches while the import window remains open. Larger imports are scheduled for background processing by cron.

The imported-record list supports source-module filters. AND/OR conditions apply together with the restriction to your imported records; the count uses the same filters.
