<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_ImageValidation_Helper
{
    /**
     * Validate whether the file is an image. Checks the extension, detected MIME type,
     * metadata and file contents.
     *
     * @param array $fileDetails
     * @param array $allowedImageFormats
     *
     * @return bool
     * @throws Exception
     */
    public static function validate(array $fileDetails, array $allowedImageFormats): bool
    {
        $fileType = strtolower(pathinfo($fileDetails['name'], PATHINFO_EXTENSION));

        if (!in_array($fileType, $allowedImageFormats)) {
            return false;
        }

        $mimeType = self::getMimeType($fileDetails['tmp_name']);

        if (!is_string($mimeType)) {
            return false;
        }

        $mimeParts = explode('/', $mimeType, 2);

        if (count($mimeParts) !== 2) {
            return false;
        }

        [$mimeTypeContents, $mimeSubtype] = $mimeParts;

        if (strtolower($mimeTypeContents) !== 'image' || !in_array($mimeSubtype, $allowedImageFormats)) {
            return false;
        }

        $shortTagSupported = (bool)ini_get('short_open_tag');
        $tmpFileName = $fileDetails['tmp_name'];

        if (in_array($mimeType, ['image/jpeg', 'image/tiff'])) {
            $exifData = function_exists('exif_read_data') ? @exif_read_data($tmpFileName) : false;

            if ($exifData && !self::validateMetadata($exifData, $shortTagSupported)) {
                return false;
            }

            if ($mimeType === 'image/jpeg' && extension_loaded('gd') && function_exists('gd_info')) {
                $image = @imagecreatefromjpeg($tmpFileName);

                if (!$image) {
                    return false;
                }

                $imageSaved = imagejpeg($image, $tmpFileName);
                imagedestroy($image);

                if (!$imageSaved) {
                    return false;
                }
            }
        }

        $imageContents = file_get_contents($tmpFileName);

        if (!is_string($imageContents)) {
            return false;
        }

        $shortTag = $shortTagSupported ? '<?' : '<?php';

        if (stripos($imageContents, $shortTag) !== false) {
            return false;
        }

        if (in_array('svg+xml', [$fileType, $mimeSubtype])) {
            $imageContents = purifyHtmlEventAttributes($imageContents, true);

            if (file_put_contents($tmpFileName, $imageContents) === false) {
                return false;
            }
        }

        /*
         * File functions like filegroup(), fileowner(), filesize(), filetype(), fileperms() and a few others cache
         * file information. Clear it so later calls do not return stale values after the file has been updated.
         */
        clearstatcache();

        return true;
    }

    /**
     * @param mixed $data
     * @param bool  $short
     *
     * @return bool
     */
    public static function validateMetadata($data, bool $short = true): bool
    {
        if (is_array($data)) {
            foreach ($data as $value) {
                if (!self::validateMetadata($value, $short)) {
                    return false;
                }
            }
        } elseif (stripos($data, $short ? '<?' : '<?php') !== false) {
            return false;
        }

        return true;
    }

    /**
     * @param string $filename
     *
     * @return string|null
     * @throws Exception
     */
    private static function getMimeType(string $filename): ?string
    {
        if (function_exists('mime_content_type')) {
            $type = mime_content_type($filename);
        } elseif (function_exists('finfo_open')) {
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($fileInfo, $filename);
            finfo_close($fileInfo);
        } else {
            throw new Exception('mime_magic or fileinfo extension required.');
        }

        return is_string($type) ? $type : null;
    }
}
