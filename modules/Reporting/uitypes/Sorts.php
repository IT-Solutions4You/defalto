<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Reporting_Sorts_UIType extends Core_Data_UIType
{
    /**
     * @param string $value
     * @return bool
     */
    public function isValueASC(string $value): bool
    {
        return 'ASC' === explode(' ', $value)[1];
    }

    /**
     * @param string $value
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
        [$field, $order] = explode(' ', $value);

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