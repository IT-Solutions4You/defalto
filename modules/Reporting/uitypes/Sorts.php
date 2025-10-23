<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_Sorts_UIType extends Core_Data_UIType
{
    /**
     * @param string $value
     *
     * @return bool
     */
    public function isValueASC(string $value): bool
    {
        return 'ASC' === explode(' ', $value)[1];
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public function isValueDESC(string $value): bool
    {
        return 'DESC' === explode(' ', $value)[1];
    }

    public function getFieldName($value)
    {
        return $this->getFieldInfo($value)['field'];
    }

    public function getFieldInfo($value): array
    {
        [$field, $order] = array_pad(explode(' ', $value), 2, null);

        return ['field' => $field, 'order' => $order];
    }

    public function getOrderType($value)
    {
        return $this->getFieldInfo($value)['order'];
    }

    public function getLabelOptions(string $moduleName, array $labels = []): array
    {
        $options = [];

        if (empty($moduleName)) {
            return $options;
        }

        return array_merge(Reporting_Fields_Model::getFieldLabels($moduleName), $labels);
    }
}