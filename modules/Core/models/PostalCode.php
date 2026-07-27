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
 * Read interface over the local postal-code dataset (`df_postalcodes`, populated by
 * Settings_PostalCodes_Record_Model from GeoNames). Powers the address auto-completion:
 * a postal code OR a city name (both directions) maps to the matching place / PSČ
 * plus its region (admin_name1) and country.
 *
 * Reference data only — this is a pure reader; the table itself is owned/created by
 * Settings_PostalCodes_Record_Model.
 */
class Core_PostalCode_Model
{
    /** Same table as Settings_PostalCodes_Record_Model::TABLE (kept here to avoid a Settings dependency from Core). */
    public const TABLE = 'df_postalcodes';

    protected const DEFAULT_LIMIT = 20;
    protected const MAX_LIMIT = 50;

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new self();
    }

    /**
     * Prefix-search the postal dataset by postal code or by city name.
     *
     * @param string $countryCode ISO 3166-1 alpha-2; '' searches across all countries
     *                            (slower on a worldwide table — pass a country when known).
     * @param string $term        what the user is typing
     * @param string $by          'zip' | 'city'; anything else is inferred from $term
     * @param int    $limit       max rows (defaults to 20, capped at 50)
     *
     * @return array<int, array{postal_code: string, place_name: string, admin_name1: ?string, admin_name2: ?string, country_code: string}>
     */
    public function search(string $countryCode, string $term, string $by = '', int $limit = 0): array
    {
        $term = trim($term);

        if ('' === $term || !Vtiger_Utils::CheckTable(self::TABLE)) {
            return [];
        }

        $by = strtolower($by);

        if (!in_array($by, ['zip', 'city'], true)) {
            // Heuristic fallback: a term starting with a digit is most likely a PSČ.
            $by = preg_match('~^\d~', $term) ? 'zip' : 'city';
        }

        $isZip = 'zip' === $by;
        $column = $isZip ? 'postal_code' : 'place_name';

        // For postal codes, ignore spaces on BOTH sides so a user typing "08001"
        // still matches a stored "080 01" (and vice versa). City names keep their
        // spaces — there they are meaningful. Stripping spaces from the column
        // defeats the postal_code index, but a country-filtered scan is small.
        if ($isZip) {
            $term = str_replace(' ', '', $term);
            $searchExpr = "REPLACE(" . $column . ", ' ', '')";
        } else {
            $searchExpr = $column;
        }

        if ($limit <= 0) {
            $limit = self::DEFAULT_LIMIT;
        }

        $limit = min($limit, self::MAX_LIMIT);

        // Escape LIKE wildcards so the user's input is matched literally; prefix match.
        $like = strtr($term, ['\\' => '\\\\', '%' => '\\%', '_' => '\\_']) . '%';

        $params = [];
        $where = '';
        $countryCode = strtoupper(trim($countryCode));

        if ('' !== $countryCode) {
            $where .= 'country_code = ? AND ';
            $params[] = $countryCode;
        }

        $where .= $searchExpr . ' LIKE ?';
        $params[] = $like;

        $sql = 'SELECT DISTINCT postal_code, place_name, admin_name1, admin_name2, country_code'
            . ' FROM ' . self::TABLE
            . ' WHERE ' . $where
            . ' ORDER BY ' . $column . ', place_name'
            . ' LIMIT ' . (int)$limit;

        $db = PearDatabase::getInstance();
        $result = $db->pquery($sql, $params);

        $rows = [];

        while ($result && $row = $db->fetchByAssoc($result)) {
            // Place/region names can be stored HTML-entity-encoded (e.g. "Mal&yacute;
            // &Scaron;ari&scaron;"); decode so the API returns clean UTF-8 that the
            // autocomplete both displays and writes into the record verbatim.
            $rows[] = [
                'postal_code' => $row['postal_code'],
                'place_name' => decode_html($row['place_name']),
                'admin_name1' => null !== $row['admin_name1'] ? decode_html($row['admin_name1']) : null,
                'admin_name2' => null !== $row['admin_name2'] ? decode_html($row['admin_name2']) : null,
                'country_code' => $row['country_code'],
            ];
        }

        return $rows;
    }
}
