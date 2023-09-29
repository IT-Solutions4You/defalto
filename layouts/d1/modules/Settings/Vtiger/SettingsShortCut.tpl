{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->*}
{strip}
	<div id="shortcut_{$SETTINGS_SHORTCUT->getId()}" data-actionurl="{$SETTINGS_SHORTCUT->getPinUnpinActionUrl()}" class="col-lg-3 contentsBackground well cursorPointer moduleBlock py-3" data-url="{$SETTINGS_SHORTCUT->getUrl()}">
		<div class="h-100 w-100 border-1 border p-3 container-fluid rounded">
			<div class="row">
				<div class="col pb-3">
					<div class="fw-bold">{vtranslate($SETTINGS_SHORTCUT->get('name'),$MODULE)}</div>
				</div>
				<div class="col-auto">
					<button data-id="{$SETTINGS_SHORTCUT->getId()}" title="{vtranslate('LBL_REMOVE',$MODULE)}" type="button" class="unpin close hiden">
						<i class="fa fa-close"></i>
					</button>
				</div>
			</div>
			<div class="row">
				<div class="col">
					{if $SETTINGS_SHORTCUT->get('description') && $SETTINGS_SHORTCUT->get('description') neq 'NULL'}
						{vtranslate($SETTINGS_SHORTCUT->get('description'),$MODULE)}
					{/if}
				</div>
			</div>
		</div>
	</div>
{/strip}
