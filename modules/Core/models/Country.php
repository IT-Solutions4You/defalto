<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_Country_Model extends Core_DatabaseData_Model
{
    public string $table = 'its4you_countries';
    public static array $countryCodes = [
        'AD' => 'Andorra',
        'AE' => 'United Arab Emirates (the)',
        'AF' => 'Afghanistan',
        'AG' => 'Antigua and Barbuda',
        'AI' => 'Anguilla',
        'AL' => 'Albania',
        'AM' => 'Armenia',
        'AO' => 'Angola',
        'AQ' => 'Antarctica',
        'AR' => 'Argentina',
        'AS' => 'American Samoa',
        'AT' => 'Austria',
        'AU' => 'Australia',
        'AW' => 'Aruba',
        'AX' => 'Åland Islands',
        'AZ' => 'Azerbaijan',
        'BA' => 'Bosnia and Herzegovina',
        'BB' => 'Barbados',
        'BD' => 'Bangladesh',
        'BE' => 'Belgium',
        'BF' => 'Burkina Faso',
        'BG' => 'Bulgaria',
        'BH' => 'Bahrain',
        'BI' => 'Burundi',
        'BJ' => 'Benin',
        'BL' => 'Saint Barthélemy',
        'BM' => 'Bermuda',
        'BN' => 'Brunei Darussalam',
        'BO' => 'Bolivia (Plurinational State of)',
        'BQ' => 'Bonaire, Sint Eustatius and Saba',
        'BR' => 'Brazil',
        'BS' => 'Bahamas (the)',
        'BT' => 'Bhutan',
        'BV' => 'Bouvet Island',
        'BW' => 'Botswana',
        'BY' => 'Belarus',
        'BZ' => 'Belize',
        'CA' => 'Canada',
        'CC' => 'Cocos (Keeling) Islands (the)',
        'CD' => 'Congo (the Democratic Republic of the)',
        'CF' => 'Central African Republic (the)',
        'CG' => 'Congo (the)',
        'CH' => 'Switzerland',
        'CI' => 'Côte d\'Ivoire',
        'CK' => 'Cook Islands (the)',
        'CL' => 'Chile',
        'CM' => 'Cameroon',
        'CN' => 'China',
        'CO' => 'Colombia',
        'CR' => 'Costa Rica',
        'CU' => 'Cuba',
        'CV' => 'Cabo Verde',
        'CW' => 'Curaçao',
        'CX' => 'Christmas Island',
        'CY' => 'Cyprus',
        'CZ' => 'Czechia',
        'DE' => 'Germany',
        'DJ' => 'Djibouti',
        'DK' => 'Denmark',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic (the)',
        'DZ' => 'Algeria',
        'EC' => 'Ecuador',
        'EE' => 'Estonia',
        'EG' => 'Egypt',
        'EH' => 'Western Sahara',
        'ER' => 'Eritrea',
        'ES' => 'Spain',
        'ET' => 'Ethiopia',
        'FI' => 'Finland',
        'FJ' => 'Fiji',
        'FK' => 'Falkland Islands (the) Malvinas]',
        'FM' => 'Micronesia (Federated States of)',
        'FO' => 'Faroe Islands (the)',
        'FR' => 'France',
        'GA' => 'Gabon',
        'GB' => 'United Kingdom of Great Britain and Northern Ireland (the)',
        'GD' => 'Grenada',
        'GE' => 'Georgia',
        'GF' => 'French Guiana',
        'GG' => 'Guernsey',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GL' => 'Greenland',
        'GM' => 'Gambia (the)',
        'GN' => 'Guinea',
        'GP' => 'Guadeloupe',
        'GQ' => 'Equatorial Guinea',
        'GR' => 'Greece',
        'GS' => 'South Georgia and the South Sandwich Islands',
        'GT' => 'Guatemala',
        'GU' => 'Guam',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HK' => 'Hong Kong',
        'HM' => 'Heard Island and McDonald Islands',
        'HN' => 'Honduras',
        'HR' => 'Croatia',
        'HT' => 'Haiti',
        'HU' => 'Hungary',
        'ID' => 'Indonesia',
        'IE' => 'Ireland',
        'IL' => 'Israel',
        'IM' => 'Isle of Man',
        'IN' => 'India',
        'IO' => 'British Indian Ocean Territory (the)',
        'IQ' => 'Iraq',
        'IR' => 'Iran (Islamic Republic of)',
        'IS' => 'Iceland',
        'IT' => 'Italy',
        'JE' => 'Jersey',
        'JM' => 'Jamaica',
        'JO' => 'Jordan',
        'JP' => 'Japan',
        'KE' => 'Kenya',
        'KG' => 'Kyrgyzstan',
        'KH' => 'Cambodia',
        'KI' => 'Kiribati',
        'KM' => 'Comoros (the)',
        'KN' => 'Saint Kitts and Nevis',
        'KP' => 'Korea (the Democratic People\'s Republic of)',
        'KR' => 'Korea (the Republic of)',
        'KW' => 'Kuwait',
        'KY' => 'Cayman Islands (the)',
        'KZ' => 'Kazakhstan',
        'LA' => 'Lao People\'s Democratic Republic (the)',
        'LB' => 'Lebanon',
        'LC' => 'Saint Lucia',
        'LI' => 'Liechtenstein',
        'LK' => 'Sri Lanka',
        'LR' => 'Liberia',
        'LS' => 'Lesotho',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'LV' => 'Latvia',
        'LY' => 'Libya',
        'MA' => 'Morocco',
        'MC' => 'Monaco',
        'MD' => 'Moldova (the Republic of)',
        'ME' => 'Montenegro',
        'MF' => 'Saint Martin (French part)',
        'MG' => 'Madagascar',
        'MH' => 'Marshall Islands (the)',
        'MK' => 'Republic of North Macedonia',
        'ML' => 'Mali',
        'MM' => 'Myanmar',
        'MN' => 'Mongolia',
        'MO' => 'Macao',
        'MP' => 'Northern Mariana Islands (the)',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MS' => 'Montserrat',
        'MT' => 'Malta',
        'MU' => 'Mauritius',
        'MV' => 'Maldives',
        'MW' => 'Malawi',
        'MX' => 'Mexico',
        'MY' => 'Malaysia',
        'MZ' => 'Mozambique',
        'NA' => 'Namibia',
        'NC' => 'New Caledonia',
        'NE' => 'Niger (the)',
        'NF' => 'Norfolk Island',
        'NG' => 'Nigeria',
        'NI' => 'Nicaragua',
        'NL' => 'Netherlands (the)',
        'NO' => 'Norway',
        'NP' => 'Nepal',
        'NR' => 'Nauru',
        'NU' => 'Niue',
        'NZ' => 'New Zealand',
        'OM' => 'Oman',
        'PA' => 'Panama',
        'PE' => 'Peru',
        'PF' => 'French Polynesia',
        'PG' => 'Papua New Guinea',
        'PH' => 'Philippines (the)',
        'PK' => 'Pakistan',
        'PL' => 'Poland',
        'PM' => 'Saint Pierre and Miquelon',
        'PN' => 'Pitcairn',
        'PR' => 'Puerto Rico',
        'PS' => 'Palestine, State of',
        'PT' => 'Portugal',
        'PW' => 'Palau',
        'PY' => 'Paraguay',
        'QA' => 'Qatar',
        'RE' => 'Réunion',
        'RO' => 'Romania',
        'RS' => 'Serbia',
        'RU' => 'Russian Federation (the)',
        'RW' => 'Rwanda',
        'SA' => 'Saudi Arabia',
        'SB' => 'Solomon Islands',
        'SC' => 'Seychelles',
        'SD' => 'Sudan (the)',
        'SE' => 'Sweden',
        'SG' => 'Singapore',
        'SH' => 'Saint Helena, Ascension and Tristan da Cunha',
        'SI' => 'Slovenia',
        'SJ' => 'Svalbard and Jan Mayen',
        'SK' => 'Slovakia',
        'SL' => 'Sierra Leone',
        'SM' => 'San Marino',
        'SN' => 'Senegal',
        'SO' => 'Somalia',
        'SR' => 'Suriname',
        'SS' => 'South Sudan',
        'ST' => 'Sao Tome and Principe',
        'SV' => 'El Salvador',
        'SX' => 'Sint Maarten (Dutch part)',
        'SY' => 'Syrian Arab Republic',
        'SZ' => 'Eswatini',
        'TC' => 'Turks and Caicos Islands (the)',
        'TD' => 'Chad',
        'TF' => 'French Southern Territories (the)',
        'TG' => 'Togo',
        'TH' => 'Thailand',
        'TJ' => 'Tajikistan',
        'TK' => 'Tokelau',
        'TL' => 'Timor-Leste',
        'TM' => 'Turkmenistan',
        'TN' => 'Tunisia',
        'TO' => 'Tonga',
        'TR' => 'Turkey',
        'TT' => 'Trinidad and Tobago',
        'TV' => 'Tuvalu',
        'TW' => 'Taiwan (Province of China)',
        'TZ' => 'Tanzania, United Republic of',
        'UA' => 'Ukraine',
        'UG' => 'Uganda',
        'UM' => 'United States Minor Outlying Islands (the)',
        'US' => 'United States of America (the)',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VA' => 'Holy See (the)',
        'VC' => 'Saint Vincent and the Grenadines',
        'VE' => 'Venezuela (Bolivarian Republic of)',
        'VG' => 'Virgin Islands (British)',
        'VI' => 'Virgin Islands (U.S.)',
        'VN' => 'Viet Nam',
        'VU' => 'Vanuatu',
        'WF' => 'Wallis and Futuna',
        'WS' => 'Samoa',
        'YE' => 'Yemen',
        'YT' => 'Mayotte',
        'ZA' => 'South Africa',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe',
    ];
    public static array $countries = [];
    public string $moduleName = 'Vtiger';

    public function isActive($code): bool
    {
        return !empty(self::$countries[$code]['is_active']) && 1 === self::$countries[$code]['is_active'];
    }

    public function retrieveCountries()
    {
        foreach ($this->getCodes() as $code => $country) {
            self::$countries[$code] = $this->getCountry($code);
        }
    }

    public function getCountry($code)
    {
        if (!empty(self::$countries[$code])) {
            return self::$countries[$code];
        }

        global $countries;

        $countries = (int)$countries + 1;

        $this->retrieveDB();
        $table = $this->getTable($this->table, null);
        $data = $table->selectData([], ['code' => $code]);

        self::$countries[$code] = !empty($data) ? $data : [
            'code' => $code,
            'name' => self::$countryCodes[$code],
            'is_active' => 1,
        ];

        return self::$countries[$code];
    }

    public function getCodes()
    {
        return self::$countryCodes;
    }

    public static function getInstance($moduleName = 'Vtiger')
    {
        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Country', $moduleName);
        $instance = new $modelClassName();
        $instance->moduleName = $moduleName;

        return $instance;
    }

    public function save(): void
    {
        if ($this->isEmpty('countries')) {
            return;
        }

        $countries = (array)$this->get('countries');
        $codes = $this->getCodes();

        foreach ($countries as $code => $active) {
            $name = $codes[$code];
            $table = $this->getTable($this->table, null);
            $data = $table->selectData([], ['code' => $code]);

            if (empty($data)) {
                $table->insertData(['code' => $code, 'name' => $name, 'is_active' => $active]);
            } else {
                $table->updateData(['name' => $name, 'is_active' => $active], ['code' => $code]);
            }
        }
    }

    public function getCountries(): array
    {
        $this->retrieveCountries();

        return self::$countries;
    }

    /**
     * @throws AppException
     */
    public function createTables(): void
    {
        $this->getTable('its4you_countries', 'id')
            ->createTable()
            ->createColumn('code', 'VARCHAR(2)')
            ->createColumn('name', 'VARCHAR(155)')
            ->createColumn('is_active', 'INT(1)')
            ->createKey('UNIQUE KEY IF NOT EXISTS `code` (`code`)');
    }

    public function createLinks(): void
    {
        $link = 'index.php?module=Vtiger&parent=Settings&view=Country';
        $name = 'Countries';
        $blockId = getSettingsBlockId('LBL_CONFIGURATION');
        $linkInstance = Settings_Vtiger_MenuItem_Model::getInstanceFromArray([
            'blockid' => $blockId,
            'name' => $name,
            'linkto' => $link,
        ]);
        $linkInstance->save();
    }

    public function activateAll()
    {
        $codes = $this->getCodes();
        $countries = [];

        foreach ($codes as $code => $country) {
            $countries[$code] = 1;
        }

        $this->set('countries', $countries);
        $this->save();
    }

    public function deactivateAll() {
        $codes = $this->getCodes();
        $countries = [];

        foreach ($codes as $code => $country) {
            $countries[$code] = 0;
        }

        $this->set('countries', $countries);
        $this->save();
    }
}