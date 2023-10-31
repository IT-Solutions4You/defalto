{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	<div class="px-4 pb-4 content-area" id="importModules">
		<div class="container-fluid rounded bg-body">
			<div class="row py-3">
				<div class="col-sm-4 col-xs-4">
					<div class="row">
						<div class="col-sm-8 col-xs-8">
							<input type="text" id="searchExtension" class="extensionSearch form-control" placeholder="{vtranslate('Search for an extension..', $QUALIFIED_MODULE)}"/>
						</div>
					</div>
				</div>
			</div>
			<div class="contents row">
				<div class="col-sm-12 col-xs-12" id="extensionContainer">
					{include file='ExtensionModules.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
				</div>
			</div>
		</div>
		{include file="CardSetupModals.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
	</div>
{/strip}