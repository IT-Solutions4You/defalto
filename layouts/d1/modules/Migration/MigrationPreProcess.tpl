{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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
