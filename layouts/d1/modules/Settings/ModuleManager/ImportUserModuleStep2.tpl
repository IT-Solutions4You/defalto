{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="px-4 pb-4" id="importModules">
		<div class="bg-body rounded">
			<div>
				<div id="vtlib_modulemanager_import_div">
					{if $MODULEIMPORT_FAILED neq ''}
						<div class="col-lg-2"></div>
						<div class="col-lg-10">
							<b>{vtranslate('LBL_FAILED', $QUALIFIED_MODULE)}</b>
						</div>
						<div class="col-lg-2"></div>
						<div class="col-lg-10">
							{if $VERSION_NOT_SUPPORTED eq 'true'}
								<font color=red><b>{vtranslate('LBL_VERSION_NOT_SUPPORTED', $QUALIFIED_MODULE)}</b></font>
							{else}
								{if $MODULEIMPORT_FILE_INVALID eq "true"}
									<font color=red><b>{vtranslate('LBL_INVALID_FILE', $QUALIFIED_MODULE)}</b></font> {vtranslate('LBL_INVALID_IMPORT_TRY_AGAIN', $QUALIFIED_MODULE)}
								{else}
									<font color=red>{vtranslate('LBL_UNABLE_TO_UPLOAD', $QUALIFIED_MODULE)}</font> {vtranslate('LBL_UNABLE_TO_UPLOAD2', $QUALIFIED_MODULE)}
								{/if}
							{/if}
						</div>
						<input type="hidden" name="view" value="List">
					{else}
						<div class="p-3 border-bottom">
							<h4 class="m-0">{vtranslate('LBL_VERIFY_IMPORT_DETAILS',$QUALIFIED_MODULE)}</h4>
						</div>
						<div class="container-fluid p-3">
							<h4>
								{vtranslate($MODULEIMPORT_NAME, $QUALIFIED_MODULE)}
								{if $MODULEIMPORT_EXISTS eq 'true'} <span class="text-danger"><b>{vtranslate('LBL_EXISTS', $QUALIFIED_MODULE)}</b></span> {/if}
							</h4>
							<div>
								<p>
									<small>{vtranslate('LBL_REQ_VTIGER_VERSION', $QUALIFIED_MODULE)} : {$MODULEIMPORT_DEP_VTVERSION}</small>
								</p>
							</div>
							<div>
								{if $MODULEIMPORT_EXISTS eq 'true' || $MODULEIMPORT_DIR_EXISTS eq 'true'}
									{if $MODULEIMPORT_EXISTS eq 'true'}
										<input type="hidden" name="module_import_file" value="{$MODULEIMPORT_FILE}">
										<input type="hidden" name="module_import_type" value="{$MODULEIMPORT_TYPE}">
										<input type="hidden" name="module_import_name" value="{$MODULEIMPORT_NAME}">
									{else}
										<span class="alert alert-info">{vtranslate('LBL_DELETE_EXIST_DIRECTORY', $QUALIFIED_MODULE)}</span>
									{/if}
								{else}
									{assign var="need_license_agreement" value="false"}
									{if $MODULEIMPORT_LICENSE}
										{assign var="need_license_agreement" value="true"}
										<p>{vtranslate('LBL_LICENSE', $QUALIFIED_MODULE)}</p>
										<div class="py-2">
											<textarea readonly="" rows="15" class="form-control">{$MODULEIMPORT_LICENSE}</textarea>
										</div>
									{/if}
									{if $need_license_agreement eq 'true'}
										<div class="py-2">
											<input type="checkbox" class="acceptLicense form-check-input"> {vtranslate('LBL_LICENSE_ACCEPT_AGREEMENT', $QUALIFIED_MODULE)}
										</div>
									{/if}
									<input type="hidden" name="module_import_file" value="{$MODULEIMPORT_FILE}">
									<input type="hidden" name="module_import_type" value="{$MODULEIMPORT_TYPE}">
									<input type="hidden" name="module_import_name" value="{$MODULEIMPORT_NAME}">
								{/if}
							</div>
						</div>
					{/if}
				</div>
			</div>
			<div class="modal-overlay-footer modal-footer border-top">
				<div class="container-fluid p-3">
					<div class="row">
						<div class="col-6 text-end">
							<a class="cancelLink btn btn-primary" href="javascript:history.back()" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
						</div>
						<div class="col-6">
							{if $MODULEIMPORT_FAILED neq ''}
								<button class="btn btn-primary active finishButton" type="submit">
									<strong>{vtranslate('LBL_FINISH', $QUALIFIED_MODULE)}</strong>
								</button>
							{elseif $MODULEIMPORT_EXISTS eq 'true' || $MODULEIMPORT_DIR_EXISTS eq 'true'}
								<button class="btn btn-primary active updateModule" name="saveButton" {if $need_license_agreement eq 'true'} disabled {/if}>
									<span>{vtranslate('LBL_UPDATE_NOW', $QUALIFIED_MODULE)}</span>
								</button>
							{else}
								<button class="btn btn-primary active importModule" name="saveButton" {if $need_license_agreement eq 'true'} disabled {/if}>
									<strong>{vtranslate('LBL_IMPORT_NOW', $QUALIFIED_MODULE)}</strong>
								</button>
							{/if}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
