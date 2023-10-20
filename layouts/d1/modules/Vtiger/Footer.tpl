{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
</div>
<footer class="app-footer bg-body">
	<p>
		Powered by vtiger CRM - {$VTIGER_VERSION}&nbsp;&nbsp;© 2004 - {date('Y')}&nbsp;&nbsp;
		<a href="//www.vtiger.com" target="_blank">Vtiger</a>&nbsp;|&nbsp;
		<a href="https://www.vtiger.com/privacy-policy" target="_blank">Privacy Policy</a>
	</p>
</footer>
<div id='overlayPage' class="modal fade">
	<div class="modal-dialog modal-fullscreen">
		<div class="modal-content">
			<!-- arrow is added to point arrow to the clicked element (Ex:- TaskManagement),
			any one can use this by adding "show" class to it -->
			<div class='arrow'></div>
			<div class='data'></div>
		</div>
	</div>
</div>
<div id='helpPageOverlay' class="modal fade">
	<div class="modal-dialog">
		<div class="data"></div>
	</div>
</div>
<div id="js_strings" class="hide noprint">{Zend_Json::encode($LANGUAGE_STRINGS)}</div>
<div id="maxListFieldsSelectionSize" class="hide noprint">{$MAX_LISTFIELDS_SELECTION_SIZE}</div>
<div id="myModal" class="modal myModal fade">
	<div class="modal-dialog"></div>
</div>
{include file='JSResources.tpl'|@vtemplate_path}
</body>
</html>
