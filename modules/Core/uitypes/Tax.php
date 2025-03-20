<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_Tax_UIType extends Vtiger_Base_UIType
{
    /**
     * @param mixed $recordId
     * @return array
     * @throws AppException
     */
    public function getTaxes(mixed $recordId): array
    {
        $recordId = (int)$recordId;

        return Core_TaxRecord_Model::getActiveTaxes($recordId);
    }

    /**
     * @param mixed $recordId
     * @return array
     * @throws AppException
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
        return true;
    }

    /**
     * @throws AppException
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        /** @var Core_Tax_Model $tax */
        $taxes = $this->getDetailTaxes($record);
        $data = [];

        foreach ($taxes as $tax) {
            if ($tax->isActive()) {
                $data[] = sprintf('%s: %s%s', $tax->getName(), $tax->getTax(), '%');
            }
        }

        return implode(', ', $data);
    }
}