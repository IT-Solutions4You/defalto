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
 * Local postal-code dataset (GeoNames "allCountries" postal file).
 *
 * Holds the worldwide postal-code lookup table plus a single-row meta table that
 * records which dataset version is currently loaded. Imports are done into a
 * staging table and swapped in atomically (RENAME TABLE), so an interrupted or
 * failed import never leaves the live table empty or half-populated.
 *
 * The live table is reference data only (records store the chosen city/PSČ as
 * plain text, no FK points here), therefore the whole table can be replaced safely.
 */
class Settings_Country_Data_Model extends Core_DatabaseData_Model
{
    public const TABLE = 'df_postalcodes';
    public const META = 'df_postalcodes_meta';
    public const STAGING = 'df_postalcodes_import';

    protected const BATCH_SIZE = 2000;

    /**
     * Tab-separated column index in the GeoNames postal file => our column name.
     * (Columns 6,7,8 = admin name3/code3 and 11 = accuracy are intentionally skipped.)
     */
    protected const DATA_COLUMNS = ['country_code', 'postal_code', 'place_name', 'admin_name1', 'admin_code1', 'admin_name2', 'latitude', 'longitude'];

    protected string $table = self::TABLE;
    protected string $tableId = 'id';

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
     * Create the live table + meta table if they do not exist yet.
     *
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->definePostalColumns(self::TABLE);

        $this->getTable(self::META, 'id')
            ->createTable()
            ->createColumn('dataset_version', 'varchar(32) DEFAULT NULL')
            ->createColumn('imported_at', 'datetime DEFAULT NULL')
            ->createColumn('row_count', 'int(11) NOT NULL DEFAULT 0')
            ->createColumn('sha256', 'varchar(64) DEFAULT NULL')
            ->createColumn('source', 'varchar(255) DEFAULT NULL');
    }

    /**
     * Build the postal-code column layout on the given table. Used for both the
     * live table and the staging table so the two are guaranteed identical.
     *
     * @throws Exception
     */
    protected function definePostalColumns(string $table): void
    {
        $this->getTable($table, 'id')
            ->createTable()
            ->createColumn('country_code', "varchar(2) NOT NULL DEFAULT ''")
            ->createColumn('postal_code', "varchar(20) NOT NULL DEFAULT ''")
            ->createColumn('place_name', "varchar(180) NOT NULL DEFAULT ''")
            ->createColumn('admin_name1', 'varchar(100) DEFAULT NULL')
            ->createColumn('admin_code1', 'varchar(20) DEFAULT NULL')
            ->createColumn('admin_name2', 'varchar(100) DEFAULT NULL')
            ->createColumn('latitude', 'decimal(10,6) DEFAULT NULL')
            ->createColumn('longitude', 'decimal(10,6) DEFAULT NULL')
            ->createKey('INDEX IF NOT EXISTS `idx_pc_country_postal` (`country_code`,`postal_code`)')
            ->createKey('INDEX IF NOT EXISTS `idx_pc_country_place` (`country_code`,`place_name`)');
    }

    /**
     * Import a GeoNames "allCountries.txt" (or single-country) postal file.
     *
     * The whole pipeline is idempotent and re-runnable: load into a fresh staging
     * table, then swap it in atomically and record the version in the meta table.
     *
     * @return int number of rows imported
     * @throws Exception
     */
    public function import(string $filePath, string $version, string $source = 'GeoNames', string $countryCode = ''): int
    {
        if (!is_readable($filePath)) {
            throw new Exception('Postal code import file is not readable: ' . $filePath);
        }

        $this->retrieveDB();
        $sha256 = (string)hash_file('sha256', $filePath);

        $this->prepareStaging();
        $rowCount = $this->streamLoad($filePath);

        if ('' === trim($countryCode)) {
            // Whole-world dataset → fast atomic table replace.
            $this->swap();
        } else {
            // Single-country (or partial) dataset → replace only the countries
            // present in the file, leaving every other country untouched.
            $this->mergeFromStaging();
        }

        // row_count in meta reflects the whole table after the import (correct for
        // both a full replace and a per-country merge).
        $this->setMeta($version, $this->countRows(), $sha256, $source);

        return $rowCount;
    }

    /**
     * Top up its4you_countries from a GeoNames countryInfo.txt file: every country
     * it lists that is not already present is added (active, with the English name
     * from the file). Existing rows are left intact. This is how non-ISO GeoNames
     * codes (e.g. XK = Kosovo) get a record + name without touching the seed catalog.
     *
     * @return array<string> codes newly added to its4you_countries
     * @throws Exception
     */
    public function importCountryInfo(string $filePath): array
    {
        if (!is_readable($filePath)) {
            throw new Exception('countryInfo file is not readable: ' . $filePath);
        }

        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw new Exception('Cannot open countryInfo file: ' . $filePath);
        }

        $codeNames = [];

        while (($line = fgets($handle)) !== false) {
            // Skip comment header lines and blanks.
            if ('' === $line || '#' === $line[0]) {
                continue;
            }

            $c = explode("\t", rtrim($line, "\r\n"));

            // col 0 = ISO2 code, col 4 = English country name.
            if (count($c) < 5) {
                continue;
            }

            $code = strtoupper(trim($c[0]));

            if ('' !== $code) {
                $codeNames[$code] = trim($c[4]);
            }
        }

        fclose($handle);

        return Core_Country_Model::getInstance()->ensureCountries($codeNames);
    }

    /**
     * Drop any leftover staging table and recreate it empty with the live layout.
     *
     * @throws Exception
     */
    protected function prepareStaging(): void
    {
        $this->getDB()->query('DROP TABLE IF EXISTS ' . self::STAGING);
        $this->definePostalColumns(self::STAGING);
    }

    /**
     * Stream the file line by line (never loads the whole file into memory) and
     * insert into the staging table in batches.
     *
     * @throws Exception
     */
    protected function streamLoad(string $filePath): int
    {
        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw new Exception('Cannot open postal code file: ' . $filePath);
        }

        $batch = [];
        $count = 0;

        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");

            if ($line === '') {
                continue;
            }

            $c = explode("\t", $line);

            // A valid postal row needs at least up to longitude (index 10).
            if (count($c) < 11) {
                continue;
            }

            $batch[] = [
                strtoupper(substr($c[0], 0, 2)),
                substr($c[1], 0, 20),
                substr($c[2], 0, 180),
                $c[3] !== '' ? substr($c[3], 0, 100) : null,
                $c[4] !== '' ? substr($c[4], 0, 20) : null,
                $c[5] !== '' ? substr($c[5], 0, 100) : null,
                $c[9] !== '' ? (float)$c[9] : null,
                $c[10] !== '' ? (float)$c[10] : null,
            ];

            if (count($batch) >= self::BATCH_SIZE) {
                $this->insertBatch($batch);
                $count += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->insertBatch($batch);
            $count += count($batch);
        }

        fclose($handle);

        return $count;
    }

    /**
     * Insert a batch of rows into the staging table with a single multi-row INSERT.
     *
     * @param array $rows list of value-arrays ordered as self::DATA_COLUMNS
     */
    protected function insertBatch(array $rows): void
    {
        $rowPlaceholder = '(' . implode(',', array_fill(0, count(self::DATA_COLUMNS), '?')) . ')';
        $placeholders = implode(',', array_fill(0, count($rows), $rowPlaceholder));

        $params = [];

        foreach ($rows as $row) {
            foreach ($row as $value) {
                $params[] = $value;
            }
        }

        $sql = 'INSERT INTO ' . self::STAGING . ' (' . implode(',', self::DATA_COLUMNS) . ') VALUES ' . $placeholders;
        $this->getDB()->pquery($sql, $params);
    }

    /**
     * Atomically swap the freshly populated staging table in for the live one.
     * RENAME TABLE with both renames in one statement is atomic in MySQL, so there
     * is no window where the live table is missing or empty.
     */
    protected function swap(): void
    {
        $db = $this->getDB();
        $old = self::TABLE . '_old';

        $db->query('DROP TABLE IF EXISTS ' . $old);
        $db->query('RENAME TABLE ' . self::TABLE . ' TO ' . $old . ', ' . self::STAGING . ' TO ' . self::TABLE);
        $db->query('DROP TABLE IF EXISTS ' . $old);
    }

    /**
     * Merge the staging table into the live table for a per-country import: delete
     * only the rows of the countries actually present in the staged file, then copy
     * the staged rows in. All other countries are left intact. Done in a transaction
     * so the live table is never left with a country removed but not re-inserted.
     *
     * @throws Exception
     */
    protected function mergeFromStaging(): void
    {
        $db = $this->getDB();

        $codes = [];
        $result = $db->query('SELECT DISTINCT country_code FROM ' . self::STAGING);

        while ($result && $row = $db->fetchByAssoc($result)) {
            if ('' !== (string)$row['country_code']) {
                $codes[] = $row['country_code'];
            }
        }

        if (empty($codes)) {
            $db->query('DROP TABLE IF EXISTS ' . self::STAGING);

            return;
        }

        $columns = implode(',', self::DATA_COLUMNS);
        $placeholders = implode(',', array_fill(0, count($codes), '?'));

        $db->query('START TRANSACTION');

        try {
            $db->pquery('DELETE FROM ' . self::TABLE . ' WHERE country_code IN (' . $placeholders . ')', $codes);
            $db->query('INSERT INTO ' . self::TABLE . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . self::STAGING);
            $db->query('COMMIT');
        } catch (Exception $e) {
            $db->query('ROLLBACK');

            throw $e;
        } finally {
            $db->query('DROP TABLE IF EXISTS ' . self::STAGING);
        }
    }

    /**
     * @return int total number of rows currently in the live postal-code table
     */
    protected function countRows(): int
    {
        $db = $this->getDB();
        $result = $db->query('SELECT COUNT(*) AS cnt FROM ' . self::TABLE);

        return (int)$db->query_result($result, 0, 'cnt');
    }

    /**
     * Record which dataset version is now live (single row, id = 1).
     */
    public function setMeta(string $version, int $rowCount, string $sha256 = '', string $source = 'GeoNames'): void
    {
        $this->retrieveDB();
        $db = $this->getDB();
        $db->pquery('DELETE FROM ' . self::META . ' WHERE id = ?', [1]);
        $db->pquery(
            'INSERT INTO ' . self::META . ' (id, dataset_version, imported_at, row_count, sha256, source) VALUES (?,?,?,?,?,?)',
            [1, $version, date('Y-m-d H:i:s'), $rowCount, $sha256, $source]
        );
    }

    /**
     * @return array|null currently loaded dataset info, or null if nothing imported yet
     */
    public function getMeta(): ?array
    {
        $this->retrieveDB();

        if (!Vtiger_Utils::CheckTable(self::META)) {
            return null;
        }

        $db = $this->getDB();
        $result = $db->pquery('SELECT * FROM ' . self::META . ' WHERE id = ?', [1]);

        if (!$db->num_rows($result)) {
            return null;
        }

        return $db->fetchByAssoc($result);
    }

    /**
     * Upper-case ISO2 codes of the countries that currently have postal data, so the
     * UI can flag which countries are already downloaded.
     *
     * @return array<string>
     */
    public function getImportedCountryCodes(): array
    {
        $this->retrieveDB();

        if (!Vtiger_Utils::CheckTable(self::TABLE)) {
            return [];
        }

        $codes = [];
        $db = $this->getDB();
        $result = $db->query('SELECT DISTINCT country_code FROM ' . self::TABLE);

        while ($result && $row = $db->fetchByAssoc($result)) {
            $code = strtoupper((string)$row['country_code']);

            if ('' !== $code) {
                $codes[] = $code;
            }
        }

        return $codes;
    }
}
