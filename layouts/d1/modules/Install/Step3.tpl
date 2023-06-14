{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

<form class="form-horizontal" name="step3" method="get" action="index.php">
	<input type=hidden name="module" value="Install" />
	<input type=hidden name="view" value="Index" />
	<input type=hidden name="mode" value="Step4" />

	<div class="main-container">
		<div class="inner-container">
			<div class="row">
				<div class="col-sm-10">
					<h4>{vtranslate('LBL_INSTALL_PREREQUISITES', 'Install')}</h4>
				</div>
				<div class="col-sm-2">
					<a href="https://wiki.vtiger.com/vtiger6/" target="_blank" class="pull-right">
						<img src="{'help.png'|vimage_path}" alt="Help-Icon"/>
					</a>
				</div>
			</div>
			<hr>
			<div class="row">
				<div class="row offset2">
					<div class="col-sm-2"></div>
					<div class="col-sm-8">
						<div class=" pull-right">
							<div class="button-container">
								<a href ="#">
									<input type="button" class="btn btn-default" value="{vtranslate('LBL_RECHECK', 'Install')}" id='recheck'/>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="row offset2">
					<div class="col-sm-2"></div>
					<div class="col-sm-8">
						<table class="config-table">
							<tr>
								<th>{vtranslate('LBL_PHP_CONFIGURATION', 'Install')}</th>
								<th>{vtranslate('LBL_REQUIRED_VALUE', 'Install')}</th>
								<th>{vtranslate('LBL_PRESENT_VALUE', 'Install')}</th>
							</tr>
							{foreach key=CONFIG_NAME item=INFO from=$SYSTEM_PREINSTALL_PARAMS}
								<tr>
									<td>{vtranslate($CONFIG_NAME, 'Install')}</td>
									<td>
										{if $INFO.1 eq 1} 
											{vtranslate('LBL_YES', 'Install')} 
										{else} 
											{$INFO.1} 
										{/if}
									</td>
									<td {if $INFO.2 eq false} class="no" >
											{if $CONFIG_NAME = 'LBL_PHP_VERSION'}
												{$INFO.0}
											{else}
												{vtranslate('LBL_NO', 'Install')}
											{/if}
										{else if ($INFO.2 eq true and $INFO.1 === true)} > 
											{vtranslate('LBL_YES', 'Install')} 
										{else} > 
											{$INFO.0} 
										{/if}
									</td>
								</tr>
							{/foreach}
						</table>
						{if $PHP_INI_CURRENT_SETTINGS}
							<table class="config-table">
								<tr>
									<th colspan="3">{vtranslate('LBL_PHP_RECOMMENDED_SETTINGS', 'Install')}</th>
								</tr>
								{foreach key=DIRECTIVE item=VALUE from=$PHP_INI_CURRENT_SETTINGS name=directives}
									<tr>
										<td>{$DIRECTIVE}</td><td>{$PHP_INI_RECOMMENDED_SETTINGS[$DIRECTIVE]}</td><td class="no">{$VALUE}</td>
									</tr>
								{/foreach}
							</table>
						{/if}
						{if $FAILED_FILE_PERMISSIONS}
							<table class="config-table">
								<tr>
									<th colspan="2">{vtranslate('LBL_READ_WRITE_ACCESS', 'Install')}</th>
								</tr>
								{foreach item=FILE_PATH key=FILE_NAME from=$FAILED_FILE_PERMISSIONS}
									<tr>
										<td nowrap>{$FILE_NAME} ({str_replace("./","",$FILE_PATH)})</td><td class="no">{vtranslate('LBL_NO', 'Install')}</td>
									</tr>
								{/foreach}
							</table>
						{/if}
					</div>
				</div>
			</div>
			<div class="row offset2">
				<div class="col-sm-2"></div>
				<div class="col-sm-8">
					<div class="button-container">
						<input type="button" class="btn btn-default" value="{vtranslate('LBL_BACK', 'Install')}" name="back"/>
						<input type="button" class="btn btn-large btn-primary" value="{vtranslate('LBL_NEXT', 'Install')}" name="step4"/>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>