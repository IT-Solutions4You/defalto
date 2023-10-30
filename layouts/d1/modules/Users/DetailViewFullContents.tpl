{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Vtiger/views/Detail.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
	{assign var=NAME_FIELDS value=array('first_name', 'last_name')}
	{if $MODULE_MODEL}
		{assign var=NAME_FIELDS value=$MODULE_MODEL->getNameFields()}
	{/if}
    <form id="detailView" data-name-fields='{ZEND_JSON::encode($NAME_FIELDS)}' method="POST">
        {include file='DetailViewBlockView.tpl'|@vtemplate_path:$MODULE_NAME RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
    </form>
{/strip}
