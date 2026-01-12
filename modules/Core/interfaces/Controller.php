<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

interface Core_Controller_Interface
{
    public function __construct();

    public function isLoginRequired();

    /**
     * @param Vtiger_Request $request
     */
    public function validateRequest(Vtiger_Request $request);

    /**
     * @param Vtiger_Request $request
     */
    public function getViewer(Vtiger_Request $request);

    /**
     * @param Vtiger_Request $request
     */
    public function preProcess(Vtiger_Request $request);

    /**
     * @param Vtiger_Request $request
     */
    public function process(Vtiger_Request $request);

    /**
     * @param Vtiger_Request $request
     */
    public function postProcess(Vtiger_Request $request);
}