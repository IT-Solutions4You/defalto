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
 * Fetches the postal-code dataset from GeoNames and imports it.
 *
 * Downloads the postal archive (allCountries.zip, or a single-country file such as
 * SK.zip) plus countryInfo.txt, extracts them into a working directory under
 * cache/country/, then hands them to Settings_Country_Data_Model for the actual
 * DB import. Used by both the manual "Update now" action and the cron task.
 */
class Settings_Country_Update_Model
{
    /**
     * Our own GeoNames mirror — the default source for all installs. Refreshed
     * quarterly from GeoNames by a server-side job (runs on geonames.defalto.online),
     * so installs fetch from us instead of hammering GeoNames directly and are
     * unaffected if GeoNames changes its URLs. The dedicated subdomain's docroot
     * serves the published export dir, so files sit at the base directly, keeping
     * the GeoNames layout: {base}zip/{CODE}.zip and {base}dump/countryInfo.txt.
     */
    public const MIRROR_SOURCE_BASE = 'https://geonames.defalto.online/';

    /**
     * Upstream GeoNames. Always tried last, as an automatic fallback if the mirror
     * (and any configured override) is unreachable, so a mirror outage never blocks
     * an install's postal-code update.
     */
    public const GEONAMES_SOURCE_BASE = 'https://download.geonames.org/export/';

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * Entry point for the cron task: import the full worldwide dataset.
     *
     * @throws Exception
     */
    public static function runCron(): void
    {
        self::getInstance()->run();
    }

    /**
     * Download, extract and import the GeoNames postal dataset.
     *
     * @param string $countryCode optional ISO2 code to import a single country
     *                            (e.g. 'SK'); empty imports the worldwide dataset
     * @param string $version     optional dataset version label; defaults to today
     *
     * @return array{version: string, rows: int, countriesAdded: array}
     * @throws Exception
     */
    public function run(string $countryCode = '', string $version = ''): array
    {
        $countryCode = strtoupper(trim($countryCode));
        $basename = '' !== $countryCode ? $countryCode : 'allCountries';
        $version = '' !== $version ? $version : date('Y-m-d');

        $dir = $this->getWorkingDir();
        $zipPath = $dir . $basename . '.zip';
        $countryInfoPath = $dir . 'countryInfo.txt';

        try {
            // 1. Postal codes (city / PSČ / state).
            $this->downloadFromSources('zip/' . $basename . '.zip', $zipPath);
            $postalTxt = $this->extractZip($zipPath, $dir, $basename . '.txt');

            // 2. Country names (English), used to top up its4you_countries.
            $this->downloadFromSources('dump/countryInfo.txt', $countryInfoPath);

            $data = Settings_Country_Data_Model::getInstance();
            $rows = $data->import($postalTxt, $version, 'GeoNames', $countryCode);
            $countriesAdded = $data->importCountryInfo($countryInfoPath);

            return [
                'version'        => $version,
                'rows'           => $rows,
                'countriesAdded' => $countriesAdded,
            ];
        } finally {
            $this->cleanup($dir);
        }
    }

    /**
     * Ordered list of base download URLs, tried in turn until one succeeds:
     *   1. $postal_codes_source_base from config.inc.php (if set) — per-install
     *      override, e.g. a customer behind a firewall pointing elsewhere;
     *   2. our own mirror (the default) — {@see self::MIRROR_SOURCE_BASE};
     *   3. upstream GeoNames — {@see self::GEONAMES_SOURCE_BASE}, the last-resort
     *      fallback so a mirror outage never blocks an update.
     * Each entry is normalised to a trailing slash; duplicates are removed so an
     * override equal to the mirror/GeoNames is not fetched twice.
     *
     * @return array<int, string> non-empty list of base URLs, each ending in '/'
     */
    protected function getSourceBases(): array
    {
        global $postal_codes_source_base;

        $candidates = [];

        if (!empty($postal_codes_source_base)) {
            $candidates[] = (string)$postal_codes_source_base;
        }

        $candidates[] = self::MIRROR_SOURCE_BASE;
        $candidates[] = self::GEONAMES_SOURCE_BASE;

        $bases = [];

        foreach ($candidates as $candidate) {
            $candidate = trim($candidate);

            if ('' === $candidate) {
                continue;
            }

            $candidate = rtrim($candidate, '/') . '/';

            if (!in_array($candidate, $bases, true)) {
                $bases[] = $candidate;
            }
        }

        return $bases;
    }

    /**
     * Download a GeoNames-layout file (e.g. "zip/SK.zip", "dump/countryInfo.txt")
     * trying each configured source in order, falling back to the next on any
     * failure. Per-file fallback is deliberate: a partial mirror (missing a single
     * file) still resolves that file from GeoNames.
     *
     * @param string $relativePath path relative to a source base, no leading slash
     * @param string $destPath     local file to write the download to
     *
     * @return void
     * @throws Exception if every source failed (message aggregates each attempt)
     */
    protected function downloadFromSources(string $relativePath, string $destPath): void
    {
        $relativePath = ltrim($relativePath, '/');
        $errors = [];

        foreach ($this->getSourceBases() as $base) {
            try {
                $this->download($base . $relativePath, $destPath);

                return;
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        throw new Exception(sprintf(
            'All postal-code sources failed for "%s": %s',
            $relativePath,
            implode(' | ', $errors)
        ));
    }

    /**
     * Stream a URL to a local file (low memory, no full buffering).
     *
     * @throws Exception
     */
    protected function download(string $url, string $destPath): void
    {
        $fp = fopen($destPath, 'wb');

        if ($fp === false) {
            throw new Exception('Cannot open file for writing: ' . $destPath);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Defalto-CRM');

        $ok = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if (false === $ok || $httpCode >= 400) {
            @unlink($destPath);
            throw new Exception(sprintf('Download failed (%s, HTTP %d): %s', $url, $httpCode, $error));
        }
    }

    /**
     * Extract a zip archive and return the path of the expected inner file.
     *
     * @throws Exception
     */
    protected function extractZip(string $zipPath, string $destDir, string $innerFile): string
    {
        $zip = new ZipArchive();

        if (true !== $zip->open($zipPath)) {
            throw new Exception('Cannot open zip archive: ' . $zipPath);
        }

        $zip->extractTo($destDir);
        $zip->close();

        $extracted = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $innerFile;

        if (!is_readable($extracted)) {
            throw new Exception('Expected file not found in archive: ' . $innerFile);
        }

        return $extracted;
    }

    /**
     * Working directory for downloads/extraction (cache/country/). Created if needed.
     *
     * @throws Exception
     */
    protected function getWorkingDir(): string
    {
        global $root_directory;

        $base = (!empty($root_directory) ? rtrim($root_directory, '/\\') . DIRECTORY_SEPARATOR : '')
            . 'cache' . DIRECTORY_SEPARATOR . 'country' . DIRECTORY_SEPARATOR;

        if (!is_dir($base) && !mkdir($base, 0755, true) && !is_dir($base)) {
            throw new Exception('Cannot create working directory: ' . $base);
        }

        return $base;
    }

    /**
     * Remove downloaded/extracted files (the dataset can be large), keep the dir.
     */
    protected function cleanup(string $dir): void
    {
        foreach ((array)glob($dir . '*') as $file) {
            if (is_file($file) && 'index.html' !== basename($file)) {
                @unlink($file);
            }
        }
    }
}
