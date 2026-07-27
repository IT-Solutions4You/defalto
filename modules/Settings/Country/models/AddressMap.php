<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Per-module address field mapping.
 *
 * Address blocks are named differently across modules (Accounts/Contacts/Vendors
 * have bill_/ship_ pairs, Leads has a single address) and custom fields (cf_NNN)
 * carry no reliable hint about their role, so we cannot auto-detect which field is
 * the PSČ (zip) and which is the city. Instead an admin maps it explicitly here.
 *
 * One module can hold several address "groups" (e.g. Billing + Shipping). Each
 * group is { label, zip, city, state?, country? } where the values are field ids.
 * Both zip and city are mandatory and act as autocomplete sources in BOTH
 * directions (type a PSČ → get cities, type a city → get PSČ); state/country are
 * optional fields that get filled along with the chosen suggestion.
 *
 * The whole list of groups for a module is stored as a single JSON row keyed by
 * tabid. Field ids (not names) are stored so a field label rename never breaks the
 * mapping; the frontend resolves them back to field names via
 * {@see getMappingWithFieldNames()}.
 */
class Settings_Country_AddressMap_Model extends Core_DatabaseData_Model
{
    public const TABLE = 'df_address_field_map';

    /**
     * Substring that marks an address block label. Standard modules use
     * LBL_ADDRESS_INFORMATION, Vendors uses LBL_VENDOR_ADDRESS_INFORMATION and a
     * custom module may use its own label — but every address block label contains
     * "ADDRESS". (Step 4 will switch this to the dedicated Address blockuitype.)
     */
    public const ADDRESS_BLOCK_TOKEN = 'ADDRESS';

    /**
     * Modules that own an address block but are intentionally not offered for
     * mapping. Users is excluded — its address fields are problematic for this
     * auto-completion (it is not a regular record edit flow).
     */
    public const EXCLUDED_MODULES = ['Users'];

    protected string $table = self::TABLE;
    protected string $tableId = 'tabid';
    protected string $tableName = 'tabid';

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        $instance = new self();
        $instance->retrieveDB();

        return $instance;
    }

    /**
     * Create the mapping table if it does not exist yet. Keyed by tabid with a
     * cascading FK so the row disappears when a module is removed.
     *
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->getTable(self::TABLE, null)
            ->createTable('tabid', 'int(19) NOT NULL')
            ->createColumn('mapping', 'text')
            ->createKey('PRIMARY KEY (tabid)')
            ->createKey('CONSTRAINT fk_df_afm_tabid FOREIGN KEY (tabid) REFERENCES vtiger_tab (tabid) ON DELETE CASCADE');
    }

    /**
     * Modules that expose an address block, i.e. the ones the mapping can target.
     *
     * @return array<int, array{tabid: int, name: string}>
     */
    public function getAddressModules(): array
    {
        $db = $this->getDB();
        $modules = [];

        $excludedPlaceholders = implode(',', array_fill(0, count(self::EXCLUDED_MODULES), '?'));

        $result = $db->pquery(
            'SELECT DISTINCT b.tabid, t.name
               FROM vtiger_blocks b
               INNER JOIN vtiger_tab t ON t.tabid = b.tabid
              WHERE b.blocklabel LIKE ? AND t.presence = 0 AND t.name NOT IN (' . $excludedPlaceholders . ')
              ORDER BY t.name',
            array_merge(['%' . self::ADDRESS_BLOCK_TOKEN . '%'], self::EXCLUDED_MODULES)
        );

        while ($row = $db->fetchByAssoc($result)) {
            $modules[] = [
                'tabid' => (int)$row['tabid'],
                'name' => $row['name'],
            ];
        }

        return $modules;
    }

    /**
     * Active fields of a module's address block — the candidates for the dropdowns.
     *
     * @return array<int, array{id: int, name: string, label: string}>
     */
    public function getAddressFields(string $moduleName): array
    {
        $fields = [];

        if ('' === trim($moduleName) || in_array($moduleName, self::EXCLUDED_MODULES, true)) {
            return $fields;
        }

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        if (!$moduleModel) {
            return $fields;
        }

        // Collect the fields of every address block (a module may have more than one).
        foreach ($moduleModel->getBlocks() as $blockLabel => $blockModel) {
            if (false === stripos((string)$blockLabel, self::ADDRESS_BLOCK_TOKEN)) {
                continue;
            }

            foreach ($blockModel->getFields() as $fieldModel) {
                // Skip disabled fields (presence 1); 0 and 2 are active.
                if (1 === (int)$fieldModel->get('presence')) {
                    continue;
                }

                $fields[] = [
                    'id' => (int)$fieldModel->getId(),
                    'name' => $fieldModel->getName(),
                    'label' => vtranslate($fieldModel->get('label'), $moduleName),
                ];
            }
        }

        return $fields;
    }

    /**
     * Raw saved groups for a module (field ids), or [] if none configured.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMapping(string $moduleName): array
    {
        $tabid = getTabid($moduleName);

        if (empty($tabid) || !Vtiger_Utils::CheckTable(self::TABLE)) {
            return [];
        }

        $db = $this->getDB();
        $result = $db->pquery('SELECT mapping FROM ' . self::TABLE . ' WHERE tabid = ?', [$tabid]);

        if (!$db->num_rows($result)) {
            return [];
        }

        $raw = (string)$db->query_result($result, 0, 'mapping');
        $decoded = json_decode($raw, true);

        // Defensive: tolerate html-encoded storage from older write paths.
        if (!is_array($decoded)) {
            $decoded = json_decode(decode_html($raw), true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Everything the admin UI needs for one module: the selectable fields plus the
     * currently saved groups.
     *
     * @return array{fields: array, groups: array}
     */
    public function getModuleConfig(string $moduleName): array
    {
        return [
            'fields' => $this->getAddressFields($moduleName),
            'groups' => $this->getMapping($moduleName),
        ];
    }

    /**
     * Persist the groups for a module. Field ids are whitelisted against the
     * module's actual address block, zip+city are mandatory, state/country optional.
     * Saving an empty set clears the module's mapping.
     *
     * @param array<int, array<string, mixed>> $groups
     *
     * @return array<int, array<string, mixed>> the cleaned groups that were stored
     * @throws Exception
     */
    public function saveMapping(string $moduleName, array $groups): array
    {
        $tabid = getTabid($moduleName);

        if (empty($tabid)) {
            throw new Exception('Unknown module for address mapping: ' . $moduleName);
        }

        $this->retrieveDB();

        // Bail out BEFORE running any SQL if the feature's table is missing (e.g. the
        // database upgrade has not been run yet). Otherwise the DB layer prints an
        // ADODB warning ahead of the JSON response, and pquery() — which returns false
        // instead of throwing — would let the caller wrongly report success.
        if (!Vtiger_Utils::CheckTable(self::TABLE)) {
            throw new Exception('The address field mapping is not installed yet - please run the database upgrade.');
        }

        $allowed = [];

        foreach ($this->getAddressFields($moduleName) as $field) {
            $allowed[(int)$field['id']] = true;
        }

        $clean = [];

        foreach ($groups as $group) {
            $zip = isset($group['zip']) ? (int)$group['zip'] : 0;
            $city = isset($group['city']) ? (int)$group['city'] : 0;

            // zip + city are mandatory and must belong to the address block.
            if (empty($allowed[$zip]) || empty($allowed[$city])) {
                continue;
            }

            $state = isset($group['state']) ? (int)$group['state'] : 0;
            $country = isset($group['country']) ? (int)$group['country'] : 0;

            $clean[] = [
                'label' => isset($group['label']) ? trim((string)$group['label']) : '',
                'zip' => $zip,
                'city' => $city,
                'state' => !empty($allowed[$state]) ? $state : null,
                'country' => !empty($allowed[$country]) ? $country : null,
            ];
        }

        $db = $this->getDB();

        if (empty($clean)) {
            $result = $db->pquery('DELETE FROM ' . self::TABLE . ' WHERE tabid = ?', [$tabid]);
        } else {
            $result = $db->pquery('REPLACE INTO ' . self::TABLE . ' (tabid, mapping) VALUES (?, ?)', [$tabid, json_encode($clean)]);
        }

        // pquery() returns false (it does not throw) when the statement fails — surface
        // it so the caller reports a truthful failure instead of a false success.
        if (false === $result) {
            throw new Exception('Failed to save the address field mapping.');
        }

        return $clean;
    }

    /**
     * Best-effort default mapping for a single module. Used at install time and
     * re-usable from a custom module's installer (e.g. ProformaInvoice, DeliveryNote)
     * to auto-wire its address block:
     *
     *   Settings_Country_AddressMap_Model::setDefaultMapping('DeliveryNote');
     *
     * Defaults are derived from the address-block field naming convention
     * (bill_/ship_, mailing/other, or a single unprefixed address); each detected
     * group gets zip + city and, when present, state + country. An already-configured
     * module is left untouched unless $force is true.
     *
     * @return array<int, array<string, mixed>> the groups that were stored
     * @throws Exception
     */
    public static function setDefaultMapping(string $moduleName, bool $force = false): array
    {
        if (in_array($moduleName, self::EXCLUDED_MODULES, true)) {
            return [];
        }

        $instance = self::getInstance();
        $existing = $instance->getMapping($moduleName);

        // Never clobber an existing (possibly admin-tuned) mapping unless forced.
        if (!$force && !empty($existing)) {
            return $existing;
        }

        $groups = $instance->buildDefaultGroups($moduleName);

        if (empty($groups)) {
            return [];
        }

        return $instance->saveMapping($moduleName, $groups);
    }

    /**
     * Seed default mappings for every address-bearing standard module. Called once
     * at install/upgrade; idempotent (skips modules already configured).
     *
     * @throws Exception
     */
    public static function setDefaultMappingForAll(bool $force = false): void
    {
        $instance = self::getInstance();

        foreach ($instance->getAddressModules() as $module) {
            self::setDefaultMapping($module['name'], $force);
        }
    }

    /**
     * Build default address groups for a module from its address-block field names.
     * Fields are grouped by the prefix in front of the role keyword (bill_, ship_,
     * mailing, other, or none), so a billing/shipping pair becomes two groups.
     *
     * @return array<int, array<string, mixed>>
     */
    public function buildDefaultGroups(string $moduleName): array
    {
        $labels = ['bill' => 'Billing', 'ship' => 'Shipping', 'mailing' => 'Mailing', 'other' => 'Other'];

        $byPrefix = [];
        $order = [];

        foreach ($this->getAddressFields($moduleName) as $field) {
            $info = self::classifyAddressField($field['name']);

            if (null === $info) {
                continue;
            }

            $prefix = $info['prefix'];

            if (!isset($byPrefix[$prefix])) {
                $byPrefix[$prefix] = [
                    'label' => '' === $prefix ? '' : ($labels[$prefix] ?? ucfirst($prefix)),
                    'zip' => null,
                    'city' => null,
                    'state' => null,
                    'country' => null,
                ];
                $order[] = $prefix;
            }

            // First field wins per role (address blocks list their fields in order).
            if (null === $byPrefix[$prefix][$info['role']]) {
                $byPrefix[$prefix][$info['role']] = (int)$field['id'];
            }
        }

        $groups = [];

        foreach ($order as $prefix) {
            $group = $byPrefix[$prefix];

            // zip + city are mandatory for a usable group.
            if (empty($group['zip']) || empty($group['city'])) {
                continue;
            }

            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * Classify an address field by its name into a role (zip/city/state/country) and
     * the prefix preceding the role keyword (used to separate billing/shipping/
     * mailing/other addresses). Returns null for non-address-role fields (street,
     * pobox, lane…). This is a naming heuristic used ONLY to seed sensible defaults —
     * runtime behaviour relies on the explicit admin mapping, never on field names.
     *
     * @return array{role: string, prefix: string}|null
     */
    public static function classifyAddressField(string $fieldName): ?array
    {
        $name = strtolower($fieldName);

        // Order matters: country is checked before zip so e.g. "country_code" is
        // recognised as a country field, not a postal-code field.
        $roles = [
            'country' => ['country'],
            'city' => ['city'],
            'state' => ['state', 'province', 'region', 'county'],
            'zip' => ['postal', 'zip', 'code', 'psc'],
        ];

        foreach ($roles as $role => $keywords) {
            foreach ($keywords as $keyword) {
                $pos = strpos($name, $keyword);

                if (false !== $pos) {
                    return [
                        'role' => $role,
                        'prefix' => rtrim(substr($name, 0, $pos), '_- '),
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Same as {@see getMapping()} but with field ids resolved to field names, ready
     * for the frontend autocomplete (which addresses DOM inputs by name). Groups
     * whose zip or city field no longer exists are dropped.
     *
     * @return array<int, array{label: string, zip: string, city: string, state: ?string, country: ?string}>
     */
    public function getMappingWithFieldNames(string $moduleName): array
    {
        $groups = $this->getMapping($moduleName);

        if (empty($groups)) {
            return [];
        }

        $idToName = [];

        foreach ($this->getAddressFields($moduleName) as $field) {
            $idToName[(int)$field['id']] = $field['name'];
        }

        $resolved = [];

        foreach ($groups as $group) {
            $zip = $idToName[(int)($group['zip'] ?? 0)] ?? null;
            $city = $idToName[(int)($group['city'] ?? 0)] ?? null;

            if (!$zip || !$city) {
                continue;
            }

            $state = !empty($group['state']) ? ($idToName[(int)$group['state']] ?? null) : null;
            $country = !empty($group['country']) ? ($idToName[(int)$group['country']] ?? null) : null;

            $resolved[] = [
                'label' => (string)($group['label'] ?? ''),
                'zip' => $zip,
                'city' => $city,
                'state' => $state,
                'country' => $country,
            ];
        }

        return $resolved;
    }
}
