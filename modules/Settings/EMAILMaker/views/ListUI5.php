<?php

/* * *******************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class Settings_EMAILMaker_ListUI5_View extends Settings_Vtiger_UI5Embed_View {
	
	protected function getUI5EmbedURL(Vtiger_Request $request) {
		return '../index.php?module=Settings&action=listemailtemplates&parenttab=Settings';
	}
	
}
