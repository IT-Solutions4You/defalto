{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Settings/Profiles/views/DeleteAjax.php *}
{strip}
	<div class="modal-dialog modelContainer">
		<div class="modal-content">
			{assign var=HEADER_TITLE value={vtranslate('LBL_DELETE_PROFILE', $QUALIFIED_MODULE)}|cat:" - "|cat:{$RECORD_MODEL->getName()}}
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
			<form class="form-horizontal" id="DeleteModal" name="AddComment" method="post" action="index.php">
				<input type="hidden" name="module" value="{$MODULE}"/>
				<input type="hidden" name="parent" value="Settings"/>
				<input type="hidden" name="action" value="Delete"/>
				<input type="hidden" name="record" id="record" value="{$RECORD_MODEL->getId()}"/>
				<div name='massEditContent'>
					<div class="modal-body">
						<div class="form-group">
							<label class="control-label fieldLabel mb-3">{vtranslate('LBL_TRANSFER_ROLES_TO_PROFILE',$QUALIFIED_MODULE)}</label>
							<div class="controls fieldValue">
								<select id="transfer_record" name="transfer_record" class="select2 col-xs-9">
									<optgroup label="{vtranslate('LBL_PROFILES', $QUALIFIED_MODULE)}">
										{foreach from=$ALL_RECORDS item=PROFILE_MODEL}
											{assign var=PROFILE_ID value=$PROFILE_MODEL->get('profileid')}
											{if $PROFILE_ID neq $RECORD_MODEL->getId()}
												<option value="{$PROFILE_ID}">{$PROFILE_MODEL->get('profilename')}</option>
											{/if}
										{/foreach}
									</optgroup>
								</select>
							</div>
						</div>
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</form>
		</div>
	</div>
{/strip}


