{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
	<div class="detailViewContainer full-height">
		<div class="px-4 pb-4">
			<div class="detailViewInfo bg-body rounded" >
				<form id="detailView" class="form-horizontal container-fluid" method="POST">
					<div class="row py-3 border-bottom">
						<div class="col">
							<h4>
								{$RECORD_MODEL->get('groupname')}
							</h4>
						</div>
						<div class="col-auto">
							<button class="btn btn-outline-secondary" onclick="window.location.href='{$RECORD_MODEL->getEditViewUrl()}'" type="button">
								<strong>{vtranslate('LBL_EDIT_RECORD', $MODULE)}</strong>
							</button>
						</div>
					</div>
					<div class="form-group row py-3">
						<div class="fieldLabel col-lg-3">
							{vtranslate('LBL_GROUP_NAME', $QUALIFIED_MODULE)}
						</div>
						<div class="fieldValue col-lg">
							<b>{$RECORD_MODEL->getName()}</b>
						</div>
					</div>
					<div class="form-group row py-3">
						<div class="fieldLabel col-lg-3">
							{vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}
						</div>
						<div class="fieldValue col-lg">
							<b>{$RECORD_MODEL->getDescription()}</b>
						</div>
					</div>
					<div class="form-group row py-3">
						<span class="fieldLabel col-lg-3">
							{vtranslate('LBL_GROUP_MEMBERS', $QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span>
						</span>
						<div class="fieldValue col-lg">
							<span class="col-lg-6 col-md-6 col-sm-6 collectiveGroupMembers groupMembersColors">
								{assign var="GROUPS" value=$RECORD_MODEL->getMembers()}
								{foreach key=GROUP_LABEL item=GROUP_MEMBERS from=$GROUPS}
									{if !empty($GROUP_MEMBERS)}
										<ul class="nav mb-3">
											<li class="groupLabel px-3 py-2 me-2 rounded fw-bold {$GROUP_LABEL}">
												{vtranslate($GROUP_LABEL,$QUALIFIED_MODULE)}
											</li>
											{foreach item=GROUP_MEMBER_INFO from=$GROUP_MEMBERS}
												<li class="px-3 py-2 rounded me-2 {$GROUP_LABEL}">
													<a href="{$GROUP_MEMBER_INFO->getDetailViewUrl()}">{$GROUP_MEMBER_INFO->get('name')}</a>
												</li>
											{/foreach}
										</ul>
									{/if}
								{/foreach}
							</span>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}