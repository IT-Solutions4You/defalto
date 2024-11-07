<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Core_CKEditor_UIType extends Vtiger_Base_UIType {

    public const NEW_LINE_TAGS = ['address', 'br','article','aside','blockquote','canvas','dd','div','dl','dt','fieldset','figcaption','figure','footer','form','h1','h2','h3','h4','h5','h6','header','hr','li','main','nav','noscript','ol','p','pre','section','table','tfoot','ul','video',];
    /**
     * Function to get the Display Value, for the current field type with given DB Insert Value
     * @param $value
     * @param false $record
     * @param false $recordInstance
     * @return string
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        return self::transformDisplayValue((string)$value);
    }

    /**
     * @param string $value
     * @return string
     */
    public static function transformDisplayValue($value): string
    {
        $content = nl2br(strip_tags(decode_html($value)));

        return preg_replace('#(<br */?>\s*)+#i', '<br />', $content);
    }

    public function getEditViewDisplayValue($value)
    {
        return purifyHtmlEventAttributes(decode_html($value), true);
    }

    public static function transformEditViewDisplayValue($value): string
    {
        $content = decode_html($value);
        $newLineTags = self::NEW_LINE_TAGS;

        foreach ($newLineTags as $newLineTag) {
            $newLineTag = '</' . $newLineTag . '>';
            $content = str_replace($newLineTag, $newLineTag . '<br>', $content);
        }

        $newContent = '';
        $content = preg_replace('/<img.*src="(.*?)".*\/?>/', '\1', $content);
        $content = str_replace(['<br />', '<br/>'], ['<br>', '<br>'], $content);
        $lines = explode('<br>', $content);

        foreach ($lines as $line) {
            $line = trim(strip_tags($line));
            $line = preg_replace('/\s+/', ' ', $line);

            if (!empty($line)) {
                $newContent .= $line . PHP_EOL;
            }
        }

        return $newContent;
    }

    /**
	 * Function to get the Template name for the current UI Type Object
	 * @return string - Template Name
	 */
    public function getTemplateName()
    {
        return 'uitypes/CKEditor.tpl';
    }

    /**
	 * Function to get the Template name for the current UI Type Object
	 * @return string - Template Name
	 */
    public function getDetailViewTemplateName()
    {
        return 'uitypes/CKEditorDetailView.tpl';
    }
}