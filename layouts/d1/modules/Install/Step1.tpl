{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<div class="main-container container h-main px-4 py-3">
	<div class="inner-container">
		<form class="bg-body rounded" name="step1" method="post" action="index.php">
			<input type=hidden name="module" value="Install" />
			<input type=hidden name="view" value="Index" />
			<input type=hidden name="mode" value="Step2" />
			{include file='StepHeader.tpl'|@vtemplate_path:'Install'}
			<div class="container-fluid p-3">
				<div class="row">
					<div class="col-sm">
						<div class="welcome-div">
							<h4>{vtranslate('LBL_WELCOME_TO_VTIGER7_SETUP_WIZARD', 'Install')}</h4>
							<p>{vtranslate('LBL_VTIGER7_SETUP_WIZARD_DESCRIPTION','Install')}</p>
						</div>
						{if $LANGUAGES|@count > 1}
							<div class="row my-3">
								<div class="col-sm-3">{vtranslate('LBL_CHOOSE_LANGUAGE', 'Install')}</div>
								<div class="col-sm-3">
									<select class="form-select" name="lang" id="lang">
										{foreach key=header item=language from=$LANGUAGES}
											<option value="{$header}" {if $header eq $CURRENT_LANGUAGE}selected{/if}>{vtranslate("$language",'Install')}</option>
										{/foreach}
									</select>
								</div>
							</div>
						{/if}
					</div>
				</div>
			</div>
			<div class="p-3 button-container text-end">
				<input type="submit" class="btn btn-primary active" value="{vtranslate('LBL_INSTALL_BUTTON','Install')}"/>
			</div>
		</form>
	</div>
</div>