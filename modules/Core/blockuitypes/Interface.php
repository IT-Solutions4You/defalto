<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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