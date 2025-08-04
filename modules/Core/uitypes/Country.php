<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Country_UIType extends Vtiger_Base_UIType
{
    /**
     * @param $value
     * @param $record
     * @param $recordInstance
     *
     * @return mixed
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        $moduleName = $this->getModuleName();
        /** @var Core_Country_Model $countryModel */
        $countryModel = Core_Country_Model::getInstance($moduleName);

        if (!empty($value)) {
            $country = $countryModel->getCountry($value);

            return $country['name'];
        }

        return $value;
    }

    public function getEditViewValue($value, $record = false, $recordInstance = false)
    {
        return $value;
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return $this->get('field') ? $this->get('field')->getModuleName() : 'Vtiger';
    }

    /**
     * @return array
     */
    public function getPicklistValues(): array
    {
        $moduleName = $this->getModuleName();
        /** @var Core_Country_Model $countryModel */
        $countryModel = Core_Country_Model::getInstance($moduleName);
        $countries = $countryModel->getCountries();
        $values = [];

        foreach ($countries as $country) {
            if (1 === (int)$country['is_active']) {
                $values[$country['code']] = $country['name'];
            }
        }

        return $values;
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return 'uitypes/Country.tpl';
    }

    public function getListSearchTemplateName()
    {
        return 'uitypes/CountryFieldSearchView.tpl';
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