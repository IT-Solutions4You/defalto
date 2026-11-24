<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
class Core_DateOnly_Helper
{
    public static function convertToDBFormat(string $value): ?string
    {
        $value = trim($value);

        if ($value === '' || $value === '0000-00-00') {
            return '';
        }

        $format = 'yyyy-mm-dd';

        if (!preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $value)) {
            $format = (string)Users_Record_Model::getCurrentUserModel()->get('date_format');
        }

        $pattern = strtr(preg_quote($format, '/'), ['yyyy' => '\d{4}', 'mm' => '\d{1,2}', 'dd' => '\d{1,2}']);

        if ($pattern === '' || !preg_match('/^' . $pattern . '$/', $value)) {
            return null;
        }

        // Convert validation copies only; CRMEntity converts the submitted value when saving.
        $date = DateTimeField::__convertToDBFormat($value, $format);

        if (!preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $date, $parts)
            || !checkdate((int)$parts[2], (int)$parts[3], (int)$parts[1])) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', (int)$parts[1], (int)$parts[2], (int)$parts[3]);
    }
}
