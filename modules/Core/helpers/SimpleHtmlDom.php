<?php
/*
 *
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_SimpleHtmlDom_Helper {

    protected $html = null;

    public static function getInstance($content)
    {
        require_once 'vendor/simplehtmldom/simplehtmldom/simple_html_dom.php';

        $instance = new self();
        $instance->html = str_get_html($content);

        return $instance;
    }

    /** Convert text newlines without adding breaks before opening or closing paragraphs. */
    public static function convertNewlinesToHtml(?string $content): string
    {
        $parts = preg_split(
            '/((?:\r\n|\n\r|\r|\n)[ \t\r\n]*(?=(?:<|&lt;)\/?p(?=[ \t\r\n>]|&gt;)))/i',
            (string)$content,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );

        foreach ($parts as $index => $part) {
            if (0 === $index % 2) {
                $parts[$index] = nl2br($part);
            }
        }

        return implode('', $parts);
    }

    public function getHtml()
    {
        return $this->html->save();
    }

    /** Remove redundant explicit breaks at paragraph boundaries without changing HTML wrappers. */
    public static function convertParagraphBreaks(string $content): string
    {
        $protectedContent = '(?:<!--.*?-->|<(?<raw>pre|textarea|script|style)\b[^>]*>.*?</\k<raw>\s*>|<(?!br\s*/?>|/p\s*>)(?:"[^"]*"|\'[^\']*\'|[^\'">])*>)(*SKIP)(*F)|';
        $content = preg_replace(
            '~' . $protectedContent . '(?:<br\s*/?>\s*)+(?=(?:</(?:body|html)\s*>\s*)*</?p(?=[\s>]))~is',
            '',
            $content
        );

        return preg_replace_callback(
            '~' . $protectedContent . '(?<paragraph></p\s*>)(?<spacing>\s*)(?:<br\s*/?>\s*)+~is',
            static fn(array $match): string => $match['paragraph'] . $match['spacing'],
            $content
        );
    }

    public function getHtmlNode()
    {
        return $this->html;
    }

    public function parents($node, $tag) {
        if(!$node) {
            return null;
        }

        $parent = $node->parent();

        if($tag === $parent->tag) {
            return $parent;
        }

        return $this->parents($parent, $tag);
    }

    public function innerText($node, $html): void
    {
        $node->innertext = $html;
    }

    public function outerText($node, $html): void
    {
        $node->outerText = $html;
    }
}
