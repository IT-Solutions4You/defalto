<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_Number_UIType extends Vtiger_Base_UIType {

    /**
     * @return string
     */
    public function getTemplateName() {
        return 'uitypes/Number.tpl';
    }

    /**
     * @param mixed $value
     * @param int $record
     * @param object $recordInstance
     * @return string
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        $value = strip_tags($value);

        if (empty($value)) {
            return '0';
        }

        $currentUser = Users_Record_Model::getCurrentUserModel();
        $value = CurrencyField::convertToUserFormat($value, $currentUser, true);

        if ($currentUser->isEmpty('truncate_trailing_zeros')) {
            $value = rtrim(rtrim($value, '0'), $currentUser->getDecimalSeparator());
        }

        return $value;
    }


    /**
     * @param mixed $value
     * @return float
     */
    public function getEditViewDisplayValue($value)
    {
        if (empty($value)) {
            return '';
        }

        $value = CurrencyField::convertToUserFormatForEdit($value, null, true, false);

        return decimalFormat($value);
    }

    /**
     * @param int|string $value
     * @return string
     */
    public static function transformDisplayValue($value): string
    {
        return (new self())->getDisplayValue($value);
    }
}