<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Tax_UIType extends Vtiger_Base_UIType
{
    /**
     * @param mixed $recordId
     *
     * @return array
     * @throws Exception
     */
    public function getTaxes(mixed $recordId): array
    {
        $recordId = (int)$recordId;

        return Core_TaxRecord_Model::getActiveTaxes($recordId);
    }

    /**
     * @param mixed $recordId
     *
     * @return array
     * @throws Exception
     */
    public function getDetailTaxes(mixed $recordId): array
    {
        $recordId = (int)$recordId;

        return Core_TaxRecord_Model::getActiveTaxesForRecord($recordId);
    }

    /**
     * Function to get the Template name for the current UI Type Object
     * @return <String> - Template Name
     */
    public function getTemplateName()
    {
        return 'uitypes/Tax.tpl';
    }

    public function getDetailViewTemplateName()
    {
        return 'uitypes/TaxDetailView.tpl';
    }

    /**
     * @return bool
     */
    public function isLabelTemplate(): bool
    {
        return false;
    }

    /**
     * @throws Exception
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        /** @var Core_Tax_Model $tax */
        $taxes = $this->getDetailTaxes($record);
        $data = [];

        foreach ($taxes as $tax) {
            $data[] = sprintf('%s: %s%s', $tax->getName(), $tax->getTax(), '%');
        }

        return implode(', ', $data);
    }

    /**
     * @param int|string $value
     *
     * @return string
     * @throws Exception
     */
    public static function transformDisplayValue($value)
    {
        if (!empty($value)) {
            return (new self())->getDisplayValue(null, $value);
        }

        return '';
    }
}