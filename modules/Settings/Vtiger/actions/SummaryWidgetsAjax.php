<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Vtiger_SummaryWidgetsAjax_Action extends Settings_Vtiger_Index_Action
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('addWidget');
        $this->exposeMethod('removeWidget');
        $this->exposeMethod('saveOrder');
        $this->exposeMethod('getListWidgetOptions');
        $this->exposeMethod('saveListWidget');
    }

    public function checkPermission(Vtiger_Request $request): bool
    {
        parent::checkPermission($request);

        if (!$request->get('sourceModule')) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', $request->getModule(false)));
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();

        if ($mode) {
            echo $this->invokeExposedMethod($mode, $request);
        }
    }

    public function addWidget(Vtiger_Request $request): void
    {
        $model = Settings_Vtiger_SummaryWidgets_Model::getInstance($request->get('sourceModule'));
        $widget = $model->addWidget((string)$request->get('widgetLabel'));
        $this->emitResult('LBL_SUMMARY_WIDGET_ADDED', $request, $this->getWidgetResult($widget, $request));
    }

    public function removeWidget(Vtiger_Request $request): void
    {
        $model = Settings_Vtiger_SummaryWidgets_Model::getInstance($request->get('sourceModule'));
        $isAvailable = $model->removeWidget((int)$request->get('linkId'));
        $this->emitResult('LBL_SUMMARY_WIDGET_REMOVED', $request, ['available' => $isAvailable]);
    }

    public function saveOrder(Vtiger_Request $request): void
    {
        $model = Settings_Vtiger_SummaryWidgets_Model::getInstance($request->get('sourceModule'));
        $model->updateOrder((array)$request->get('leftLinkIds'), (array)$request->get('rightLinkIds'));
        $this->emitResult('LBL_SUMMARY_WIDGET_ORDER_SAVED', $request);
    }

    public function getListWidgetOptions(Vtiger_Request $request): void
    {
        Settings_Vtiger_SummaryWidgets_Model::getInstance((string)$request->get('sourceModule'));
        $response = new Vtiger_Response();
        $response->setResult(Settings_Vtiger_SummaryWidgets_Model::getListWidgetEditorOptions(
            (string)$request->get('sourceModule'),
            (string)$request->get('targetModule')
        ));
        $response->emit();
    }

    public function saveListWidget(Vtiger_Request $request): void
    {
        $model = Settings_Vtiger_SummaryWidgets_Model::getInstance((string)$request->get('sourceModule'));
        $linkId = (int)$request->get('linkId');
        $widget = $linkId
            ? $model->updateListWidget($linkId, $request)
            : $model->createListWidget($request);
        $label = $linkId ? 'LBL_SUMMARY_LIST_WIDGET_UPDATED' : 'LBL_SUMMARY_LIST_WIDGET_SAVED';
        $this->emitResult($label, $request, $this->getWidgetResult($widget, $request));
    }

    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }

    protected function emitResult(string $label, Vtiger_Request $request, array $result = []): void
    {
        $response = new Vtiger_Response();
        $response->setResult(array_merge([
            'message' => vtranslate($label, $request->getModule(false)),
        ], $result));
        $response->emit();
    }

    protected function getWidgetResult(Vtiger_Link_Model $widget, Vtiger_Request $request): array
    {
        $sourceModule = (string)$request->get('sourceModule');

        return [
            'widget' => [
                'linkId' => (int)$widget->getId(),
                'label' => $widget->getLabel(),
                'title' => vtranslate($widget->getLabel(), $sourceModule),
                'sequence' => (int)$widget->get('sequence'),
                'editable' => (bool)$widget->get('editable'),
            ],
        ];
    }
}
