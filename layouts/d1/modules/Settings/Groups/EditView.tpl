{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Settings/Groups/views/Edit.php *}

{strip}
	<div class="editViewPageDiv">
		<div class="px-4 pb-4">
			<div class="editViewContainer bg-body rounded">
				<form name="EditGroup" action="index.php" method="post" id="EditView" class="form-horizontal">
					<input type="hidden" name="module" value="Groups">
					<input type="hidden" name="action" value="Save">
					<input type="hidden" name="parent" value="Settings">
					<input type="hidden" name="record" value="{$RECORD_MODEL->getId()}">
					<input type="hidden" name="mode" value="{$MODE}">
					<div class="p-3 border-bottom">
						<h4 class="m-0">
							{if !empty($MODE)}
								{vtranslate('LBL_EDITING', $MODULE)} {vtranslate('SINGLE_'|cat:$MODULE, $QUALIFIED_MODULE)} - {$RECORD_MODEL->getName()}
							{else}
								{vtranslate('LBL_CREATING_NEW', $MODULE)} {vtranslate('SINGLE_'|cat:$MODULE, $QUALIFIED_MODULE)}
							{/if}
						</h4>
					</div>
					<div class="editViewBody container-fluid p-3">
						<div class="form-group row py-2">
							<label class="col-lg-3 fieldLabel control-label">
								<span>{vtranslate('LBL_GROUP_NAME', $QUALIFIED_MODULE)}</span>
								<span class="text-danger ms-2">*</span>
							</label>
							<div class="col-lg-6 fieldValue">
								<input class="inputElement form-control" type="text" name="groupname" value="{$RECORD_MODEL->getName()}" data-rule-required="true">
							</div>
						</div>
						<div class="form-group row py-2">
							<div class="col-lg-3 fieldLabel control-label">
								<span>{vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}</span>
							</div>
							<div class="col-lg-6 fieldValue">
								<input class="inputElement form-control" type="text" name="description" id="description" value="{$RECORD_MODEL->getDescription()}" />
							</div>
						</div>
						<div class="form-group row py-2">
							<label class="col-lg-3 fieldLabel control-label">
								<span>{vtranslate('LBL_GROUP_MEMBERS', $QUALIFIED_MODULE)}</span>
								<span class="text-danger ms-2">*</span>
							</label>
							{assign var=GROUP_MEMBERS value=$RECORD_MODEL->getMembers()}
							<div class="col-lg-6 fieldValue">
								<select id="memberList" class="select2 form-select inputElement" multiple="true" name="members[]" data-rule-required="true" data-placeholder="{vtranslate('LBL_ADD_USERS_ROLES', $QUALIFIED_MODULE)}" >
									{foreach from=$MEMBER_GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
										<optgroup label="{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)}" class="{$GROUP_LABEL}">
											{foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
												{if $MEMBER->getName() neq $RECORD_MODEL->getName()}
													<option value="{$MEMBER->getId()}" data-member-type="{$GROUP_LABEL}" {if isset($GROUP_MEMBERS[$GROUP_LABEL][$MEMBER->getId()])} selected="true"{/if}>{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)}: {trim($MEMBER->getName())}</option>
												{/if}
											{/foreach}
										</optgroup>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="row py-2" >
							<div class="col-lg-3"></div>
							<div class="groupMembersColors col-lg-6">
								<style>
									[title*="{vtranslate('LBL_USERS', $QUALIFIED_MODULE)}:"]
									{
										background-color: rgba(var(--bs-danger-rgb),0.25) !important;
										border-color: rgba(var(--bs-danger-rgb),0.25) !important;
									}
									[title*="{vtranslate('LBL_GROUPS', $QUALIFIED_MODULE)}:"]
									{
										background-color: rgba(var(--bs-primary-rgb),0.25) !important;
										border-color: rgba(var(--bs-primary-rgb),0.25) !important;
									}
									[title*="{vtranslate('LBL_ROLES', $QUALIFIED_MODULE)}:"]
									{
										background-color: rgba(var(--bs-warning-rgb),0.25) !important;
										border-color: rgba(var(--bs-warning-rgb),0.25) !important;
									}
									[title*="{vtranslate('LBL_ROLEANDSUBORDINATE', $QUALIFIED_MODULE)}:"]
									{
										background-color: rgba(var(--bs-success-rgb),0.25) !important;
										border-color: rgba(var(--bs-success-rgb),0.25) !important;
									}
								</style>
								<div class="d-flex">
									<div class="Users btn me-2">{vtranslate('LBL_USERS', $QUALIFIED_MODULE)}</div>
									<div class="Groups btn me-2">{vtranslate('LBL_GROUPS', $QUALIFIED_MODULE)}</div>
									<div class="Roles btn me-2">{vtranslate('LBL_ROLES', $QUALIFIED_MODULE)}</div>
									<div class="RoleAndSubordinates btn me-2">{vtranslate('LBL_ROLEANDSUBORDINATE', $QUALIFIED_MODULE)}</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-overlay-footer modal-footer border-top">
						<div class="container-fluid p-3">
							<div class="row">
								<div class="col-6 text-end">
									<a class="cancelLink btn btn-primary" data-dismiss="modal" href="javascript:history.back()" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
								</div>
								<div class="col-6">
									<button type="submit" class="btn btn-primary active saveButton" type="submit" >{vtranslate('LBL_SAVE', $MODULE)}</button>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}