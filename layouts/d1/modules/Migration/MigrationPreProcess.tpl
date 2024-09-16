{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{include file='Header.tpl'|@vtemplate_path:'Vtiger'}
<header class="fixed-top app-fixed-navbar bg-body-secondary">
	<div class="container-fluid px-4 page-container">
		<div class="row h-header align-items-center">
			<div class="col-sm">
				<div class="logo">
					<img src="{'logo.png'|vimage_path}"/>
				</div>
			</div>
			<div class="col-sm-auto">
				<div class="head">
					<h3>{vtranslate('LBL_INSTALLATION_WIZARD', $MODULE)}</h3>
				</div>
			</div>
		</div>
	</div>
</header>
