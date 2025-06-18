<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Users_Install_Model extends Core_Install_Model
{
    public static array $separator_labes = [
        ',' => 'Comma (,)',
        '.' => 'Dot (.)',
        ' ' => 'Space ( )',
        "'" => "Apostrophe (')",
        '$' => 'Dollar ($)',
    ];

    public static array $currency_decimal_separator = [
        ',',
        '.',
        '\'',
        '$',
    ];

    public static array $currency_grouping_separator = [
        ' ',
        '.',
        ',',
        '\'',
        '$',
    ];

    /**
     * @return string
     */
    public static function getDefaultGroupingSeparator(): string
    {
        global $user_config;

        return $user_config['currency_grouping_separator'] ?? '.';
    }

    /**
     * @return string
     */
    public static function getDefaultDecimalSeparator(): string
    {
        global $user_config;

        return $user_config['currency_decimal_separator'] ?? ',';
    }

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        $this->updateProfiles();
    }

    public function updateProfiles()
    {
        $sql = 'SELECT vtiger_user2role.userid as user_id, vtiger_role2profile.profileid as profile_id FROM vtiger_user2role 
    INNER JOIN vtiger_role2profile ON vtiger_role2profile.roleid=vtiger_user2role.roleid';
        $result = $this->getDB()->pquery($sql);

        while ($row = $this->getDB()->fetchByAssoc($result)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($row['user_id'], 'Users');

            if ($recordModel && $recordModel->isEmpty('profile_id')) {
                $recordModel->set('profile_id', $row['profile_id']);
                $recordModel->set('mode', 'edit');
                $recordModel->save();
            }
        }
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
    }

    public function retrieveBlocks(): void
    {
        self::$fieldsConfig['Users'] = $this->getBlocks();
    }

    /**
     * @return array[]
     */
    public function getBlocks(): array
    {
        return [
            'LBL_USERLOGIN_ROLE' => [
                'user_name' => [
                    'name' => 'user_name',
                    'uitype' => 106,
                    'column' => 'user_name',
                    'table' => 'vtiger_users',
                    'label' => 'User Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 11,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'headerfield' => 0,
                    'filter' => 1,
                    'filter_sequence' => 8,
                ],
                'is_admin' => [
                    'name' => 'is_admin',
                    'uitype' => 156,
                    'column' => 'is_admin',
                    'table' => 'vtiger_users',
                    'label' => 'Admin',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 3,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'headerfield' => 0,
                    'filter' => 1,
                    'filter_sequence' => 9,
                ],
                'user_password' => [
                    'name' => 'user_password',
                    'uitype' => 99,
                    'column' => 'user_password',
                    'table' => 'vtiger_users',
                    'label' => 'Password',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 30,
                    'typeofdata' => 'P~M',
                    'quickcreate' => 1,
                    'displaytype' => 4,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'confirm_password' => [
                    'name' => 'confirm_password',
                    'uitype' => 99,
                    'column' => 'confirm_password',
                    'table' => 'vtiger_users',
                    'label' => 'Confirm Password',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 30,
                    'typeofdata' => 'P~M',
                    'quickcreate' => 1,
                    'displaytype' => 4,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'first_name' => [
                    'name' => 'first_name',
                    'uitype' => 1,
                    'column' => 'first_name',
                    'table' => 'vtiger_users',
                    'label' => 'First Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 30,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'headerfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 1,
                    'entity_identifier' => 1,
                ],
                'last_name' => [
                    'name' => 'last_name',
                    'uitype' => 2,
                    'column' => 'last_name',
                    'table' => 'vtiger_users',
                    'label' => 'Last Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 30,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'headerfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 2,
                    'entity_identifier' => 1,
                ],
                'roleid' => [
                    'name' => 'roleid',
                    'uitype' => 98,
                    'column' => 'roleid',
                    'table' => 'vtiger_user2role',
                    'label' => 'Role',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 200,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'headerfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 4,
                ],
                'email1' => [
                    'name' => 'email1',
                    'uitype' => 104,
                    'column' => 'email1',
                    'table' => 'vtiger_users',
                    'label' => 'Email',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'E~M',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 3,
                ],
                'profile_id' => [
                    'name' => 'profile_id',
                    'uitype' => 14001,
                    'column' => 'profile_id',
                    'table' => 'df_user2profile',
                    'label' => 'Profile',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 5,
                ],
                'status' => [
                    'name' => 'status',
                    'uitype' => 115,
                    'column' => 'status',
                    'table' => 'vtiger_users',
                    'label' => 'Status',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'filter' => 1,
                    'filter_sequence' => 10,
                ],
                'end_hour' => [
                    'name' => 'end_hour',
                    'uitype' => 116,
                    'column' => 'end_hour',
                    'table' => 'vtiger_users',
                    'label' => 'Day ends at',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'is_owner' => [
                    'name' => 'is_owner',
                    'uitype' => 1,
                    'column' => 'is_owner',
                    'table' => 'vtiger_users',
                    'label' => 'Account Owner',
                    'readonly' => 0,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 0,
                    'displaytype' => 5,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'language' => [
                    'name' => 'language',
                    'uitype' => 32,
                    'column' => 'language',
                    'table' => 'vtiger_users',
                    'label' => 'Language',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_CURRENCY_CONFIGURATION' => [
                'currency_id' => [
                    'name' => 'currency_id',
                    'uitype' => 117,
                    'column' => 'currency_id',
                    'table' => 'vtiger_users',
                    'label' => 'Currency',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'currency_grouping_pattern' => [
                    'name' => 'currency_grouping_pattern',
                    'uitype' => 16,
                    'column' => 'currency_grouping_pattern',
                    'table' => 'vtiger_users',
                    'label' => 'Digit Grouping Pattern',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                            '123,456,789',
                            '123456789',
                            '123456,789',
                            '12,34,56,789',
                        ],
                ],
                'currency_decimal_separator' => [
                    'name' => 'currency_decimal_separator',
                    'uitype' => 16,
                    'column' => 'currency_decimal_separator',
                    'table' => 'vtiger_users',
                    'label' => 'Decimal Separator',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'defaultvalue' => self::getDefaultDecimalSeparator(),
                    'picklist_overwrite' => true,
                    'picklist_values' => self::$currency_decimal_separator,
                ],
                'currency_grouping_separator' => [
                    'name' => 'currency_grouping_separator',
                    'uitype' => 16,
                    'column' => 'currency_grouping_separator',
                    'table' => 'vtiger_users',
                    'label' => 'Digit Grouping Separator',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'defaultvalue' => self::getDefaultGroupingSeparator(),
                    'picklist_overwrite' => true,
                    'picklist_values' => self::$currency_grouping_separator,
                ],
                'currency_symbol_placement' => [
                    'name' => 'currency_symbol_placement',
                    'uitype' => 16,
                    'column' => 'currency_symbol_placement',
                    'table' => 'vtiger_users',
                    'label' => 'Symbol Placement',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 20,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                            '$1.0',
                            '1.0$',
                        ],
                ],
                'no_of_currency_decimals' => [
                    'name' => 'no_of_currency_decimals',
                    'uitype' => 16,
                    'column' => 'no_of_currency_decimals',
                    'table' => 'vtiger_users',
                    'label' => 'Number Of Currency Decimals',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'defaultvalue' => 2,
                    'helpinfo' => "<b>Currency - Number of Decimal places</b> <br/><br/>".
                        "Number of decimal places specifies how many number of decimals will be shown after decimal separator.<br/>".
                        "<b>Eg:</b> 123.00",
                    'picklist_values' => [
                        '0',
                        '1',
                        '2',
                        '3',
                        '4',
                    ],
                ],
                'truncate_trailing_zeros' => [
                    'name' => 'truncate_trailing_zeros',
                    'uitype' => 56,
                    'column' => 'truncate_trailing_zeros',
                    'table' => 'vtiger_users',
                    'label' => 'Truncate Trailing Zeros',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'helpinfo' => "<b> Truncate Trailing Zeros </b> <br/><br/>".
                        "It truncated trailing 0s in any of Currency, Decimal and Percentage Field types<br/><br/>".
                        "<b>Ex:</b><br/>".
                        "If value is 89.00000 then <br/>".
                        "decimal and Percentage fields were shows 89<br/>".
                        "currency field type - shows 89.00<br/>",
                ],
            ],
            'LBL_MORE_INFORMATION' => [
                    'title' => [
                        'name' => 'title',
                        'uitype' => 1,
                        'column' => 'title',
                        'table' => 'vtiger_users',
                        'label' => 'Title',
                        'readonly' => 1,
                        'presence' => 0,
                        'maximumlength' => 50,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'phone_work' => [
                        'name' => 'phone_work',
                        'uitype' => 11,
                        'column' => 'phone_work',
                        'table' => 'vtiger_users',
                        'label' => 'Phone',
                        'readonly' => 1,
                        'presence' => 0,
                        'maximumlength' => 50,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                        'headerfield' => 0,
                        'filter' => 1,
                        'filter_sequence' => 7,
                    ],
                    'department' => [
                        'name' => 'department',
                        'uitype' => 1,
                        'column' => 'department',
                        'table' => 'vtiger_users',
                        'label' => 'Department',
                        'readonly' => 1,
                        'presence' => 0,
                        'maximumlength' => 50,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'reports_to_id' => [
                        'name' => 'reports_to_id',
                        'uitype' => 101,
                        'column' => 'reports_to_id',
                        'table' => 'vtiger_users',
                        'label' => 'Reports To',
                        'readonly' => 1,
                        'presence' => 0,
                        'maximumlength' => 50,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'phone_other' => [
                        'name' => 'phone_other',
                        'uitype' => 11,
                        'column' => 'phone_other',
                        'table' => 'vtiger_users',
                        'label' => 'Other Phone',
                        'readonly' => 1,
                        'presence' => 0,
                        'maximumlength' => 50,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'secondaryemail' => [
                        'name' => 'secondaryemail',
                        'uitype' => 13,
                        'column' => 'secondaryemail',
                        'table' => 'vtiger_users',
                        'label' => 'Secondary Email',
                        'readonly' => 1,
                        'presence' => 0,
                        'typeofdata' => 'E~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'signature' => [
                        'name' => 'signature',
                        'uitype' => 21,
                        'column' => 'signature',
                        'table' => 'vtiger_users',
                        'label' => 'Signature',
                        'readonly' => 1,
                        'presence' => 0,
                        'maximumlength' => 250,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'internal_mailer' => [
                        'name' => 'internal_mailer',
                        'uitype' => 56,
                        'column' => 'internal_mailer',
                        'table' => 'vtiger_users',
                        'label' => 'INTERNAL_MAIL_COMPOSER',
                        'readonly' => 1,
                        'presence' => 0,
                        'maximumlength' => 50,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'theme' => [
                        'name' => 'theme',
                        'uitype' => 31,
                        'column' => 'theme',
                        'table' => 'vtiger_users',
                        'label' => 'Theme',
                        'readonly' => 1,
                        'presence' => 0,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'phone_crm_extension' => [
                        'name' => 'phone_crm_extension',
                        'uitype' => 11,
                        'column' => 'phone_crm_extension',
                        'table' => 'vtiger_users',
                        'label' => 'CRM Phone Extension',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'default_record_view' => [
                        'name' => 'default_record_view',
                        'uitype' => 16,
                        'column' => 'default_record_view',
                        'table' => 'vtiger_users',
                        'label' => 'Default Record View',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                        'picklist_values' =>
                            [
                                'Summary',
                                'Detail',
                            ],
                    ],
                    'rowheight' => [
                        'name' => 'rowheight',
                        'uitype' => 16,
                        'column' => 'rowheight',
                        'table' => 'vtiger_users',
                        'label' => 'Row Height',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                        'picklist_values' => [
                            'wide',
                            'medium',
                            'narrow',
                        ],
                    ],
                    'userlabel' => [
                        'name' => 'userlabel',
                        'uitype' => 1,
                        'column' => 'userlabel',
                        'table' => 'vtiger_users',
                        'label' => 'User Label',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 3,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                ],
            'LBL_ADDRESS_INFORMATION' => [
                'address_street' => [
                    'name' => 'address_street',
                    'uitype' => 21,
                    'column' => 'address_street',
                    'table' => 'vtiger_users',
                    'label' => 'Street Address',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 250,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'address_city' => [
                    'name' => 'address_city',
                    'uitype' => 1,
                    'column' => 'address_city',
                    'table' => 'vtiger_users',
                    'label' => 'City',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'address_state' => [
                    'name' => 'address_state',
                    'uitype' => 1,
                    'column' => 'address_state',
                    'table' => 'vtiger_users',
                    'label' => 'State',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'address_postalcode' => [
                    'name' => 'address_postalcode',
                    'uitype' => 1,
                    'column' => 'address_postalcode',
                    'table' => 'vtiger_users',
                    'label' => 'Postal Code',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'address_country_id' => [
                    'name' => 'address_country_id',
                    'uitype' => 18,
                    'column' => 'address_country_id',
                    'table' => 'vtiger_users',
                    'label' => 'Country',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_USER_IMAGE_INFORMATION' => [
                'imagename' => [
                    'name' => 'imagename',
                    'uitype' => 105,
                    'column' => 'imagename',
                    'table' => 'vtiger_users',
                    'label' => 'User Image',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 250,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_USER_ADV_OPTIONS' => [
                'accesskey' => [
                    'name' => 'accesskey',
                    'uitype' => 3,
                    'column' => 'accesskey',
                    'table' => 'vtiger_users',
                    'label' => 'Webservice Access Key',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 2,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_CALENDAR_SETTINGS' => [
                'activity_view' => [
                    'name' => 'activity_view',
                    'uitype' => 16,
                    'column' => 'activity_view',
                    'table' => 'vtiger_users',
                    'label' => 'Default Activity View',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        'Today',
                        'This Week',
                        'This Month',
                    ],
                ],
                'hour_format' => [
                    'name' => 'hour_format',
                    'uitype' => 16,
                    'column' => 'hour_format',
                    'table' => 'vtiger_users',
                    'label' => 'Calendar Hour Format',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        '12',
                        '24',
                    ],
                ],
                'start_hour' => [
                    'name' => 'start_hour',
                    'uitype' => 16,
                    'column' => 'start_hour',
                    'table' => 'vtiger_users',
                    'label' => 'Day starts at',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        '00:00',
                        '01:00',
                        '02:00',
                        '03:00',
                        '04:00',
                        '05:00',
                        '06:00',
                        '07:00',
                        '08:00',
                        '09:00',
                        '10:00',
                        '11:00',
                        '12:00',
                        '13:00',
                        '14:00',
                        '15:00',
                        '16:00',
                        '17:00',
                        '18:00',
                        '19:00',
                        '20:00',
                        '21:00',
                        '22:00',
                        '23:00',
                    ],
                ],
                'date_format' => [
                    'name' => 'date_format',
                    'uitype' => 16,
                    'column' => 'date_format',
                    'table' => 'vtiger_users',
                    'label' => 'Date Format',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 30,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                            'dd-mm-yyyy',
                            'mm-dd-yyyy',
                            'yyyy-mm-dd',
                            'dd.mm.yyyy',
                            'dd/mm/yyyy',
                        ],
                ],
                'time_zone' => [
                    'name' => 'time_zone',
                    'uitype' => 16,
                    'column' => 'time_zone',
                    'table' => 'vtiger_users',
                    'label' => 'Time Zone',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 200,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        'Pacific/Midway',
                        'Pacific/Samoa',
                        'Pacific/Honolulu',
                        'America/Anchorage',
                        'America/Los_Angeles',
                        'America/Tijuana',
                        'America/Denver',
                        'America/Chihuahua',
                        'America/Mazatlan',
                        'America/Phoenix',
                        'America/Regina',
                        'America/Tegucigalpa',
                        'America/Chicago',
                        'America/Mexico_City',
                        'America/Monterrey',
                        'America/New_York',
                        'America/Bogota',
                        'America/Lima',
                        'America/Rio_Branco',
                        'America/Indiana/Indianapolis',
                        'America/Caracas',
                        'America/Halifax',
                        'America/Manaus',
                        'America/Santiago',
                        'America/La_Paz',
                        'America/Cuiaba',
                        'America/Asuncion',
                        'America/St_Johns',
                        'America/Argentina/Buenos_Aires',
                        'America/Sao_Paulo',
                        'America/Godthab',
                        'America/Montevideo',
                        'Atlantic/South_Georgia',
                        'Atlantic/Azores',
                        'Atlantic/Cape_Verde',
                        'Europe/London',
                        'UTC',
                        'Africa/Monrovia',
                        'Africa/Casablanca',
                        'Europe/Belgrade',
                        'Europe/Sarajevo',
                        'Europe/Brussels',
                        'Africa/Algiers',
                        'Europe/Amsterdam',
                        'Europe/Minsk',
                        'Africa/Cairo',
                        'Europe/Helsinki',
                        'Europe/Athens',
                        'Europe/Istanbul',
                        'Asia/Jerusalem',
                        'Asia/Amman',
                        'Asia/Beirut',
                        'Africa/Windhoek',
                        'Africa/Harare',
                        'Asia/Kuwait',
                        'Asia/Baghdad',
                        'Africa/Nairobi',
                        'Asia/Tehran',
                        'Asia/Tbilisi',
                        'Europe/Moscow',
                        'Asia/Muscat',
                        'Asia/Baku',
                        'Asia/Yerevan',
                        'Asia/Karachi',
                        'Asia/Tashkent',
                        'Asia/Kolkata',
                        'Asia/Colombo',
                        'Asia/Katmandu',
                        'Asia/Dhaka',
                        'Asia/Almaty',
                        'Asia/Yekaterinburg',
                        'Asia/Rangoon',
                        'Asia/Novosibirsk',
                        'Asia/Bangkok',
                        'Asia/Brunei',
                        'Asia/Krasnoyarsk',
                        'Asia/Ulaanbaatar',
                        'Asia/Kuala_Lumpur',
                        'Asia/Taipei',
                        'Australia/Perth',
                        'Asia/Irkutsk',
                        'Asia/Seoul',
                        'Asia/Tokyo',
                        'Australia/Darwin',
                        'Australia/Adelaide',
                        'Australia/Canberra',
                        'Australia/Brisbane',
                        'Australia/Hobart',
                        'Asia/Vladivostok',
                        'Pacific/Guam',
                        'Asia/Yakutsk',
                        'Pacific/Fiji',
                        'Asia/Kamchatka',
                        'Pacific/Auckland',
                        'Asia/Magadan',
                        'Pacific/Tongatapu',
                        'Etc/GMT-11',
                    ],
                ],
                'reminder_interval' => [
                    'name' => 'reminder_interval',
                    'uitype' => 16,
                    'column' => 'reminder_interval',
                    'table' => 'vtiger_users',
                    'label' => 'Reminder Interval',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        '1 Minute',
                        '5 Minutes',
                        '15 Minutes',
                        '30 Minutes',
                        '45 Minutes',
                        '1 Hour',
                        '1 Day',
                    ],
                ],
                'dayoftheweek' => [
                    'name' => 'dayoftheweek',
                    'uitype' => 16,
                    'column' => 'dayoftheweek',
                    'table' => 'vtiger_users',
                    'label' => 'Starting Day of the week',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'defaultvalue' => 'Monday',
                    'picklist_values' => [
                        'Sunday',
                        'Monday',
                        'Tuesday',
                        'Wednesday',
                        'Thursday',
                        'Friday',
                        'Saturday',
                    ],
                ],
                'callduration' => [
                    'name' => 'callduration',
                    'uitype' => 16,
                    'column' => 'callduration',
                    'table' => 'vtiger_users',
                    'label' => 'Default Call Duration',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'defaultvalue' => 5,
                    'picklist_values' => [
                        '5',
                        '10',
                        '30',
                        '60',
                        '120',
                    ],
                ],
                'othereventduration' => [
                    'name' => 'othereventduration',
                    'uitype' => 16,
                    'column' => 'othereventduration',
                    'table' => 'vtiger_users',
                    'label' => 'Other Event Duration',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'defaultvalue' => 5,
                    'picklist_values' => [
                        '5',
                        '10',
                        '30',
                        '60',
                        '120',
                    ],
                ],
                'calendarsharedtype' => [
                    'name' => 'calendarsharedtype',
                    'uitype' => 16,
                    'column' => 'calendarsharedtype',
                    'table' => 'vtiger_users',
                    'label' => 'Calendar Shared Type',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'defaultvalue' => 'public',
                    'picklist_values' => [
                        'public',
                        'private',
                        'seletedusers',
                    ],
                ],
                'defaulteventstatus' => [
                    'name' => 'defaulteventstatus',
                    'uitype' => 15,
                    'column' => 'defaulteventstatus',
                    'table' => 'vtiger_users',
                    'label' => 'Default Event Status',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        'Planned',
                        'Held',
                        'Not Held',
                    ],
                ],
                'defaultactivitytype' =>
                    [
                        'name' => 'defaultactivitytype',
                        'uitype' => 15,
                        'column' => 'defaultactivitytype',
                        'table' => 'vtiger_users',
                        'label' => 'Default Activity Type',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                        'picklist_values' => [
                            'Call',
                            'Meeting',
                        ],
                    ],
                'hidecompletedevents' =>
                    [
                        'name' => 'hidecompletedevents',
                        'uitype' => 56,
                        'column' => 'hidecompletedevents',
                        'table' => 'vtiger_users',
                        'label' => 'LBL_HIDE_COMPLETED_EVENTS',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'C~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                'defaultcalendarview' =>
                    [
                        'name' => 'defaultcalendarview',
                        'uitype' => 16,
                        'column' => 'defaultcalendarview',
                        'table' => 'vtiger_users',
                        'label' => 'Default Calendar View',
                        'readonly' => 1,
                        'presence' => 0,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                        'picklist_values' =>
                            [
                                'ListView',
                                'MyCalendar',
                            ],
                    ],
                'week_days' => [
                    'name' => 'week_days',
                    'uitype' => 33,
                    'column' => 'week_days',
                    'table' => 'vtiger_users',
                    'label' => 'Week days',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        'Monday',
                        'Tuesday',
                        'Wednesday',
                        'Thursday',
                        'Friday',
                        'Saturday',
                        'Sunday',
                    ],
                ],
                'slot_duration' => [
                    'name' => 'slot_duration',
                    'uitype' => 15,
                    'column' => 'slot_duration',
                    'table' => 'vtiger_users',
                    'label' => 'Slot duration',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        '30 minutes',
                        '15 minutes',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getTables(): array
    {
        return [];
    }

    /**
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_users', null)
            ->createTable('id', 'int(11) NOT NULL')
            ->createColumn('user_name', 'varchar(255) DEFAULT NULL')
            ->createColumn('user_password', 'varchar(200) DEFAULT NULL')
            ->createColumn('cal_color', 'varchar(25) DEFAULT \'#E6FAD8\'')
            ->createColumn('first_name', 'varchar(30) DEFAULT NULL')
            ->createColumn('last_name', 'varchar(30) DEFAULT NULL')
            ->createColumn('reports_to_id', 'varchar(36) DEFAULT NULL')
            ->createColumn('is_admin', 'varchar(3) DEFAULT \'0\'')
            ->createColumn('currency_id', 'int(19) NOT NULL DEFAULT 1')
            ->createColumn('date_entered', 'timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()')
            ->createColumn('date_modified', 'datetime DEFAULT NULL')
            ->createColumn('modified_user_id', 'varchar(36) DEFAULT NULL')
            ->createColumn('title', 'varchar(50) DEFAULT NULL')
            ->createColumn('department', 'varchar(50) DEFAULT NULL')
            ->createColumn('phone_work', 'varchar(50) DEFAULT NULL')
            ->createColumn('phone_other', 'varchar(50) DEFAULT NULL')
            ->createColumn('email1', 'varchar(100) DEFAULT NULL')
            ->createColumn('secondaryemail', 'varchar(100) DEFAULT NULL')
            ->createColumn('status', 'varchar(25) DEFAULT NULL')
            ->createColumn('signature', 'text DEFAULT NULL')
            ->createColumn('address_street', 'varchar(250) DEFAULT NULL')
            ->createColumn('address_city', 'varchar(150) DEFAULT NULL')
            ->createColumn('address_state', 'varchar(150) DEFAULT NULL')
            ->createColumn('address_country_id', 'varchar(2) DEFAULT NULL')
            ->createColumn('address_postalcode', 'varchar(150) DEFAULT NULL')
            ->createColumn('user_preferences', 'text DEFAULT NULL')
            ->createColumn('tz', 'varchar(30) DEFAULT NULL')
            ->createColumn('holidays', 'varchar(60) DEFAULT NULL')
            ->createColumn('namedays', 'varchar(60) DEFAULT NULL')
            ->createColumn('workdays', 'varchar(30) DEFAULT NULL')
            ->createColumn('weekstart', 'int(11) DEFAULT NULL')
            ->createColumn('date_format', 'varchar(200) DEFAULT NULL')
            ->createColumn('hour_format', 'varchar(30) DEFAULT \'24\'')
            ->createColumn('start_hour', 'varchar(30) DEFAULT \'10:00\'')
            ->createColumn('end_hour', 'varchar(30) DEFAULT \'23:00\'')
            ->createColumn('is_owner', 'varchar(100) DEFAULT \'0\'')
            ->createColumn('activity_view', 'varchar(200) DEFAULT \'Today\'')
            ->createColumn('imagename', 'varchar(250) DEFAULT NULL')
            ->createColumn('deleted', 'int(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('confirm_password', 'varchar(300) DEFAULT NULL')
            ->createColumn('internal_mailer', 'varchar(3) NOT NULL DEFAULT \'1\'')
            ->createColumn('reminder_interval', 'varchar(100) DEFAULT NULL')
            ->createColumn('reminder_next_time', 'varchar(100) DEFAULT NULL')
            ->createColumn('crypt_type', 'varchar(20) NOT NULL DEFAULT \'MD5\'')
            ->createColumn('accesskey', 'varchar(36) DEFAULT NULL')
            ->createColumn('theme', 'varchar(100) DEFAULT NULL')
            ->createColumn('language', 'varchar(36) DEFAULT NULL')
            ->createColumn('time_zone', 'varchar(200) DEFAULT NULL')
            ->createColumn('currency_grouping_pattern', 'varchar(100) DEFAULT NULL')
            ->createColumn('currency_decimal_separator', 'varchar(2) DEFAULT NULL')
            ->createColumn('currency_grouping_separator', 'varchar(2) DEFAULT NULL')
            ->createColumn('currency_symbol_placement', 'varchar(20) DEFAULT NULL')
            ->createColumn('userlabel', 'varchar(255) DEFAULT NULL')
            ->createColumn('phone_crm_extension', 'varchar(100) DEFAULT NULL')
            ->createColumn('no_of_currency_decimals', 'varchar(2) DEFAULT NULL')
            ->createColumn('truncate_trailing_zeros', 'varchar(3) DEFAULT NULL')
            ->createColumn('dayoftheweek', 'varchar(100) DEFAULT NULL')
            ->createColumn('callduration', 'varchar(100) DEFAULT NULL')
            ->createColumn('othereventduration', 'varchar(100) DEFAULT NULL')
            ->createColumn('calendarsharedtype', 'varchar(100) DEFAULT NULL')
            ->createColumn('default_record_view', 'varchar(10) DEFAULT NULL')
            ->createColumn('rowheight', 'varchar(10) DEFAULT NULL')
            ->createColumn('defaulteventstatus', 'varchar(50) DEFAULT NULL')
            ->createColumn('defaultactivitytype', 'varchar(50) DEFAULT NULL')
            ->createColumn('hidecompletedevents', 'int(11) DEFAULT NULL')
            ->createColumn('defaultcalendarview', 'varchar(100) DEFAULT NULL')
            ->createColumn('defaultlandingpage', 'varchar(255) NOT NULL')
            ->createColumn('week_days', 'varchar(100) DEFAULT NULL')
            ->createColumn('slot_duration', 'varchar(100) DEFAULT NULL');
        
        $this->getTable('df_user2profile', 'user_id')
            ->createTable('user_id', 'INT(11) NOT NULL')
            ->createColumn('profile_id', 'INT(11) NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`userid`)')
            ->createKey('INDEX IF NOT EXISTS `user2profile_profileid_idx` (`profile_id`)')
            ;
    }
}