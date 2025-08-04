<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Number_UIType extends Vtiger_Base_UIType
{
    /**
     * @return string
     */
    public function getTemplateName()
    {
        return 'uitypes/Number.tpl';
    }

    /**
     * @param mixed  $value
     * @param int    $record
     * @param object $recordInstance
     *
     * @return string
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        $value = strip_tags($value);

        if ('' == $value) {
            return '';
        }

        global $number_user;

        $number_user = $number_user ?: Users_Record_Model::getCurrentUserModel();
        $value = CurrencyField::convertToUserFormat($value, $number_user, true);

        if ($number_user->isEmpty('truncate_trailing_zeros')) {
            $value = rtrim(rtrim($value, '0'), $number_user->getDecimalSeparator());
        }

        return $value;
    }

    /**
     * @param int|string $value
     *
     * @return string
     */
    public static function transformDisplayValue($value): string
    {
        return (new self())->getDisplayValue($value);
    }
}