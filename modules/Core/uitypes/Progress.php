<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Progress_UIType extends Vtiger_Percentage_UIType
{
    public function getHeaderViewTemplateName(): string
    {
        return 'uitypes/ProgressHeader.tpl';
    }
}
