<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Vtiger_Country_UIType extends Vtiger_Base_UIType
{
    /**
     * @param $value
     * @param $record
     * @param $recordInstance
     * @return mixed
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        $moduleName = $this->getModuleName();
        /** @var Vtiger_Country_Model $countryModel */
        $countryModel = Vtiger_Country_Model::getInstance($moduleName);

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
        /** @var Vtiger_Country_Model $countryModel */
        $countryModel = Vtiger_Country_Model::getInstance($moduleName);
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
     * @return string
     */
    public static function transformDisplayValue($value): string
    {
        return (new self())->getDisplayValue($value);
    }
}