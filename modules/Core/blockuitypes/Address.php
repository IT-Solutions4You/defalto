<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Address_BlockUIType extends Vtiger_Base_Model implements Core_Interface_BlockUIType
{
    /**
     * @inheritDoc
     */
    public function getTemplateName(): string
    {
        return 'blockuitypes/AddressEdit.tpl';
    }

    /**
     * @inheritDoc
     */
    public function getDetailViewTemplateName(): string
    {
        return 'blockuitypes/Address.tpl';
    }

    /**
     * Resolved address-map groups for a module, or [] when nothing is mapped (the
     * templates fall back to the standard block rendering in that case). Convenience
     * wrapper so the templates only ever touch this class.
     *
     * @param string $moduleName
     *
     * @return array<int, array{label: string, zip: string, city: string, state: ?string, country: ?string}>
     * @throws Exception
     */
    public static function getAddressGroups(string $moduleName): array
    {
        return Settings_Country_AddressMap_Model::getInstance()->getMappingWithFieldNames($moduleName);
    }

    /**
     * The address-map groups of a module resolved against one record, with the
     * display values needed by the detail template. Street is derived here (it is not
     * part of the mapping): the block's remaining text fields that share the group's
     * field-name prefix. The template only arranges these parts into the final line,
     * so the address format itself stays in the tpl.
     *
     * @param string $moduleName
     * @param Vtiger_Record_Model $record
     *
     *
     * @return array<int, array{index: int, label: string, street: string, zip: string, city: string, state: string, country: string}>
     * @throws Exception
     */
    public static function getFormattedGroups(string $moduleName, Vtiger_Record_Model $record): array
    {
        $groups = self::getAddressGroups($moduleName);

        if (empty($groups)) {
            return [];
        }

        // Field names used as an explicit role in ANY group — excluded from street.
        $roleFields = [];

        foreach ($groups as $group) {
            foreach (['zip', 'city', 'state', 'country'] as $role) {
                if (!empty($group[$role])) {
                    $roleFields[$group[$role]] = true;
                }
            }
        }

        $streetCandidates = self::getStreetCandidates($moduleName, $roleFields);

        $formatted = [];

        foreach ($groups as $index => $group) {
            $prefix = self::getGroupPrefix($group);
            $streetParts = [];

            foreach ($streetCandidates as $fieldName => $unused) {
                if ('' !== $prefix && 0 !== strncmp($fieldName, $prefix, strlen($prefix))) {
                    continue;
                }

                $value = trim((string)$record->getDisplayValue($fieldName));

                if ('' !== $value) {
                    $streetParts[] = $value;
                }
            }

            $formatted[] = [
                'index' => $index,
                'label' => self::getDisplayLabel((string)$group['label'], $moduleName),
                'street' => implode(', ', $streetParts),
                'zip' => trim((string)$record->getDisplayValue($group['zip'])),
                'city' => trim((string)$record->getDisplayValue($group['city'])),
                'state' => !empty($group['state']) ? trim((string)$record->getDisplayValue($group['state'])) : '',
                'country' => !empty($group['country']) ? trim((string)$record->getDisplayValue($group['country'])) : '',
            ];
        }

        return $formatted;
    }

    /**
     * The address groups of a module resolved to the ordered list of editable field
     * names that make up each group — used by the detail template to pre-render a
     * per-group inline edit form (Part F). Order mirrors the display format: street
     * field(s), zip, city, state, country. Each field carries its role so the JS can
     * rebuild the formatted line after an inline save; `map` is the group in the
     * shape Part E's autocomplete expects (`{label, zip, city, state, country}`).
     *
     * Keyed by the same group index as {@see getFormattedGroups()} so the template
     * can pair a formatted line with its editor.
     *
     * @param string $moduleName
     *
     *
     * @return array<int, array{index: int, label: string, map: array, fields: array<int, array{name: string, role: string}>}>
     * @throws Exception
     */
    public static function getEditGroups(string $moduleName): array
    {
        $groups = self::getAddressGroups($moduleName);

        if (empty($groups)) {
            return [];
        }

        $roleFields = [];

        foreach ($groups as $group) {
            foreach (['zip', 'city', 'state', 'country'] as $role) {
                if (!empty($group[$role])) {
                    $roleFields[$group[$role]] = true;
                }
            }
        }

        $streetCandidates = self::getStreetCandidates($moduleName, $roleFields);
        $orderedNames = self::getOrderedAddressFieldNames($moduleName);
        $result = [];

        foreach ($groups as $index => $group) {
            $prefix = self::getGroupPrefix($group);

            // The group's mapped role fields, keyed by name so we can classify while
            // walking the block in its native field sequence.
            $roleByName = [];

            foreach (['zip', 'city', 'state', 'country'] as $role) {
                if (!empty($group[$role])) {
                    $roleByName[$group[$role]] = $role;
                }
            }

            // Walk the address block fields in their configured sequence so the inline
            // editor lists them in the same order as the standard edit view.
            $fields = [];

            foreach ($orderedNames as $fieldName) {
                if (isset($roleByName[$fieldName])) {
                    $fields[] = ['name' => $fieldName, 'role' => $roleByName[$fieldName]];
                } elseif (isset($streetCandidates[$fieldName])) {
                    if ('' !== $prefix && 0 !== strncmp($fieldName, $prefix, strlen($prefix))) {
                        continue;
                    }

                    $fields[] = ['name' => $fieldName, 'role' => 'street'];
                }
            }

            $result[$index] = [
                'index' => $index,
                'label' => self::getDisplayLabel((string)$group['label'], $moduleName),
                'map' => [
                    'label' => (string)$group['label'],
                    'zip' => $group['zip'],
                    'city' => $group['city'],
                    'state' => !empty($group['state']) ? $group['state'] : null,
                    'country' => !empty($group['country']) ? $group['country'] : null,
                ],
                'fields' => $fields,
            ];
        }

        return $result;
    }

    /**
     * Rendering plan for the summary / key-fields widget. When address-block fields
     * are configured as key fields, we don't list them one by one — we show the whole
     * formatted address instead, exactly like the detail view, and only for the
     * address group(s) actually present in the key fields (e.g. if only Billing fields
     * are key fields, Shipping is not shown).
     *
     * Given the key fields in display order, returns:
     *  - render: keyed by the FIRST key-field name of each present group -> that
     *    group's formatted address (the entry to render at that position);
     *  - skip: the other address field names of present groups -> true (their value
     *    is folded into the group address and must not be rendered individually).
     *
     * Non-address fields are absent from both maps and render normally.
     *
     * @param string $moduleName
     * @param Vtiger_Record_Model $record
     * @param array<string, mixed> $summaryFields  key fields keyed by field name, in display order
     *
     * @return array{render: array<string, array>, skip: array<string, bool>}
     * @throws Exception
     */
    public static function getSummaryAddressPlan(string $moduleName, Vtiger_Record_Model $record, array $summaryFields): array
    {
        $plan = ['render' => [], 'skip' => []];

        $editGroups = self::getEditGroups($moduleName);

        if (empty($editGroups)) {
            return $plan;
        }

        // field name -> group index
        $fieldToGroup = [];

        foreach ($editGroups as $index => $group) {
            foreach ($group['fields'] as $field) {
                $fieldToGroup[$field['name']] = $index;
            }
        }

        $formatted = self::getFormattedGroups($moduleName, $record);
        $triggered = [];

        foreach (array_keys($summaryFields) as $fieldName) {
            if (!isset($fieldToGroup[$fieldName])) {
                continue;
            }

            $index = $fieldToGroup[$fieldName];

            if (!isset($triggered[$index])) {
                // First key field of this group — render the whole address here,
                // provided we actually resolved the group's values.
                if (isset($formatted[$index])) {
                    $triggered[$index] = true;
                    $plan['render'][$fieldName] = $formatted[$index];
                }
            } else {
                // Another field of an already-triggered group — fold it in.
                $plan['skip'][$fieldName] = true;
            }
        }

        return $plan;
    }

    /**
     * Address-block field models (keyed by field name) in EDIT mode — i.e. carrying
     * the record's current values, ready to render the inline edit widgets. Used by
     * the key-fields summary, which (unlike the detail view) has no pre-built edit
     * structure of its own to feed the shared inline-edit form.
     *
     * @param string $moduleName
     * @param Vtiger_Record_Model $record
     *
     * @return array<string, Vtiger_Field_Model>
     * @throws Exception
     */
    public static function getEditFieldModels(string $moduleName, Vtiger_Record_Model $record): array
    {
        $models = [];
        $structure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($record, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT)->getStructure();

        foreach ($structure as $blockLabel => $fields) {
            if (false === stripos((string)$blockLabel, Settings_Country_AddressMap_Model::ADDRESS_BLOCK_TOKEN)) {
                continue;
            }

            foreach ($fields as $fieldName => $fieldModel) {
                $models[$fieldName] = $fieldModel;
            }
        }

        return $models;
    }

    /**
     * User-facing label for an address group. The raw label comes from the admin
     * mapping (e.g. "Billing", "Shipping", or empty for a single unprefixed address)
     * and is stored untranslated, so resolve it for display:
     *   - empty     -> vtranslate('Address') (generic single address);
     *   - "X"       -> vtranslate('X Address') when that key is translated (e.g.
     *                  "Billing Address" -> "Fakturačná adresa");
     *   - otherwise -> vtranslate('X') so a bare translation still applies, falling
     *                  back to the raw text when there is none.
     *
     * @param string $rawLabel
     * @param string $moduleName
     *
     * @return string
     */
    public static function getDisplayLabel(string $rawLabel, string $moduleName): string
    {
        $rawLabel = trim($rawLabel);

        if ('' === $rawLabel) {
            return vtranslate('Address', $moduleName);
        }

        $withSuffix = $rawLabel . ' Address';
        $translated = vtranslate($withSuffix, $moduleName);

        if ($translated !== $withSuffix) {
            return $translated;
        }

        return vtranslate($rawLabel, $moduleName);
    }

    /**
     * Field-name prefix (bill/ship/mailing/… or '') that identifies a group, derived
     * from its zip (fallback city) field so street fields of the same group can be
     * matched by name.
     *
     * @param array<string, mixed> $group
     *
     * @return string
     */
    protected static function getGroupPrefix(array $group): string
    {
        foreach (['zip', 'city'] as $role) {
            $info = Settings_Country_AddressMap_Model::classifyAddressField((string)($group[$role] ?? ''));

            if (null !== $info) {
                return $info['prefix'];
            }
        }

        return '';
    }

    /**
     * Active field names of a module's address block(s) in their configured sequence.
     * Used to order the inline editor's fields the same way the standard edit view does.
     *
     * @param string $moduleName
     *
     * @return array<int, string>
     * @throws Exception
     */
    protected static function getOrderedAddressFieldNames(string $moduleName): array
    {
        $names = [];
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        if (!$moduleModel) {
            return $names;
        }

        foreach ($moduleModel->getBlocks() as $blockLabel => $blockModel) {
            if (false === stripos((string)$blockLabel, Settings_Country_AddressMap_Model::ADDRESS_BLOCK_TOKEN)) {
                continue;
            }

            foreach ($blockModel->getFields() as $fieldModel) {
                if (1 === (int)$fieldModel->get('presence')) {
                    continue;
                }

                $names[] = $fieldModel->getName();
            }
        }

        return $names;
    }

    /**
     * Text fields of a module's address block(s) that are candidates for the street
     * line: string/text type, not already used as a mapped role, and not disabled.
     * Keyed by field name, in block sequence order.
     *
     * @param string $moduleName
     * @param array<string, bool> $roleFields
     *
     * @return array<string, bool>
     * @throws Exception
     */
    protected static function getStreetCandidates(string $moduleName, array $roleFields): array
    {
        $candidates = [];
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        if (!$moduleModel) {
            return $candidates;
        }

        foreach ($moduleModel->getBlocks() as $blockLabel => $blockModel) {
            if (false === stripos((string)$blockLabel, Settings_Country_AddressMap_Model::ADDRESS_BLOCK_TOKEN)) {
                continue;
            }

            foreach ($blockModel->getFields() as $fieldModel) {
                $fieldName = $fieldModel->getName();

                if (isset($roleFields[$fieldName]) || 1 === (int)$fieldModel->get('presence')) {
                    continue;
                }

                if (!in_array($fieldModel->getFieldDataType(), ['string', 'text'], true)) {
                    continue;
                }

                $candidates[$fieldName] = true;
            }
        }

        return $candidates;
    }
}
