{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	<div class="modal-dialog modelContainer">
		<div class="modal-content">
			{assign var=HEADER_TITLE value={vtranslate($RECORD_MODEL->get('name'), $QUALIFIED_MODULE)}}
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
			<form class="form-horizontal" id="cronJobSaveAjax" method="post" action="index.php">
				<input type="hidden" name="module" value="{$MODULE}" />
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="action" value="SaveAjax" />
				<input type="hidden" name="record" value="{$RECORD}" />
				<input type="hidden" name="cronjob" value="{$RECORD_MODEL->get('name')}" />
				<input type="hidden" name="oldstatus" value="{$RECORD_MODEL->get('status')}" />
				<input type="hidden" id="minimumFrequency" value="{$RECORD_MODEL->getMinimumFrequency()}" />
				<input type="hidden" name="frequency" id="frequency" value="" />

				<div class="modal-body container-fluid">
					<div class="form-group row">
						<label class="control-label fieldLabel col-lg-4">{vtranslate('LBL_STATUS',$QUALIFIED_MODULE)}</label>
						<div class="controls fieldValue col-lg-8">
							<select class="select2 inputElement" name="status">
								<option {if $RECORD_MODEL->get('status') eq 1} selected="" {/if} value="1">{vtranslate('LBL_ACTIVE',$QUALIFIED_MODULE)}</option>
								<option {if $RECORD_MODEL->get('status') eq 0} selected="" {/if} value="0">{vtranslate('LBL_INACTIVE',$QUALIFIED_MODULE)}</option>
							</select>
						</div>
					</div>
					<div class="form-group row mt-3">
						<label class="control-label fieldLabel col-lg-4">{vtranslate('Frequency',$QUALIFIED_MODULE)}</label>
						{assign var=VALUES value=':'|explode:$RECORD_MODEL->getDisplayValue('frequency')}
						{if $VALUES[0] == '00' && $VALUES[1] == '00'}
							{assign var=MINUTES value="true"}
							{assign var=FIELD_VALUE value=$VALUES[1]}
						{elseif $VALUES[0] == '00'}
							{assign var=MINUTES value="true"}
							{assign var=FIELD_VALUE value=$VALUES[1]}
						{elseif $VALUES[1] == '00'}
							{assign var=MINUTES value="false"}
							{assign var=FIELD_VALUE value=($VALUES[0])}
						{else}
							{assign var=MINUTES value="true"}
							{assign var=FIELD_VALUE value=($VALUES[0]*60)+$VALUES[1]}
						{/if}
						<div class="controls fieldValue col-lg-4">
							<input type="text" class="inputElement form-control" value="{$FIELD_VALUE}" {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if} id="frequencyValue"/>
						</div>
						<div class="controls fieldValue col-lg-4">
							<select class="select2 inputElement" id="time_format">
								<option value="mins" {if $MINUTES eq 'true'} selected="" {/if}>{vtranslate('LBL_MINUTES',$QUALIFIED_MODULE)}</option>
								<option value="hours" {if $MINUTES eq 'false'}selected="" {/if}>{vtranslate('LBL_HOURS',$QUALIFIED_MODULE)}</option>
							</select>
						</div>
					</div>
					<div class="form-group row mt-3">
						<div class="col-lg-4"></div>
						<div class="col-lg-8">
							<div class="alert alert-info">{vtranslate($RECORD_MODEL->get('description'),$QUALIFIED_MODULE)}</div>
						</div>
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</form>
		</div>
	</div>
{/strip}	
