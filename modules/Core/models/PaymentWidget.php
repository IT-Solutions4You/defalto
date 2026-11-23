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
 * Decides which payments widget a document module shows and registers it: the common widget of the Payments list
 * while Payments is active, otherwise the widget of each integrated payment module; none without an integration.
 * Every payment module and Payments call it after their registrations change, so the result never depends on the
 * order in which the modules are installed.
 */
class Core_PaymentWidget_Model
{
    public const LINK_TYPE = 'DETAILVIEWWIDGET';
    public const WIDGET_SEQUENCE = 6;

    /**
     * Label of the common widget; it is also the name of the Payments list module.
     */
    public const COMMON_WIDGET_LABEL = Core_PaymentConfig_Helper::PAYMENTS_LIST_MODULE;

    /**
     * Returns a new instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * Returns the URL of the common payments widget; headerActions shows the add buttons of Payments in its header.
     *
     * @return string
     */
    public function getCommonWidgetUrl(): string
    {
        return 'module=' . self::COMMON_WIDGET_LABEL . '&view=Widget&mode=showPayments&sourceModule=$MODULE$&sourceRecord=$RECORD$'
            . '&headerActions=1';
    }

    /**
     * Registers the right payments widget on every document module of the installed payment modules and removes
     * the others.
     *
     * @param string[] $excludedModules Payment modules or Payments treated as inactive, e.g. while being disabled
     * @return void
     * @throws Exception
     */
    public function updateDocumentWidgets(array $excludedModules = []): void
    {
        $activeModules = Core_PaymentConfig_Helper::getActiveModules($excludedModules);
        $isCommonActive = !in_array(self::COMMON_WIDGET_LABEL, $excludedModules, true) && vtlib_isModuleActive(self::COMMON_WIDGET_LABEL);

        foreach ($this->getDocumentModules() as $documentModule) {
            $this->updateModuleWidgets($documentModule, $this->getWantedWidgets($documentModule, $activeModules, $isCommonActive));
        }
    }

    /**
     * Returns the document modules any installed payment module may integrate.
     *
     * @return string[]
     * @throws Exception
     */
    protected function getDocumentModules(): array
    {
        $modules = [];

        foreach (Core_PaymentConfig_Helper::getInstalledModules() as $paymentModule) {
            $modules = array_merge($modules, Core_PaymentConfig_Helper::getInstance($paymentModule)->getIntegrationModel()->getAvailableModules());
        }

        return array_values(array_unique($modules));
    }

    /**
     * Returns the widgets a document module should show, as [label => URL].
     *
     * @param string   $documentModule Document module name
     * @param string[] $activeModules  Active payment modules
     * @param bool     $isCommonActive Whether the Payments list is active
     * @return array<string, string>
     * @throws Exception
     */
    protected function getWantedWidgets(string $documentModule, array $activeModules, bool $isCommonActive): array
    {
        $widgets = [];

        foreach ($activeModules as $paymentModule) {
            $paymentConfig = Core_PaymentConfig_Helper::getInstance($paymentModule);

            if ($paymentConfig->getIntegrationModel()->isActive($documentModule)) {
                $widgets[$paymentConfig->getWidgetLabel()] = $paymentConfig->getWidgetUrl();
            }
        }

        if ($widgets && $isCommonActive) {
            return [self::COMMON_WIDGET_LABEL => $this->getCommonWidgetUrl()];
        }

        return $widgets;
    }

    /**
     * Adds the wanted payments widgets of a document module and removes its other payments widgets.
     * An existing wanted widget keeps its row, so the position and the hidden state set by users stay.
     *
     * @param string                $documentModule Document module name
     * @param array<string, string> $wantedWidgets  [label => URL]
     * @return void
     */
    protected function updateModuleWidgets(string $documentModule, array $wantedWidgets): void
    {
        $tabId = (int)getTabid($documentModule);

        if (!$tabId) {
            return;
        }

        $existingLabels = $this->getExistingLabels($tabId);

        foreach ($this->getManagedLabels() as $label) {
            if (!isset($wantedWidgets[$label]) && in_array($label, $existingLabels, true)) {
                Vtiger_Link::deleteLink($tabId, self::LINK_TYPE, $label);
            }
        }

        foreach ($wantedWidgets as $label => $url) {
            if (!in_array($label, $existingLabels, true)) {
                Vtiger_Link::addLink($tabId, self::LINK_TYPE, $label, $url, '', self::WIDGET_SEQUENCE);
            }
        }

        Vtiger_Cache::delete('links-' . $tabId, [self::LINK_TYPE]);
    }

    /**
     * Returns the labels of all payments widgets: the common one and the one of each payment module.
     *
     * @return string[]
     */
    protected function getManagedLabels(): array
    {
        $labels = [self::COMMON_WIDGET_LABEL];

        foreach (Core_PaymentConfig_Helper::PAYMENT_MODULES as $paymentModule) {
            $labels[] = $paymentModule;
        }

        return $labels;
    }

    /**
     * Returns the labels of the payments widgets registered on a document module.
     *
     * @param int $tabId Document module id
     * @return string[]
     */
    protected function getExistingLabels(int $tabId): array
    {
        $db = PearDatabase::getInstance();
        $labels = $this->getManagedLabels();
        $result = $db->pquery(
            'SELECT DISTINCT linklabel FROM vtiger_links WHERE tabid = ? AND linktype = ? AND linklabel IN (' . generateQuestionMarks($labels) . ')',
            [$tabId, self::LINK_TYPE, $labels]
        );
        $existingLabels = [];

        while ($row = $db->fetchByAssoc($result)) {
            $existingLabels[] = (string)$row['linklabel'];
        }

        return $existingLabels;
    }
}
