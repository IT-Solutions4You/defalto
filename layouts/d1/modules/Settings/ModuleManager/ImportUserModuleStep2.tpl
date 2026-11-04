{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="px-4 pb-4" id="importModules" data-import-url="{$MODULEIMPORT_NEW_URL}">
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
							{elseif $MODULEIMPORT_FILE_INVALID eq true}
                                <font color=red><b>{vtranslate('LBL_INVALID_FILE', $QUALIFIED_MODULE)}</b></font> {vtranslate('LBL_INVALID_IMPORT_TRY_AGAIN', $QUALIFIED_MODULE)}
                            {else}
                                <font color=red>{vtranslate('LBL_UNABLE_TO_UPLOAD', $QUALIFIED_MODULE)}</font> {vtranslate('LBL_UNABLE_TO_UPLOAD2', $QUALIFIED_MODULE)}
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
									<small>{vtranslate('LBL_PACKAGE_VERSION', $QUALIFIED_MODULE)}: {$MODULEIMPORT_VERSION}</small>
									{if $MODULEIMPORT_DEP_VTVERSION}
										<br><small>{vtranslate('LBL_REQ_CRM_VERSION', $QUALIFIED_MODULE)}: {$MODULEIMPORT_DEP_VTVERSION}</small>
									{/if}
								</p>
							</div>
							<div class="mb-3">
								<h5>{vtranslate('LBL_CHANGELOG_INFORMATION', 'Installer')}</h5>
								{if $MODULEIMPORT_CHANGELOG}
									{foreach item=CHANGELOG_ENTRY from=$MODULEIMPORT_CHANGELOG name=moduleImportChangelog}
										<section class="{if !$smarty.foreach.moduleImportChangelog.first}border-top pt-3{/if} mb-3">
											<h6 class="d-flex align-items-center gap-2">
												<span class="badge bg-primary">{$CHANGELOG_ENTRY.version|escape}</span>
												{if $CHANGELOG_ENTRY.date}<span class="text-body-secondary">{$CHANGELOG_ENTRY.date|escape}</span>{/if}
											</h6>
											<ul class="mb-0">
												{foreach item=CHANGE from=$CHANGELOG_ENTRY.changes}
													<li>{$CHANGE|escape}</li>
												{/foreach}
											</ul>
										</section>
									{/foreach}
								{else}
									<div class="alert alert-warning mb-0">{vtranslate('LBL_MISSING_CHANGELOG', 'Installer')}</div>
								{/if}
							</div>
							<div>
								{if $MODULEIMPORT_EXISTS eq 'true' || $MODULEIMPORT_DIR_EXISTS eq 'true'}
									{if $MODULEIMPORT_EXISTS eq 'true'}
										<input type="hidden" name="module_import_file" value="{$MODULEIMPORT_FILE}">
									{else}
										<span class="alert alert-info">{vtranslate('LBL_DELETE_EXIST_DIRECTORY', $QUALIFIED_MODULE)}</span>
									{/if}
								{else}
									<input type="hidden" name="module_import_file" value="{$MODULEIMPORT_FILE}">
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
							{elseif $MODULEIMPORT_EXISTS eq 'true'}
								<button class="btn btn-primary active updateModule" name="saveButton">
									<span>{vtranslate('LBL_UPDATE_NOW', $QUALIFIED_MODULE)}</span>
								</button>
							{elseif $MODULEIMPORT_DIR_EXISTS eq 'true'}
								<button class="btn btn-primary active finishButton" type="submit">
									<strong>{vtranslate('LBL_FINISH', $QUALIFIED_MODULE)}</strong>
								</button>
							{else}
								<button class="btn btn-primary active importModule" name="saveButton">
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
