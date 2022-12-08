<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ITS4YouCalendar_Field_Model extends Vtiger_Field_Model
{
    /**
     * @return bool
     */
    public function isEmptyPicklistOptionAllowed(): bool
    {
        if ('calendar_visibility' === $this->getFieldName()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getFieldDataType(): string
    {
        if (30 === (int)$this->get('uitype')) {
            return 'reminder';
        }

        return parent::getFieldDataType();
    }

    /**
     * @param $value
     * @return string
     */
    public function getEditViewDisplayValue($value): string
    {
        if (empty($value)) {
            $fieldName = $this->getName();

            switch ($fieldName) {
                case 'datetime_start':
                    $value = date('Y-m-d H:i:s');
                    break;
                case'datetime_end':
                    $currentUser = Users_Record_Model::getCurrentUserModel();
                    $minutes = $currentUser->get('callduration');
                    $value = date('Y-m-d H:i:s', strtotime("+$minutes minutes"));
                    break;
            }
        }

        return parent::getEditViewDisplayValue($value);
    }
}