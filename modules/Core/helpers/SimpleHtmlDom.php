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

    public function getHtml()
    {
        return $this->html->save();
    }

    public function getHtmlNode()
    {
        return $this->html;
    }

    public function parents($node, $tag) {
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