{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	<div class="detailViewContainer full-height">
		<div class="px-4 pb-4">
			<div class="detailViewInfo bg-body rounded" >
				<form id="detailView" class="form-horizontal" method="POST">
					<div class="container-fluid p-3 border-bottom">
						<div class="row align-items-center">
							<div class="col">
								<h4 class="m-0">
									{$RECORD_MODEL->get('groupname')}
								</h4>
							</div>
							<div class="col-auto">
								<button class="btn btn-outline-secondary" onclick="window.location.href='{$RECORD_MODEL->getEditViewUrl()}'" type="button">
									<strong>{vtranslate('LBL_EDIT_RECORD', $MODULE)}</strong>
								</button>
							</div>
						</div>
					</div>
					<div class="container-fluid p-3">
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
								<span>{vtranslate('LBL_GROUP_MEMBERS', $QUALIFIED_MODULE)}</span>
								<span class="text-danger ms-2">*</span>
							</span>
							<div class="fieldValue col-lg">
								<span class="col-lg-6 col-md-6 col-sm-6 collectiveGroupMembers groupMembersColors">
									{assign var=GROUPS value=$RECORD_MODEL->getMembers()}
									{foreach key=GROUP_LABEL item=GROUP_MEMBERS from=$GROUPS}
										{if !empty($GROUP_MEMBERS)}
											<div>
												<div class="groupLabel fw-bold">{vtranslate($GROUP_LABEL,$QUALIFIED_MODULE)}</div>
												{foreach item=GROUP_MEMBER_INFO from=$GROUP_MEMBERS}
													<span class="btn bg-primary me-2 mb-2 {$GROUP_LABEL}">
														<a href="{$GROUP_MEMBER_INFO->getDetailViewUrl()}">{$GROUP_MEMBER_INFO->get('name')}</a>
													</span>
												{/foreach}
											</div>
										{/if}
									{/foreach}
								</span>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}