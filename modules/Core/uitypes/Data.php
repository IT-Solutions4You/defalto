<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_Data_UIType extends Vtiger_Base_UIType {

    public function getTemplateName()
    {
        preg_match('/\w+[_](\w+)[_]\w+/', get_class($this), $matches);

        return 'uitypes/' . $matches[1] . '.tpl';
    }

    /**
     * @param mixed $fieldValue
     * @return mixed
     */
    public function getRequestValue(mixed $fieldValue): mixed
    {
        if (is_array($fieldValue)) {
            $fieldValue = array_filter($fieldValue);

            return json_encode($fieldValue, true);
        }

        return null;
    }

    public function getSelectedValue(mixed $fieldValue): array
    {
        if (!empty($fieldValue)) {
            $fieldValue = decode_html($fieldValue);

            return json_decode($fieldValue, true);
        }

        return [];
    }
}