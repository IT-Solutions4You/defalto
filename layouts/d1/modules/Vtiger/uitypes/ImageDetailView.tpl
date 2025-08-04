{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{foreach key=ITER item=IMAGE_INFO from=$RECORD->getImageDetails()}
	{if !empty($IMAGE_INFO.url)}
		<img src="{$IMAGE_INFO.url}" width="150" height="80">
	{/if}
{/foreach}