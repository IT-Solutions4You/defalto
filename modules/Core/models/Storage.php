<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Storage_Model extends Vtiger_Base_Model
{
    public const DEFAULT_DIRECTORY = 'storage/';
    public const DEFAULT_TEMPORARY_DIRECTORY = 'cache/images/';

    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * Return the configured root for persisted attachments.
     */
    public function getAttachmentsDirectory(): string
    {
        $directory = trim((string)vglobal('storage_directory'));

        return $directory !== '' ? $directory : self::DEFAULT_DIRECTORY;
    }

    /**
     * Create and return the dated directory for a new attachment.
     *
     * @throws Exception
     */
    public function initAttachmentFileDirectory(): string
    {
        $week = 'week' . min(5, (int)ceil((int)date('j') / 7));
        $directory = rtrim($this->getAttachmentsDirectory(), '/\\')
            . DIRECTORY_SEPARATOR
            . date('Y')
            . DIRECTORY_SEPARATOR
            . date('F')
            . DIRECTORY_SEPARATOR
            . $week
            . DIRECTORY_SEPARATOR;
        $directory = $this->normalizeDirectory($directory);

        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new Exception(sprintf('Unable to create document storage directory: %s', $directory));
        }

        return $directory;
    }

    /**
     * Create and return the application directory for temporary generated files.
     *
     * @throws Exception
     */
    public function initTemporaryFileDirectory(): string
    {
        $directory = trim((string)vglobal('tmp_dir'));

        if ($directory === '') {
            $directory = self::DEFAULT_TEMPORARY_DIRECTORY;
        }

        $directory = $this->normalizeDirectory($directory);

        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new Exception(sprintf('Unable to create temporary file directory: %s', $directory));
        }

        return $directory;
    }

    /**
     * Return the physical path for attachment metadata from vtiger_attachments.
     */
    public function getAttachmentFilePath(array $attachment): string
    {
        $storedName = !empty($attachment['storedname'])
            ? $attachment['storedname']
            : ($attachment['name'] ?? '');
        $fileName = ($attachment['attachmentsid'] ?? '') . '_' . $storedName;

        return $this->getFilePath((string)($attachment['path'] ?? ''), $fileName);
    }

    /**
     * Resolve a stored relative or absolute directory and append a file name.
     */
    public function getFilePath(string $directory, string $fileName = ''): string
    {
        if (!$this->isAbsolutePath($directory)) {
            $rootDirectory = rtrim((string)vglobal('root_directory'), '/\\');

            if ($rootDirectory !== '') {
                $directory = $rootDirectory
                    . DIRECTORY_SEPARATOR
                    . ltrim($directory, '/\\');
            }
        }

        return $this->normalizeDirectory($directory) . ltrim($fileName, '/\\');
    }

    /**
     * Return the existing public attachment endpoint URL.
     */
    public function getAttachmentPublicUrl(int $attachmentId, string $fileName, bool $absolute = true): string
    {
        $url = Vtiger_Functions::getFilePublicURL($attachmentId, $fileName);

        if ($absolute && $url !== '') {
            $url = rtrim((string)vglobal('site_URL'), '/') . '/' . ltrim($url, '/');
        }

        return $url;
    }

    public function isAbsolutePath(string $path): bool
    {
        return (bool)preg_match('#^(?:[a-zA-Z]:[\\\\/]|[\\\\/]{1,2})#', $path);
    }

    private function normalizeDirectory(string $directory): string
    {
        $directory = str_replace('\\', '/', $directory);

        return rtrim($directory, '/') . '/';
    }
}
