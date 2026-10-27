# Import

Import creates or updates records from an uploaded file using your field mapping. Review successful, failed and skipped rows before retrying.

Imports of up to 5,000 records start immediately and continue in batches while the import window remains open. Larger imports are scheduled for background processing by cron.

The imported-record list shows only the records you imported successfully and supports the source module's filters. Filter conditions, including AND/OR groups, apply on top of that restriction. The total record count uses only the quick search, not advanced conditions.

Saved mappings can be reused in later imports. A mapping remembers whether it was saved for files with or without a header row. Mappings for files with a header row fill in columns by column name, and others by column position. Columns that were not mapped when the mapping was saved stay unmapped.
