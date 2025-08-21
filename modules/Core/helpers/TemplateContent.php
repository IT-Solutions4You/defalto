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
    public static string $content = '';
    public static $thousands_separator;
    public static $decimal_point;
    public static $decimals;
    public static $recordModel;
    public static $pagebreak;
    /**
     * @var array|mixed
     */
    public static array $rep;

    /**
     * @param $finalDetails
     *
     * @return mixed
     */
    public function getTotalWithVat($finalDetails)
    {
        if ('individual' === $finalDetails['taxtype']) {
            return $finalDetails['subtotal'];
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

    /**
     * @param string $name
     * @param bool|null $lowerCase
     * @return string
     */
    public function getVariable(string $name, bool $lowerCase = false): string
    {
        $name = $lowerCase ? strtolower($name) : strtoupper($name);

        return '$' . $name . '$';
    }

    /**
     * @param string $name
     * @param bool $lowerCase
     * @return string
     */
    public function getVariableLabel(string $name, bool $lowerCase = false): string
    {
        $name = $lowerCase ? strtolower($name) : strtoupper($name);

        return '%' . $name . '%';
    }

    /**
     * @throws Exception
     */
    protected function convertRelatedBlocks(): void
    {
        if (!str_contains(self::$content, '#RELATED_BLOCK_')) {
            return;
        }

        Core_RelatedBlock_Model::$numberUserConfig = Core_RelatedBlock_Model::$currencyUserConfig = [
            'currency_grouping_separator' => self::$thousands_separator,
            'currency_decimal_separator'  => self::$decimal_point,
            'truncate_trailing_zeros'     => false,
            'no_of_currency_decimals'     => self::$decimals,
        ];

        self::$content = Core_RelatedBlock_Model::replaceAll(self::$recordModel, self::$content, $this->getTemplateModule());
    }

    public function getTemplateModule()
    {
        return explode('_', get_class($this))[0];
    }

    /**
     * @throws Exception
     */
    protected function convertInventoryBlocks(): void
    {
        if (!str_contains(self::$content, '#INVENTORY_BLOCK_')) {
            return;
        }

        Core_InventoryItemsBlock_Model::$numberUserConfig = Core_InventoryItemsBlock_Model::$currencyUserConfig = [
            'currency_grouping_separator' => self::$thousands_separator,
            'currency_decimal_separator'  => self::$decimal_point,
            'truncate_trailing_zeros'     => false,
            'no_of_currency_decimals'     => self::$decimals,
        ];

        self::$content = Core_InventoryItemsBlock_Model::replaceAll(self::$recordModel, self::$content, $this->getTemplateModule());
    }

    public function retrieveRecordModel($recordId): void
    {
        if (!self::$recordModel && !empty($recordId) && isRecordExists($recordId)) {
            self::$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
        }
    }

    /**
     * @param string $type
     */
    protected function convertHideTR(string $type = ''): void
    {
        $regex = '/<tr\b[^<]*>[^<]*(?:<(?!tr\b)[^<]*)*#' . $type . 'HIDETR#[^<]*(?:<(?!\/tr>)[^<]*)*<\/tr>/';

        self::$content = preg_replace($regex, '', self::$content);
        self::$content = str_replace('#HIDETR#', '', self::$content);
    }

    public function convertCurrencyInfo(): void
    {
        $currencyId = self::$recordModel->get('currency_id');
        $currencyInfo = (array)Vtiger_Functions::getCurrencyInfo($currencyId);

        self::$rep['$CURRENCYNAME$'] = $currencyInfo['currency_name'] ?? '';
        self::$rep['$CURRENCYSYMBOL$'] = $currencyInfo['currency_symbol'] ?? '';
        self::$rep['$CURRENCYCODE$'] = $currencyInfo['currency_code'] ?? '';

        $this->replaceContent();
    }

    public function replaceContent(): void
    {
        if (!empty(self::$rep)) {
            self::$content = str_replace(array_keys(self::$rep), self::$rep, self::$content);
            self::$rep = [];
        }
    }

    public function convertCopyHeader(): void
    {
        $html = Core_SimpleHtmlDom_Helper::getInstance(self::$content);

        foreach($html->getHtmlNode()->find('td') as $tdNode) {
            if ('#copyheader#' !== strtolower(trim($tdNode->plaintext))) {
                continue;
            }

            $table = $html->parents($tdNode, 'table');
            $tr = $html->parents($tdNode, 'tr');
            $headerTr = $table->find('tr', 0);

            $tr->outertext = $headerTr->outertext;
        }

        $html = $html->getHtml();

        self::$content = $html;
    }

    public function convertPageBreak(): void
    {
        $html = Core_SimpleHtmlDom_Helper::getInstance(self::$content);

        foreach ($html->getHtmlNode()->find('td') as $tdNode) {
            if ('#pagebreak#' !== strtolower(trim($tdNode->plaintext))) {
                continue;
            }


            $table = clone $html->parents($tdNode, 'table');
            $table->nodes = null;
            $table->innertext = '#EXPLODE#';
            $tableContent = $table->save();
            $tableInfo = explode('#EXPLODE#', $tableContent);

            $tr = $html->parents($tdNode, 'tr');
            $tr->nodes = null;
            $tr->outertext = $tableInfo[1] . '#PAGEBREAK#' . $tableInfo[0];
        }

        self::$content = $html->getHtml();

        self::$rep['#pagebreak#'] = self::$pagebreak;
        self::$rep['#PAGEBREAK#'] = self::$pagebreak;
        $this->replaceContent();
    }
}