{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{foreach key=ITER item=IMAGE_INFO from=$RECORD->getImageDetails()}
	{if !empty($IMAGE_INFO.url)}
		<img src="{$IMAGE_INFO.url}" width="150" height="80">
	{/if}
{/foreach}