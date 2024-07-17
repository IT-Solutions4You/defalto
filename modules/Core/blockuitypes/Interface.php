<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface Core_Interface_BlockUIType
{
    /**
     * Returns the Template name used when editing
     *
     * @return string
     */
    public function getTemplateName(): string;

    /**
     * Returns the Template name used when displaying
     *
     * @return string
     */
    public function getDetailViewTemplateName(): string;
}