<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class Settings_Vtiger_CompanyDetails_Model extends Settings_Vtiger_Module_Model {

	public static array $logoSupportedFormats = array('jpeg', 'jpg', 'png', 'gif', 'pjpeg', 'x-png');

	public $baseTable = 'vtiger_organizationdetails';
	public $baseIndex = 'organization_id';
	public $listFields = array('organizationname');
	public $nameFields = array('organizationname');
	public $logoPath = 'test/logo/';
    public $blocks = [
        'LBL_COMPANY_LOGO',
        'LBL_COMPANY_INFORMATION',
        'LBL_BANK_INFORMATION',
        'LBL_DESCRIPTION_INFORMATION',
    ];
    public $fields = [
        'logoname' => 'text',
        'logo' => 'file',
        //basic fields
        'organizationname' => 'text',
        'address' => 'textarea',
        'city' => 'text',
        'state' => 'text',
        'code' => 'text',
        'country_id' => 'country',
        'phone' => 'text',
        'email' => 'email',
        'website' => 'text',
        'vatid' => 'text',
        'company_reg_no' => 'text',
        //bank fields
        'bank_name' => 'text',
        'bank_account_no' => 'text',
        'iban' => 'text',
        'swift' => 'text',
        //description fields
        'description' => 'textarea',
    ];

    public $companyLogoFields = [
        'logoname' => 'text',
        'logo' => 'file',
    ];

    public $companyBasicFields = [
        'organizationname' => 'text',
        'address' => 'textarea',
        'city' => 'text',
        'state' => 'text',
        'code' => 'text',
        'country_id' => 'country',
        'phone' => 'text',
        'email' => 'email',
        'website' => 'text',
        'vatid' => 'text',
        'company_reg_no' => 'text',
    ];

    public $companyBankFields = [
        'bank_name' => 'text',
        'bank_account_no' => 'text',
        'iban' => 'text',
        'swift' => 'text',
    ];

    public $companyDescriptionFields = [
        'description' => 'textarea',
    ];

    public $companySocialLinks = array(
		'website' => 'text',
	);

	/**
	 * Function to get Edit view Url
	 * @return <String> Url
	 */
	public function getEditViewUrl() {
		return 'index.php?module=Vtiger&parent=Settings&view=CompanyDetailsEdit';
	}

	/**
	 * Function to get CompanyDetails Menu item
	 * @return Settings_Vtiger_MenuItem_Model menu item Model
	 */
    public function getMenuItem()
    {
        return Settings_Vtiger_MenuItem_Model::getInstance('LBL_COMPANY_DETAILS');
    }

    /**
	 * Function to get Index view Url
	 * @return <String> URL
	 */
	public function getIndexViewUrl() {
		$menuItem = $this->getMenuItem();
		return 'index.php?module=Vtiger&parent=Settings&view=CompanyDetails&block='.$menuItem->get('blockid').'&fieldid='.$menuItem->get('fieldid');
	}

	/**
	 * Function to get fields
	 * @return <Array>
	 */
	public function getFields() {
		return $this->fields;
	}

    public function getBlocks() {
        return $this->blocks;
    }

    public function isBlockField(string $blockName, string $fieldName): bool
    {
        if ('LBL_COMPANY_LOGO' === $blockName) {
            return isset($this->companyLogoFields[$fieldName]);
        }

        if ('LBL_COMPANY_INFORMATION' === $blockName) {
            return isset($this->companyBasicFields[$fieldName]);
        }

        if ('LBL_BANK_INFORMATION' === $blockName) {
            return isset($this->companyBankFields[$fieldName]);
        }

        if ('LBL_DESCRIPTION_INFORMATION' === $blockName) {
            return isset($this->companyDescriptionFields[$fieldName]);
        }

        return false;
    }

    /**
	 * Function to get Logo path to display
	 * @return <String> path
	 */
	public function getLogoPath() {
		$logoPath = $this->logoPath;
		$handler = @opendir($logoPath);
		$logoName = decode_html($this->get('logoname'));
        $logoPath = Vtiger_Functions::getLogoPublicURL($logoName);

		if ($logoName && $handler) {
			while ($file = readdir($handler)) {
				if($logoName === $file && in_array(str_replace('.', '', strtolower(substr($file, -4))), self::$logoSupportedFormats) && $file != "." && $file!= "..") {
					closedir($handler);
					return $logoPath;
				}
			}
		}
		return '';
	}

	/**
	 * Function to save the logoinfo
	 */
	public function saveLogo($logoName) {
		$uploadDir = vglobal('root_directory'). '/' .$this->logoPath;
		$logoName = $uploadDir.$logoName;
		move_uploaded_file($_FILES["logo"]["tmp_name"], $logoName);
		copy($logoName, $uploadDir.'application.ico');
	}


    public function getParams(): array
    {
        $params = [];

        foreach ($this->getFields() as $field => $type) {
            if ('logo' === $field) {
                continue;
            }

            $params[$field] = $this->get($field);
        }

        return $params;
    }

    /**
     * Function to save the Company details
     * @throws AppException
     */
    public function save(): void
    {
        $db = PearDatabase::getInstance();
        $params = $this->getParams();
        $tableName = $this->baseTable;
        $tableIndex = $this->baseIndex;
        $table = (new Core_DatabaseData_Model())->getTable($tableName, $tableIndex);

        if (!$this->isEmpty('id')) {
            $id = $this->get('id');
            $table->updateData($params, [$tableIndex => $id]);
        } else {
            $params[$tableIndex] = $db->getUniqueID($this->baseTable);
            $table->insertData($params);
        }
    }

    /**
     * Function to get the instance of Company details module model
     * @return self $moduleModel
     * @throws Exception
     */
    public static function getInstance($name = '')
    {
        $moduleModel = new self();
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT * FROM vtiger_organizationdetails', []);

        if ($db->num_rows($result) == 1) {
            $moduleModel->setData($db->query_result_rowdata($result));
            $moduleModel->set('id', $moduleModel->get('organization_id'));
        }

        return $moduleModel;
    }

    /**
     * @return void
     * @throws AppException
     */
    public function createTables(): void
    {
        (new Core_DatabaseTable_Model())->getTable('vtiger_organizationdetails', null)
            ->createTable('organization_id', 'int(11)')
            ->createColumn('logoname', 'varchar(50) DEFAULT NULL')
            ->createColumn('logo', 'text DEFAULT NULL')
            ->createColumn('organizationname', 'varchar(60) DEFAULT NULL')
            ->createColumn('address', 'varchar(150) DEFAULT NULL')
            ->createColumn('city', 'varchar(100) DEFAULT NULL')
            ->createColumn('state', 'varchar(100) DEFAULT NULL')
            ->createColumn('code', 'varchar(30) DEFAULT NULL')
            ->createColumn('country_id', 'varchar(4) DEFAULT NULL')
            ->createColumn('phone', 'varchar(30) DEFAULT NULL')
            ->createColumn('email', 'varchar(200) DEFAULT NULL')
            ->createColumn('website', 'varchar(100) DEFAULT NULL')
            ->createColumn('vatid', 'varchar(100) DEFAULT NULL')
            ->createColumn('company_reg_no', 'varchar(200) DEFAULT NULL')
            ->createColumn('bank_name', 'varchar(200) DEFAULT NULL')
            ->createColumn('bank_account_no', 'varchar(200) DEFAULT NULL')
            ->createColumn('iban', 'varchar(200) DEFAULT NULL')
            ->createColumn('swift', 'varchar(200) DEFAULT NULL')
            ->createColumn('description', 'text DEFAULT NULL');
    }

    public function getDisplayValue($fieldName)
    {
        $value = decode_html($this->get($fieldName));

        if ($this->isTextareaField($fieldName)) {
            return nl2br($value);
        }

        if ($this->isCountryField($fieldName)) {
            $country = Core_Country_Model::getInstance();
            $info = $country->getCountry($value);

            return $info['name'];
        }

        return $value;
    }

    public function getCountries(): array
    {
        /** @var Core_Country_Model $countryModel */
        $countryModel = Core_Country_Model::getInstance('');
        $countries = [];

        foreach ($countryModel->getCountries() as $country) {
            if (!empty($country['is_active'])) {
                $countries[$country['code']] = $country['name'];
            }
        }

        return $countries;
    }

    public function isCountryField(string $fieldName): bool
    {
        return 'country' === $this->fields[$fieldName];
    }

    public function isTextareaField(string $fieldName): bool
    {
        return 'textarea' === $this->fields[$fieldName];
    }
}
