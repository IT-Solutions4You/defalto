<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Saves the integration settings of a payment module. Each payment module extends it as
 * Settings_<Module>_IntegrationSave_Action.
 */
abstract class Settings_Core_PaymentIntegrationSave_Action extends Settings_Vtiger_IndexAjax_View
{
    /**
     * Returns the configuration of the payment module this action belongs to.
     *
     * @return Core_PaymentConfig_Helper
     * @throws Exception
     */
    protected function getConfig(): Core_PaymentConfig_Helper
    {
        return Core_PaymentConfig_Helper::getForClass(static::class);
    }

    /**
     * Write action: POST with a CSRF token only.
     *
     * @param Vtiger_Request $request
     * @return bool
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }

    /**
     * Stores the submitted integration configuration and the underpayment threshold, re-registers the document
     * module integrations and recalculates the documents with payments when the threshold, an integration or
     * a refund role changed.
     * A document module may be paid by one payment module only (validatePayingModules()).
     *
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $config = $this->getConfig();
        $threshold = $this->getUnderpaymentThreshold($request);
        $integration = $request->get('integration');
        $integration = is_array($integration) ? $integration : [];
        $this->validatePayingModules($request, $integration);
        $installModel = Core_Install_Model::getInstance('module.postupdate', $config::MODULE);
        $previousRoles = $this->getIntegrationRoles();
        // The old configuration is removed first, so a changed paid field of a document becomes editable again.
        $installModel->updateIntegrations(false);
        $config->getIntegrationModel()->saveFromRequest($integration);
        $installModel->updateIntegrations();

        $settingModel = $config->getSettingModel();
        $isRecalculationNeeded = $previousRoles !== $this->getIntegrationRoles();

        if (!Core_PaymentCalculation_Helper::isSameAmount($settingModel->getUnderpaymentThreshold(), $threshold)) {
            $settingModel->saveUnderpaymentThreshold($threshold);
            $isRecalculationNeeded = true;
        }

        if ($isRecalculationNeeded) {
            $config->getDocumentSyncModel()->recalculateAllDocuments();
        }

        $response = new Vtiger_Response();
        $response->setResult(['success' => true]);
        $response->emit();
    }

    /**
     * Returns the active and refund flags of every document module, to detect a change that alters the paid amounts.
     *
     * @return array [module => [active, is_refund]]
     */
    private function getIntegrationRoles(): array
    {
        $roles = [];

        foreach ($this->getConfig()->getIntegrationModel()->retrieveAll() as $module => $row) {
            $roles[$module] = [(int)$row['active'], (int)$row[Core_PaymentConfig_Helper::INTEGRATION_REFUND]];
        }

        return $roles;
    }

    /**
     * Refuses a configuration in which a document module would have no paying module or two of them: a document has
     * exactly one paying module, the payments of the others are refunds.
     *
     * @param Vtiger_Request $request     Save request
     * @param array          $integration Submitted configuration [module => values]
     * @return void
     * @throws Exception
     */
    private function validatePayingModules(Vtiger_Request $request, array $integration): void
    {
        $config = $this->getConfig();
        $twoPaying = $nonePaying = [];

        foreach ($integration as $module => $values) {
            if (!is_array($values) || empty($values['active'])) {
                continue;
            }

            $isRefund = !empty($values[Core_PaymentConfig_Helper::INTEGRATION_REFUND]);
            $otherPayingModule = Core_PaymentDocumentBalance_Model::getPayingModule((string)$module, [$config::MODULE]);

            if (!$isRefund && '' !== $otherPayingModule) {
                $twoPaying[] = vtranslate((string)$module, (string)$module);
            } elseif ($isRefund && '' === $otherPayingModule) {
                $nonePaying[] = vtranslate((string)$module, (string)$module);
            }
        }

        if ($twoPaying) {
            throw new Exception(sprintf(vtranslate('LBL_TWO_PAYING_MODULES', $request->getModule(false)), implode(', ', $twoPaying)));
        }

        if ($nonePaying) {
            throw new Exception(sprintf(vtranslate('LBL_NO_PAYING_MODULE', $request->getModule(false)), implode(', ', $nonePaying)));
        }
    }

    /**
     * Returns the submitted underpayment threshold: a plain number with a dot, not negative; empty means 0.
     *
     * @param Vtiger_Request $request
     * @return float
     * @throws Exception
     */
    private function getUnderpaymentThreshold(Vtiger_Request $request): float
    {
        $value = trim((string)$request->get('underpayment_threshold'));

        if ($value === '') {
            return 0.0;
        }

        if (!is_numeric($value) || (float)$value < 0) {
            throw new Exception(vtranslate('LBL_INVALID_UNDERPAYMENT_THRESHOLD', $request->getModule(false)));
        }

        return (float)$value;
    }
}
