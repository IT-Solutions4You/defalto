<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Tour_Index_View extends Vtiger_Index_View {
    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();

        $viewer = $this->getViewer($request);
        $viewer->assign('GUIDES', Tour_Base_Guide::getAll());
        $viewer->view('Index.tpl', $moduleName);
    }
}