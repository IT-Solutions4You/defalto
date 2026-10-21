<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_Changelog_Model extends Vtiger_Base_Model
{
    public const MAX_FILE_SIZE = 131072;
    public const MAX_DISPLAYED_VERSIONS = 3;

    /**
     * Parse the restricted customer-facing module changelog format.
     *
     * Version headings use `## <version>` with an optional ` - YYYY-MM-DD`
     * suffix. Customer-visible changes are plain `- <description>` items.
     *
     * @return array<int, array{version: string, date: string, changes: array<string>}>
     */
    public static function getEntries(string $source, string $packageVersion): array
    {
        if (strlen($source) > self::MAX_FILE_SIZE) {
            throw new RuntimeException('Extension changelog exceeds the maximum allowed size');
        }

        if (1 !== preg_match('//u', $source)) {
            throw new RuntimeException('Extension changelog must use valid UTF-8');
        }

        $entries = [];
        $currentEntry = null;
        $lines = preg_split('/\R/u', $source) ?: [];

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            if (preg_match('/^##\s+([^\s]+)(?:\s+-\s+(\d{4}-\d{2}-\d{2}))?\s*$/u', $trimmedLine, $matches)) {
                if (null !== $currentEntry) {
                    $entries[] = $currentEntry;
                }

                $version = ltrim(trim($matches[1]), 'vV');

                if (!preg_match('/^[0-9]+(?:\.[0-9A-Za-z-]+)+$/', $version)) {
                    throw new RuntimeException('Extension changelog contains an invalid version heading');
                }

                $currentEntry = [
                    'version' => $version,
                    'date' => (string)($matches[2] ?? ''),
                    'changes' => [],
                ];

                continue;
            }

            if (str_starts_with($trimmedLine, '## ')) {
                throw new RuntimeException('Extension changelog contains an invalid version heading');
            }

            if (null !== $currentEntry && preg_match('/^-\s+(.+)$/u', $trimmedLine, $matches)) {
                $change = trim($matches[1]);

                if ('' !== $change) {
                    $currentEntry['changes'][] = $change;
                }
            }
        }

        if (null !== $currentEntry) {
            $entries[] = $currentEntry;
        }

        if (!$entries || !$entries[0]['changes']) {
            throw new RuntimeException('Extension changelog has no customer-visible version changes');
        }

        if ($entries[0]['version'] !== $packageVersion) {
            throw new RuntimeException(
                'Extension changelog version does not match package version: '
                . $entries[0]['version'] . ' != ' . $packageVersion
            );
        }

        $seenVersions = [];
        $previousVersion = null;

        foreach ($entries as $entry) {
            if (!$entry['changes']) {
                throw new RuntimeException('Extension changelog version has no customer-visible changes: '
                    . $entry['version']);
            }

            if (isset($seenVersions[$entry['version']])) {
                throw new RuntimeException('Extension changelog contains a duplicate version: ' . $entry['version']);
            }

            if (null !== $previousVersion && version_compare($previousVersion, $entry['version'], '<')) {
                throw new RuntimeException('Extension changelog versions must be ordered newest first');
            }

            $seenVersions[$entry['version']] = true;
            $previousVersion = $entry['version'];
        }

        return array_slice($entries, 0, self::MAX_DISPLAYED_VERSIONS);
    }
}
