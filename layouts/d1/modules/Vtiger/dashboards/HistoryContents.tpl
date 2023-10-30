{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<div class="px-3 py-2">
	{if $HISTORIES neq false}
		{foreach key=$index item=HISTORY from=$HISTORIES}
			{assign var=MODELNAME value=get_class($HISTORY)}
			{if $MODELNAME == 'ModTracker_Record_Model'}
				{assign var=USER value=$HISTORY->getModifiedBy()}
				{assign var=TIME value=$HISTORY->getActivityTime()}
				{assign var=PARENT value=$HISTORY->getParent()}
				{assign var=MOD_NAME value=$HISTORY->getParent()->getModule()->getName()}
				{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MOD_NAME}
				{assign var=TRANSLATED_MODULE_NAME value = vtranslate($MOD_NAME ,$MOD_NAME)}
				{assign var=PROCEED value= TRUE}
				{if ($HISTORY->isRelationLink()) or ($HISTORY->isRelationUnLink())}
					{assign var=RELATION value=$HISTORY->getRelationInstance()}
					{if !($RELATION->getLinkedRecord())}
						{assign var=PROCEED value= FALSE}
					{/if}
				{/if}
				{if $PROCEED}
					<div class="row entry">
						<div class="col-lg-auto">
							{assign var=VT_ICON value=$MOD_NAME}
							{if $MOD_NAME eq "Events"}
								{assign var="TRANSLATED_MODULE_NAME" value="Calendar"}
								{assign var=VT_ICON value="Calendar"}
							{elseif $MOD_NAME eq "Calendar"}
								{assign var=VT_ICON value="Task"}
							{/if}
							<span>{$HISTORY->getParent()->getModule()->getModuleIcon($VT_ICON)}</span>
						</div>
						<div class="col-lg">
							{assign var=DETAILVIEW_URL value=$PARENT->getDetailViewUrl()}
							{if $HISTORY->isUpdate()}
								{assign var=FIELDS value=$HISTORY->getFieldInstances()}
								<div>
									<div>
										<b>{$USER->getName()}</b>
										<span class="me-2">{vtranslate('LBL_UPDATED')}</span>
										<a class="cursorPointer me-2" {if stripos($DETAILVIEW_URL, 'javascript:')===0} onclick='{$DETAILVIEW_URL|substr:strlen("javascript:")}' {else} onclick='window.location.href="{$DETAILVIEW_URL}"' {/if}>{$PARENT->getName()}</a>
									</div>
									{foreach from=$FIELDS key=INDEX item=FIELD}
										{if $INDEX lt 2}
											{if $FIELD && $FIELD->getFieldInstance() && $FIELD->getFieldInstance()->isViewableInDetailView()}
												<div>
													<i>{vtranslate($FIELD->getName(), $FIELD->getModuleName())}</i>
													{if $FIELD->get('prevalue') neq '' && $FIELD->get('postvalue') neq '' && !($FIELD->getFieldInstance()->getFieldDataType() eq 'reference' && ($FIELD->get('postvalue') eq '0' || $FIELD->get('prevalue') eq '0'))}
														<span class="me-2">{vtranslate('LBL_FROM')}</span>
														<b class="me-2">{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELD->getDisplayValue(decode_html($FIELD->get('prevalue'))))}</b>
													{elseif $FIELD->get('postvalue') eq '' || ($FIELD->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELD->get('postvalue') eq '0')}
														<b class="me-2">{vtranslate('LBL_DELETED')}</b>(<del>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELD->getDisplayValue(decode_html($FIELD->get('prevalue'))))}</del>)
													{else}
														<span class="me-2">{vtranslate('LBL_CHANGED')}</span>
													{/if}
													{if $FIELD->get('postvalue') neq '' && !($FIELD->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELD->get('postvalue') eq '0')}
														<span class="me-2">{vtranslate('LBL_TO')}</span>
														<b>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELD->getDisplayValue(decode_html($FIELD->get('postvalue'))))}</b>
													{/if}
												</div>
											{/if}
										{else}
											<a href="{$PARENT->getUpdatesUrl()}">{vtranslate('LBL_MORE')}</a>
											{break}
										{/if}
									{/foreach}
								</div>
							{elseif $HISTORY->isCreate()}
								<div>
									<b class="me-2">{$USER->getName()}</b>
									<span class="me-2">{vtranslate('LBL_ADDED')}</span>
									<a class="cursorPointer" {if stripos($DETAILVIEW_URL, 'javascript:')===0} onclick='{$DETAILVIEW_URL|substr:strlen("javascript:")}' {else} onclick='window.location.href="{$DETAILVIEW_URL}"' {/if}>{$PARENT->getName()}</a>
								</div>
							{elseif ($HISTORY->isRelationLink() || $HISTORY->isRelationUnLink())}
								{assign var=RELATION value=$HISTORY->getRelationInstance()}
								{assign var=LINKED_RECORD_DETAIL_URL value=$RELATION->getLinkedRecord()->getDetailViewUrl()}
								{assign var=PARENT_DETAIL_URL value=$RELATION->getParent()->getParent()->getDetailViewUrl()}
								<div>
									<b>{$USER->getName()}</b>
									{if $HISTORY->isRelationLink()}
										{vtranslate('LBL_ADDED', $MODULE_NAME)}
									{else}
										{vtranslate('LBL_REMOVED', $MODULE_NAME)}
									{/if}
									{if $RELATION->getLinkedRecord()->getModuleName() eq 'Calendar'}
										{if isPermitted('Calendar', 'DetailView', $RELATION->getLinkedRecord()->getId()) eq 'yes'}
											<a class="cursorPointer" {if stripos($LINKED_RECORD_DETAIL_URL, 'javascript:')===0} onclick='{$LINKED_RECORD_DETAIL_URL|substr:strlen("javascript:")}'
											{else} onclick='window.location.href="{$LINKED_RECORD_DETAIL_URL}"' {/if}>{$RELATION->getLinkedRecord()->getName()}</a>
									{else}
										{vtranslate($RELATION->getLinkedRecord()->getModuleName(), $RELATION->getLinkedRecord()->getModuleName())}
									{/if}
								{elseif $RELATION->getLinkedRecord()->getModuleName() == 'ModComments'}
									<i>"{$RELATION->getLinkedRecord()->getName()}"</i>
								{else}
									<a class="cursorPointer" {if stripos($LINKED_RECORD_DETAIL_URL, 'javascript:')===0} onclick='{$LINKED_RECORD_DETAIL_URL|substr:strlen("javascript:")}'
									{else} onclick='window.location.href="{$LINKED_RECORD_DETAIL_URL}"' {/if}>{$RELATION->getLinkedRecord()->getName()}</a>
							{/if}{vtranslate('LBL_FOR')} <a class="cursorPointer" {if stripos($PARENT_DETAIL_URL, 'javascript:')===0}
							   onclick='{$PARENT_DETAIL_URL|substr:strlen("javascript:")}' {else} onclick='window.location.href="{$PARENT_DETAIL_URL}"' {/if}>
									{$RELATION->getParent()->getParent()->getName()}</a>
							</div>
						{elseif $HISTORY->isRestore()}
							<div>
								<b>{$USER->getName()}</b> {vtranslate('LBL_RESTORED')} <a class="cursorPointer" {if stripos($DETAILVIEW_URL, 'javascript:')===0}
																						  onclick='{$DETAILVIEW_URL|substr:strlen("javascript:")}' {else} onclick='window.location.href="{$DETAILVIEW_URL}"' {/if}>
									{$PARENT->getName()}</a>
							</div>
						{elseif $HISTORY->isDelete()}
							<div>
								<b>{$USER->getName()}</b> {vtranslate('LBL_DELETED')}
								<strong> {$PARENT->getName()}</strong>
							</div>
						{/if}
						{if $TIME}
							<p class="text-end muted">
								<small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString("$TIME")}">{Vtiger_Util_Helper::formatDateDiffInStrings("$TIME")}</small>
							</p>
						{/if}
					</div>
				</div>
			{/if}
			{elseif $MODELNAME == 'ModComments_Record_Model'}
				<div class="row">
					<div class="col-lg-auto">
						<span>
							<i class="vicon-chat entryIcon" title={$TRANSLATED_MODULE_NAME}></i>
						</span>
					</div>
					<div class="col-lg">
						{assign var=COMMENT_TIME value=$HISTORY->getCommentedTime()}
						<div>
							<b>{$HISTORY->getCommentedByName()}</b> {vtranslate('LBL_COMMENTED')} {vtranslate('LBL_ON')} <a class="text-truncate" href="{$HISTORY->getParentRecordModel()->getDetailViewUrl()}">{$HISTORY->getParentRecordModel()->getName()}</a>
						</div>
						<div>
							<i>"{nl2br($HISTORY->get('commentcontent'))}"</i>
						</div>
						<p class="muted text-end"><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString("$COMMENT_TIME")}">{Vtiger_Util_Helper::formatDateDiffInStrings("$COMMENT_TIME")}</small></p>
					</div>
				</div>
			{/if}
		{/foreach}

		{if $NEXTPAGE}
			<div class="row">
				<div class="col-lg-12">
					<a href="javascript:;" class="load-more" data-page="{$PAGE}" data-nextpage="{$NEXTPAGE}">{vtranslate('LBL_MORE')}...</a>
				</div>
			</div>
		{/if}

	{else}
		<span class="noDataMsg">
			{if $HISTORY_TYPE eq 'updates'}
				{vtranslate('LBL_NO_UPDATES', $MODULE_NAME)}
			{elseif $HISTORY_TYPE eq 'comments'}
				{vtranslate('LBL_NO_COMMENTS', $MODULE_NAME)}
			{else}
				{vtranslate('LBL_NO_UPDATES_OR_COMMENTS', $MODULE_NAME)}
			{/if}
		</span>
	{/if}
</div>