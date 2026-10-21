<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

define('XML_HTMLSAX3', dirname(__FILE__) . '/../third-party/XML/');
include_once dirname(__FILE__) . '/../third-party/HTML.Safe.php';

class MailManager_Utils_Helper
{
    public static function safe_html_string($string)
    {
        $htmlSafe = new HTML_Safe();
        // Mails coming from HipChat has xml tag in body content. It has tag like <?xml encoding="utf-8"> and no closing tag for this
        // But HTML_Safe considers xml tag as dangerous tag and removes content between these tags.
        // Since there is no closing tag for this it removes all content. Also if it finds opening tag with ? it searches for closing
        // tag with ? and removes all content between them. So replacing <?xml to <xml, removing xml tag from deleteTagsContent
        // and adding it in noClose so that this tag can be there without closing tag.
        $string = str_replace('<?xml', '<xml', $string);
        unset($htmlSafe->deleteTagsContent[array_search('xml', $htmlSafe->deleteTagsContent)]);
        array_push($htmlSafe->noClose, 'xml');
        // End
        array_push($htmlSafe->whiteProtocols, 'cid');

        return $htmlSafe->parse($string);
    }

    public static function allowedFileExtension($filename)
    {
        $parts = explode('.', $filename);
        if (php7_count($parts) > 1) {
            $extension = $parts[php7_count($parts) - 1];

            return (in_array(strtolower($extension), vglobal('upload_badext')) === false);
        }

        return false;
    }

    /**
     * Builds a safe Content-Disposition header for a downloaded attachment: control
     * characters, quotes and path parts are removed and the UTF-8 name is sent per RFC 6266.
     *
     * @param string $fileName original attachment name
     *
     * @return string
     */
    public static function getAttachmentDispositionHeader(string $fileName): string
    {
        $fileName = basename(str_replace('\\', '/', $fileName));
        $fileName = trim(preg_replace('/[\x00-\x1F\x7F"]+/u', '', $fileName) ?? '');

        if ('' === $fileName) {
            $fileName = 'attachment';
        }

        $asciiName = preg_replace('/[^\x20-\x7E]/', '_', $fileName);

        return sprintf('Content-Disposition: attachment; filename="%s"; filename*=UTF-8\'\'%s', $asciiName, rawurlencode($fileName));
    }

    /**
     * Removes characters that would break a value inside a quoted webservice (VTQL)
     * query string or a LIKE pattern: single quotes and backslashes.
     *
     * @param string $value raw search value
     *
     * @return string
     */
    public static function convertToQueryValue(string $value): string
    {
        return str_replace(['\\', "'"], '', $value);
    }

    public static function emitJSON($object)
    {
        Zend_Json::$useBuiltinEncoderDecoder = true;
        echo Zend_Json::encode($object);
    }
}