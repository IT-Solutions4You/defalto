<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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
    public static ?array $activeCodes = null;
    public static ?array $phoneFieldConfig = null;
    public string $moduleName = 'Vtiger';

    public function isActive($code): bool
    {
        return 1 === (int)($this->getCountry($code)['is_active'] ?? 0);
    }

    /**
     * Load the country list once. The seeded its4you_countries table is the source
     * of truth; the hard-coded catalog is only a fallback used before the table is
     * created/seeded (e.g. during installation), never the live source afterwards.
     */
    public function retrieveCountries()
    {
        if (!empty(self::$countries)) {
            return;
        }

        $loaded = [];
        $this->retrieveDB();

        if (Vtiger_Utils::CheckTable($this->table)) {
            $result = $this->db->pquery('SELECT code, name, is_active FROM ' . $this->table);

            while ($result && $row = $this->db->fetchByAssoc($result)) {
                $code = strtoupper((string)$row['code']);
                $loaded[$code] = [
                    'code'      => $code,
                    'name'      => $row['name'],
                    'is_active' => (int)$row['is_active'],
                ];
            }
        }

        if (empty($loaded)) {
            // Table missing/unseeded — derive from the seed catalog so the country
            // field still works during installation.
            foreach ($this->getCodes() as $code => $name) {
                $loaded[$code] = ['code' => $code, 'name' => $name, 'is_active' => 1];
            }
        }

        self::$countries = $loaded;
    }

    public function getCountry($code)
    {
        $code = strtoupper((string)$code);
        $this->retrieveCountries();

        if (isset(self::$countries[$code])) {
            return self::$countries[$code];
        }

        // Unknown code (not seeded and not in the catalog) — synthesise an active
        // entry so display/lookups never fail.
        return self::$countries[$code] = [
            'code'      => $code,
            'name'      => self::resolveCountryName($code),
            'is_active' => 1,
        ];
    }

    public function getCodes()
    {
        return self::$countryCodes;
    }

    /**
     * ISO2 codes (upper-case) of every currently active country, read from the
     * (seeded) country list. Non-ISO extras (e.g. XK) are excluded here as they are
     * not relevant to consumers of this list such as the phone-field widget.
     *
     * @return array
     */
    public function getActiveCodes(): array
    {
        if (null !== self::$activeCodes) {
            return self::$activeCodes;
        }

        $this->retrieveCountries();
        $active = [];

        foreach (self::$countries as $code => $country) {
            if (1 === (int)$country['is_active'] && isset(self::$countryCodes[$code])) {
                $active[] = $code;
            }
        }

        self::$activeCodes = $active;

        return self::$activeCodes;
    }

    /**
     * Configuration consumed by the phone-field widget (intl-tel-input) on the
     * client side: the selectable countries and the initial country.
     *
     * @return array{countries: array, default: string}
     */
    public static function getPhoneFieldConfig(): array
    {
        if (null !== self::$phoneFieldConfig) {
            return self::$phoneFieldConfig;
        }

        $model = self::getInstance();
        $countries = array_map('strtolower', $model->getActiveCodes());
        sort($countries);

        self::$phoneFieldConfig = [
            'countries' => $countries,
            'default'   => $model->getDefaultCode($countries),
        ];

        return self::$phoneFieldConfig;
    }

    /**
     * Best-effort initial country (lower-case ISO2) for new phone values: the
     * company country when it maps to an active code, otherwise the first
     * active country.
     *
     * @param array $activeCodes lower-case ISO2 codes
     *
     * @return string
     */
    public function getDefaultCode(array $activeCodes): string
    {
        $default = '';

        $company = getCompanyDetails();
        $countryName = strtolower(trim((string)($company['country'] ?? '')));

        if ('' !== $countryName) {
            $byName = array_change_key_case(array_flip(self::$countryCodes));

            if (isset($byName[$countryName])) {
                $default = strtolower($byName[$countryName]);
            }
        }

        if ('' === $default || !in_array($default, $activeCodes, true)) {
            $default = $activeCodes[0] ?? 'us';
        }

        return $default;
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
            $name = $codes[$code] ?? self::resolveCountryName($code);
            $table = $this->getTable($this->table, null);
            $data = $table->selectData([], ['code' => $code]);

            if (empty($data)) {
                $table->insertData(['code' => $code, 'name' => $name, 'is_active' => $active]);
            } else {
                $table->updateData(['name' => $name, 'is_active' => $active], ['code' => $code]);
            }
        }

        // Invalidate request-level caches so the change is reflected immediately.
        self::$countries = [];
        self::$activeCodes = null;
        self::$phoneFieldConfig = null;
    }

    public function getCountries(): array
    {
        $this->retrieveCountries();

        return self::$countries;
    }

    /**
     * Resolve the English name for an ISO 3166-1 alpha-2 code. Falls back to the
     * GeoNames-specific supplement and finally to the code itself, so a name is
     * always returned.
     */
    public static function resolveCountryName(string $code): string
    {
        return self::$countryCodes[strtoupper($code)] ?? $code;
    }

    /**
     * Ensure each given country has a row in its4you_countries. Codes that are not
     * present yet are inserted as active, using the supplied English name (falling
     * back to the seed catalog, then the bare code). Existing rows are left intact —
     * names are never overwritten. Used to seed the table at install (from the ISO
     * catalog) and to top it up from GeoNames countryInfo during a postal import.
     *
     * @param array $codeNames map of ISO2 code (any case) => English name
     *
     * @return array<string> upper-case codes that were newly added
     * @throws Exception
     */
    public function ensureCountries(array $codeNames): array
    {
        $this->retrieveDB();
        $table = $this->getTable($this->table, null);

        $existing = [];
        $result = $this->db->pquery('SELECT code FROM ' . $this->table);

        while ($result && $row = $this->db->fetchByAssoc($result)) {
            $existing[strtoupper((string)$row['code'])] = true;
        }

        $added = [];

        foreach ($codeNames as $code => $name) {
            $code = strtoupper((string)$code);

            if ('' === $code || isset($existing[$code])) {
                continue;
            }

            $table->insertData([
                'code'      => $code,
                'name'      => '' !== (string)$name ? $name : self::resolveCountryName($code),
                'is_active' => 1,
            ]);

            $existing[$code] = true;
            $added[] = $code;
        }

        if (!empty($added)) {
            // Invalidate request-level caches so freshly added countries are visible.
            self::$countries = [];
            self::$activeCodes = null;
            self::$phoneFieldConfig = null;
        }

        return $added;
    }

    /**
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->getTable('its4you_countries', 'id')
            ->createTable()
            ->createColumn('code', 'VARCHAR(2)')
            ->createColumn('name', 'VARCHAR(155)')
            ->createColumn('is_active', 'INT(1)')
            ->createKey('UNIQUE KEY IF NOT EXISTS `code` (`code`)');

        // Seed the table from the ISO catalog so it becomes the live source of
        // truth. Idempotent: only missing codes are inserted, existing rows (incl.
        // admin activations) are kept. Non-ISO GeoNames codes (e.g. XK) are added
        // later from countryInfo.txt during the postal-code import.
        $this->ensureCountries(self::$countryCodes);
    }

    public function createLinks(): void
    {
        $link = 'index.php?parent=Settings&module=Country&view=List';
        $name = 'Countries';
        $blockId = getSettingsBlockId('LBL_CONFIGURATION');
        $linkInstance = Settings_Vtiger_MenuItem_Model::getInstanceFromArray([
            'blockid' => $blockId,
            'name'    => $name,
            'linkto'  => $link,
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

    public function deactivateAll()
    {
        $codes = $this->getCodes();
        $countries = [];

        foreach ($codes as $code => $country) {
            $countries[$code] = 0;
        }

        $this->set('countries', $countries);
        $this->save();
    }
}