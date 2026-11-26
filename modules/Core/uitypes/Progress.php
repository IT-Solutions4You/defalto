<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
class Core_Progress_UIType extends Vtiger_Percentage_UIType
{
    public function getHeaderViewTemplateName(): string
    {
        return 'uitypes/ProgressHeader.tpl';
    }
}
