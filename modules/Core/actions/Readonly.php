<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Readonly_Action extends Core_Controller_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function makeEditable(Vtiger_Request $request): void
    {
        $module = $request->getModule();
        $record = (int)$request->get('record');
        $success = false;

        if (!empty($record)) {
            $model = Core_Readonly_Model::getInstance($module);
            $model->setRecord($record);
            $model->unsetReadonly();

            $success = true;
        }

        if ($success) {
            if (!empty($_SERVER['HTTP_REFERER'])) {
                header('location:' . $_SERVER['HTTP_REFERER']);
            }
        } else {
            throw new Exception('Empty record for Editable');
        }
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function install(Vtiger_Request $request): void
    {
        error_reporting(E_ALL);
        PearDatabase::getInstance()->setDebug(1);
        PearDatabase::getInstance()->setDieOnError(1);

        echo '<h1>Update table</h1>';

        Core_Readonly_Model::updateTable();

        echo '<h1>Delete workflow</h1>';

        Core_Readonly_Model::updateWorkflow(false);

        echo '<h1>Install workflow</h1>';

        Core_Readonly_Model::updateWorkflow();

        echo '<h1>Finish</h1>';
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function makeReadonly(Vtiger_Request $request): void
    {
        $module = $request->getModule();
        $record = (int)$request->get('record');
        $success = false;

        if (!empty($record)) {
            $model = Core_Readonly_Model::getInstance($module);
            $model->setRecord($record);
            $model->setReadonly();

            $success = true;
        }

        if ($success) {
            if (!empty($_SERVER['HTTP_REFERER'])) {
                header('location:' . $_SERVER['HTTP_REFERER']);
            }
        } else {
            throw new Exception('Empty record for Readonly');
        }
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request): void
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }

        echo 'Methods: install, editable, readonly';
    }

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->exposeMethod('install');
        $this->exposeMethod('makeEditable');
        $this->exposeMethod('makeReadonly');
    }
}