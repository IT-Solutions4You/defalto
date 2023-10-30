{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {foreach item=CONTACT_INFO from=$RELATED_CONTACTS}
        <a href='{$CONTACT_INFO['_model']->getDetailViewUrl()}' title='{vtranslate("Contacts", "Contacts")}'> {Vtiger_Util_Helper::getRecordName($CONTACT_INFO['id'])}</a>
        <br>
    {/foreach}
{/strip}