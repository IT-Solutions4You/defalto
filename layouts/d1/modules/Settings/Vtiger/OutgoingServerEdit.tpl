{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Settings/Vtiger/views/OutgoingServerEdit.php *}
{strip}
	<div class="editViewPageDiv">
		<div class="editViewContainer px-4 pb-4" id="EditViewOutgoing">
			<div class="bg-body rounded">
				<div class="container-fluid py-3 border-bottom">
					<h3>{vtranslate('LBL_OUTGOING_SERVER', $QUALIFIED_MODULE)}</h3>
					<p>{vtranslate('LBL_OUTGOING_SERVER_DESC', $QUALIFIED_MODULE)}</p>
				</div>
				{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
				<form id="OutgoingServerForm" data-detail-url="{$MODEL->getDetailViewUrl()}" method="POST">
					<input type="hidden" name="default" value="false"/>
					<input type="hidden" name="server_port" value="0"/>
					<input type="hidden" name="server_type" value="email"/>
					<input type="hidden" name="id" value="{$MODEL->get('id')}"/>
					<div class="blockData">
						<div class="hide errorMessage">
							<div class="alert alert-danger">
								{vtranslate('LBL_TESTMAILSTATUS', $QUALIFIED_MODULE)}<strong>{vtranslate('LBL_MAILSENDERROR', $QUALIFIED_MODULE)}</strong>
							</div>
						</div>
						<div class="block container-fluid">
							<div class="row py-3">
								<h4 class="col-lg">{vtranslate('LBL_MAIL_SERVER_SMTP', $QUALIFIED_MODULE)}</h4>
								<div class="col-auto btn-group">
									<button class="btn btn-outline-secondary t-btn resetButton" type="button" title="{vtranslate('LBL_RESET_TO_DEFAULT', $QUALIFIED_MODULE)}"><strong>{vtranslate('LBL_RESET_TO_DEFAULT', $QUALIFIED_MODULE)}</strong></button>
								</div>
							</div>
							<div class="row">
								<div>
									<div class="row">
										<div class="col-lg-3 fieldLabel">
											<label>{vtranslate('LBL_SERVER_TYPE', $QUALIFIED_MODULE)}</label>
										</div>
										<div class="col-lg-4 fieldValue">
											<select class="select2 inputElement form-select" name="serverType">
												<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
												<option value="{"ssl://smtp.gmail.com:465"}" {if {$MODEL->get('server')} eq "ssl://smtp.gmail.com:465"} selected {/if}>{vtranslate('LBL_GMAIL', $QUALIFIED_MODULE)} </option>
												<option value="{"smtp.live.com"}" {if {$MODEL->get('server')} eq "smtp.live.com"} selected {/if}>{vtranslate('LBL_HOTMAIL', $QUALIFIED_MODULE)}</option>
												<option value="{"smtp-mail.outlook.com"}" {if {$MODEL->get('server')} eq "smtp.live.com"} selected {/if}>{vtranslate('LBL_OFFICE365', $QUALIFIED_MODULE)}</option>
												<option value="{"smtp.mail.yahoo.com"}" {if {$MODEL->get('server')} eq "smtp.mail.yahoo.com"} selected {/if}>{vtranslate('LBL_YAHOO', $QUALIFIED_MODULE)}</option>
												<option value="">{vtranslate('LBL_OTHERS', $QUALIFIED_MODULE)}</option>
											</select>
										</div>
									</div>
									<div class="row my-3">
										<div class="col-lg-3 fieldLabel">
											<label>{vtranslate('LBL_SERVER_NAME', $QUALIFIED_MODULE)}</label>
											<span class="text-danger ms-2">*</span>
										</div>
										<div class="col-lg-4 fieldValue">
											<input type="text" class="inputElement form-control" name="server" data-rule-required="true" value="{$MODEL->get('server')}">
										</div>
									</div>
									<div class="row my-3">
										<div class="col-lg-3 fieldLabel">
											<label>{vtranslate('LBL_USER_NAME', $QUALIFIED_MODULE)}</label>
										</div>
										<div class="col-lg-4 fieldValue">
											<input type="text" class="inputElement form-control" name="server_username" value="{$MODEL->get('server_username')}">
										</div>
									</div>
									<div class="row my-3">
										<div class="col-lg-3 fieldLabel">
											<label>{vtranslate('LBL_PASSWORD', $QUALIFIED_MODULE)}</label>
										</div>
										<div class="col-lg-4 fieldValue">
											<input type="password" class="inputElement form-control" name="server_password" value="{$MODEL->get('server_password')}">
										</div>
									</div>
									<div class="row my-3">
										<div class="col-lg-3 fieldLabel">
											<label>{vtranslate('LBL_FROM_EMAIL', $QUALIFIED_MODULE)}</label>
										</div>
										<div class="col-lg-4 fieldValue">
											<input type="text" class="inputElement form-control" name="from_email_field" data-rule-email="true" data-rule-illegal="true" value="{$MODEL->get('from_email_field')}">
										</div>
									</div>
									<div class="row my-3">
										<div class="col-lg-3 fieldLabel">&nbsp;</div>
										<div class="col-lg-4 fieldValue">
											<div class=" col-lg-12 col-md-12 col-sm-12">
												<div class="alert alert-info">{vtranslate('LBL_OUTGOING_SERVER_FROM_FIELD', $QUALIFIED_MODULE)}</div>
											</div>
										</div>
									</div>
									<div class="row my-3">
										<div class="col-lg-3 fieldLabel">
											<label>{vtranslate('LBL_REQUIRES_AUTHENTICATION', $QUALIFIED_MODULE)}</label>
										</div>
										<div class="col-lg-4">
											<input type="checkbox" name="smtp_auth" {if $MODEL->isSmtpAuthEnabled()}checked{/if} ></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<br>
						<div class="modal-overlay-footer modal-footer py-3">
							<div class="container-fluid">
								<div class="row">
									<div class="col-6 text-end">
										<a class="btn btn-primary cancelLink" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a>
									</div>
									<div class="col-6">
										<button type="submit" class='btn btn-primary active saveButton'>{vtranslate('LBL_SAVE', $MODULE)}</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}
