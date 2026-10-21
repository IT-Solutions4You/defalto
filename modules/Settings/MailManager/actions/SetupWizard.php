<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_MailManager_SetupWizard_Action extends Settings_MailManager_ProAbstract_Action
{
    /**
     * Registers the wizard operations: finish, skip and restart.
     */
    public function __construct()
    {
        parent::__construct();

        $this->exposeMethod('finish');
        $this->exposeMethod('skip');
        $this->exposeMethod('restart');
    }

    /**
     * Runs the requested wizard operation.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();

        if (!$this->isMethodExposed($mode)) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $this->invokeExposedMethod($mode, $request);
    }

    /**
     * Saves the wizard values and completes the setup.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function finish(Vtiger_Request $request): void
    {
        Settings_MailManager_SetupWizard_Model::saveCompleted([
            'sync_from_days' => (int)$request->get('sync_from_days'),
            'ai_feature_emailfiling' => 'on' === $request->get('ai_feature_emailfiling') || '1' === $request->get('ai_feature_emailfiling'),
            'ai_feature_autocomment' => 'on' === $request->get('ai_feature_autocomment') || '1' === $request->get('ai_feature_autocomment'),
        ]);
        $this->emitSuccess();
    }

    /**
     * Skips the setup.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function skip(Vtiger_Request $request): void
    {
        Settings_MailManager_SetupWizard_Model::saveSkipped();
        $this->emitSuccess();
    }

    /**
     * Opens the setup again on the next visit.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function restart(Vtiger_Request $request): void
    {
        Settings_MailManager_SetupWizard_Model::clearState();
        $this->emitSuccess();
    }

    /**
     * Sends the success response.
     *
     * @return void
     */
    protected function emitSuccess(): void
    {
        $response = new Vtiger_Response();
        $response->setResult(['success' => true]);
        $response->emit();
    }
}
