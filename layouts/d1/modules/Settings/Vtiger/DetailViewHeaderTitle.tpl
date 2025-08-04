{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="record-header">
		<div class="recordBasicInfo">
			<div class="recordHeaderTitle">
				<span class="fs-3 recordLabel pushDown" title="{$RECORD->getName()}">
					{foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
						{assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
						{if $FIELD_MODEL->getPermissions()}
							<span class="me-2 {$NAME_FIELD}">{trim($RECORD->get($NAME_FIELD))}</span>
						{/if}
					{/foreach}
				</span>
			</div>
		</div>
	</div>
{/strip}