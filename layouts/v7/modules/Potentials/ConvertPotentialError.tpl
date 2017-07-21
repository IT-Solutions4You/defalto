{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Potentials/views/SaveConvertPotential.php *}

{strip}
	<table border="0" cellpadding="5" cellspacing="0" width="100%" height="450px">
		<tr>
			<td align="center">
				<div style="border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 80%; position: relative; z-index: 10000000; padding :10px;">
					<table border="0" cellpadding="5" cellspacing="0" width="98%">
						<tbody>
							<tr>
								<td rowspan="2" width="11%"><img src="{vimage_path('denied.gif')}" ></td>
								<td style="border-bottom: 1px solid rgb(204, 204, 204);" nowrap="nowrap" width="70%">
									<span class="genHeaderSmall">
										{if $IS_DUPICATES_FAILURE}
											<span>{$EXCEPTION}</span>
										{else}
											{assign var=SINGLE_MODULE value="SINGLE_$MODULE"}
											{vtranslate('LBL_FOLLOWING_ARE_POSSIBLE_REASONS', $MODULE)}
											<ul>
												<li>{vtranslate('LBL_POTENTIALS_FIELD_MAPPING_INCOMPLETE', $MODULE)}</li>
												<li>{vtranslate('LBL_MANDATORY_FIELDS_ARE_EMPTY', $MODULE)}</li>
												{if $EXCEPTION}
													<li>{$EXCEPTION}</li>
												{/if}
											</ul>
										{/if}
									</span>
									</span>
								</td>
							</tr>
							{if !$IS_DUPICATES_FAILURE}
								<tr>
									<td class="small" align="right" nowrap="nowrap">
										{if $CURRENT_USER->isAdminUser()}
											<a href="index.php?parent=Settings&module=Leads&view=MappingDetail">{vtranslate('LBL_LEADS_FIELD_MAPPING', $MODULE)}</a><br>
										{/if}
										<a href="javascript:window.history.back();">{vtranslate('LBL_GO_BACK', $MODULE)}</a><br>
									</td>
								</tr>
							{/if}
						</tbody>
					</table>
				</div>
			</td>
		</tr>
	</table>
{/strip}
