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
    <div class="modal-dialog modal-content">
        {assign var="HEADER_TITLE" value={vtranslate('LBL_ADD_NEW_TAG', $QUALIFIED_MODULE)}}
		<form id="addTagSettings" method="POST">
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
			<div class="modal-body">
				<div class="row-fluid">
					<div class="form-group">
						<label class="control-label">
							{vtranslate('LBL_CREATE_NEW_TAG',$MODULE)}
						</label>
						<div>
							<input name="createNewTag" value="" data-rule-required = "true" class="form-control" placeholder="{vtranslate('LBL_CREATE_NEW_TAG',$MODULE)}"/>
						</div>
					</div>
					<div class="form-group">
						<div class="checkbox my-3">
							<label class="form-check">
								<input type="hidden" name="visibility" value="{Vtiger_Tag_Model::PRIVATE_TYPE}"/>
								<input class="form-check-input" type="checkbox" name="visibility" value="{Vtiger_Tag_Model::PUBLIC_TYPE}" />
								<span class="ms-2">{vtranslate('LBL_SHARE_TAGS',$MODULE)}</span>
							</label>
						</div>
					</div>
					<div class="form-group">
						<div class="alert alert-info">
							<h5>
								<i class="fa fa-info-circle"></i>
								<span class="ms-2">{vtranslate('Info', $QUALIFIED_MODULE)}</span>
							</h5>
							<p>{vtranslate('LBL_TAG_SEPARATOR_DESC', $QUALIFIED_MODULE)}</p>
							<p>{vtranslate('LBL_SHARED_TAGS_ACCESS',$QUALIFIED_MODULE)}</p>
						</div>
					</div>
				</div>
			</div>
			{include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
		</form>
	</div>
{/strip}
