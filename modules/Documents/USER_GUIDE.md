# Documents

Documents stores files uploaded by users and makes them available from document records and related CRM records. Documents can be organized into folders, marked active or inactive, previewed when their type is supported, and downloaded according to user permissions.

Document lists can be sorted by available document fields in ascending or descending order. Invalid or unavailable sort fields are rejected to keep list and record navigation safe.

## File storage

Administrators can set `$storage_directory` in `config.inc.php` or `config_override.php` to choose where newly uploaded attachment files, including Documents, are stored. When the setting is missing or empty, the application uses `storage/` in the application directory.

An absolute directory outside the public web directory is recommended. The PHP process must be able to create folders and write files there. The configured directory affects new uploads only; existing documents continue to use their previously stored locations.

Document files are grouped automatically into year, month, and week folders. Include the configured directory in the system backup even when it is kept separately from the application files.
