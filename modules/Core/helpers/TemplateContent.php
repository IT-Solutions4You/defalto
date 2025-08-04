<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_TemplateContent_Helper extends Vtiger_Base_Model
{
    /**
     * @param $finalDetails
     *
     * @return mixed
     */
    public function getTotalWithVat($finalDetails)
    {
        if ('individual' === $finalDetails['taxtype']) {
            return $finalDetails['hdnSubTotal'];
        }

        return $finalDetails['preTaxTotal'] + $finalDetails['tax_totalamount'];
    }

    /**
     * @throws Exception
     */
    public static function getCompanyFields(int $assignedUserId, $language): array
    {
        global $site_URL;
        $fields = [];

        if (getTabId('ITS4YouMultiCompany') && vtlib_isModuleActive('ITS4YouMultiCompany') && class_exists('ITS4YouMultiCompany_Record_Model')) {
            $CompanyDetailsRecord_Model = ITS4YouMultiCompany_Record_Model::getCompanyInstance($assignedUserId);
            $CompanyDetails_Model = $CompanyDetailsRecord_Model->getModule();
            $companyDetailsData = $CompanyDetailsRecord_Model->getData();
            $isMultiCompany = true;
        } else {
            $CompanyDetails_Model = Settings_Vtiger_CompanyDetails_Model::getInstance();
            $companyDetailsData = $CompanyDetails_Model->getData();
            $isMultiCompany = false;
        }

        $fields['%COMPANY_FAX%'] = '';
        $fields['$COMPANY_FAX$'] = '';
        $fields['%company-fax%'] = '';
        $fields['$company-fax$'] = '';

        $companyDetailsFields = $CompanyDetails_Model->getFields();
        $convertColumns = [
            'organizationname' => 'name',
            'companyname'      => 'name',
            'street'           => 'address',
            'code'             => 'zip',
        ];

        foreach ($companyDetailsFields as $fieldName => $fieldData) {
            $value = $companyDetailsData[$fieldName];
            $coll = $convertColumns[$fieldName] ?? $fieldName;
            $label = $isMultiCompany ? Vtiger_Language_Handler::getTranslatedString(
                $fieldData->get('label'),
                'ITS4YouMultiCompany',
                $language
            ) : Vtiger_Language_Handler::getTranslatedString($fieldName, 'Settings:Vtiger', $language);

            if ('country_id' === $coll) {
                $value = $value ? Core_Country_UIType::transformDisplayValue($value) : '';

                $fields['$COMPANY_COUNTRY$'] = $value;
                $fields['%COMPANY_COUNTRY$%'] = $label;

                $fields['$company-country$'] = $value;
                $fields['%company-country%'] = $label;
            } elseif ('logo' === $coll && !empty($companyDetailsData['logoname'])) {
                $value = '<img src="' . $site_URL . LOGO_PATH . $companyDetailsData['logoname'] . '">';
            } elseif (($coll == 'logo' || $coll == 'stamp') && $isMultiCompany && !empty($companyDetailsData[$coll])) {
                $value = self::getAttachmentImage($companyDetailsData[$coll], $site_URL);
            }

            $fields['$COMPANY_' . strtoupper($coll) . '$'] = $value;
            $fields['%COMPANY_' . strtoupper($coll) . '%'] = $label;

            $fields['$company-' . strtolower($coll) . '$'] = $value;
            $fields['%company-' . strtolower($coll) . '%'] = $label;
        }

        return $fields;
    }

    /**
     * @param int $id
     * @param string $site_url
     *
     * @return string
     * @throws Exception
     */
    public static function getAttachmentImage(int $id, string $site_url): string
    {
        if (empty($id)) {
            return '';
        }

        $db = PearDatabase::getInstance();
        $query = self::getAttachmentImageQuery();
        $result = $db->pquery($query, [$id]);

        if (!$db->num_rows($result)) {
            return '';
        }

        $row = $db->query_result_rowdata($result);

        if (empty($row['storedname'])) {
            $row['storedname'] = $row['name'];
        }

        $image_src = $row['path'] . $row['attachmentsid'] . "_" . $row['storedname'];

        return "<img src='" . $site_url . "/" . $image_src . "'/>";
    }

    /**
     * @return string
     */
    public static function getAttachmentImageQuery(): string
    {
        return 'SELECT vtiger_attachments.*
	            FROM vtiger_seattachmentsrel
	            INNER JOIN vtiger_attachments
	            ON vtiger_attachments.attachmentsid=vtiger_seattachmentsrel.attachmentsid
	            INNER JOIN vtiger_crmentity
	            ON vtiger_attachments.attachmentsid=vtiger_crmentity.crmid
	            WHERE deleted=0 AND vtiger_attachments.attachmentsid=?';
    }
}