<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Core_RecurringDeleteCheck_View extends Vtiger_Index_View {
	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('RecurringDeleteView.tpl', $moduleName);
	}
}
