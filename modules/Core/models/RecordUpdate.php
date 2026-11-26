<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
class Core_RecordUpdate_Model extends Vtiger_Base_Model
{
    public static function convertToUserFormat(CRMEntity $focus, object $meta): void
    {
        // Existing CRMEntity updates expect dates and currencies in the acting user's
        // edit format. Formatting itself must not become a tracked field change.
        $focus->column_fields->pauseTracking();
        $focus->column_fields = DataTransform::sanitizeDateFieldsForInsert($focus->column_fields, $meta);
        $focus->column_fields = DataTransform::sanitizeCurrencyFieldsForInsert($focus->column_fields, $meta);
        $focus->column_fields->resumeTracking();
    }
}
