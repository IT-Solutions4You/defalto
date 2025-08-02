<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Core_Taxes_Action extends Settings_Vtiger_Index_Action
{
    /**
     * @throws Exception
     */
    public function delete(Vtiger_Request $request): void
    {
        $success = true;
        $message = 'LBL_TAX_DELETED';

        $tax = Core_Tax_Model::getInstanceFromRequest($request);

        if (!$tax || !$tax->get('tax_id')) {
            $success = false;
        } else {
            $tax->delete();
        }

        $response = new Vtiger_Response();
        $response->setResult([
            'success' => $success,
            'message' => vtranslate($message, $request->getModule(false)),
        ]);
        $response->emit();
    }

    /**
     * @throws Exception
     */
    public function deleteRegion(Vtiger_Request $request): void
    {
        PearDatabase::getInstance()->setDieOnError(1);

        $success = true;
        $message = 'LBL_TAX_REGION_DELETED';
        $qualifiedModule = $request->getModule(false);
        $region = Core_TaxRegion_Model::getInstanceFromRequest($request);

        if (!$region || !$region->get('region_id')) {
            $success = false;
        } else {
            $region->delete();
        }

        $response = new Vtiger_Response();
        $response->setResult([
            'success' => $success,
            'message' => vtranslate($message, $qualifiedModule),
        ]);
        $response->emit();
    }

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request): void
    {
        $mode = $request->get('mode');

        if ($mode && $this->isMethodExposed($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function save(Vtiger_Request $request): void
    {
        $label = $request->get('tax_label');
        $success = true;
        $message = 'LBL_TAXES_UPDATED';

        if (empty($label)) {
            $success = false;
        }

        $tax = Core_Tax_Model::getInstanceFromRequest($request);
        $tax->retrieveFromRequest($request);

        if ($tax->isDuplicateName() || ($request->isEmpty('record') && !$tax->isEmpty('tax_id'))) {
            $success = false;
            $message = 'LBL_TAX_ALREADY_EXISTS';
        } else {
            $tax->save();
        }

        $response = new Vtiger_Response();
        $response->setResult([
            'success' => $success,
            'message' => vtranslate($message, $request->getModule(false)),
            'info'    => [
                'id'     => $tax->getId(),
                'label'  => $tax->getName(),
                'value'  => $tax->getTax(),
                'method' => $tax->getTaxMethod(),
                'active' => $tax->get('active'),
            ],
        ]);
        $response->emit();
    }

    public function status(Vtiger_Request $request): void
    {
        $recordId = $request->get('record');
        $success = true;
        $message = 'LBL_TAXES_UPDATED';

        if (empty($recordId)) {
            $success = false;
        } else {
            $tax = Core_Tax_Model::getInstanceFromRequest($request);
            $tax->set('active', $request->get('value'));
            $tax->save();
        }

        $response = new Vtiger_Response();
        $response->setResult([
            'success' => $success,
            'message' => vtranslate($message, $request->getModule(false)),
        ]);
        $response->emit();
    }

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('save');
        $this->exposeMethod('saveRegion');
        $this->exposeMethod('delete');
        $this->exposeMethod('deleteRegion');
        $this->exposeMethod('status');
        $this->exposeMethod('updateTaxes');
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function saveRegion(Vtiger_Request $request): void
    {
        $name = $request->get('name');
        $success = true;
        $message = 'LBL_TAXES_REGION_UPDATED';

        if (empty($name)) {
            $success = false;
        }

        $region = Core_TaxRegion_Model::getInstanceFromRequest($request);
        $region->retrieveFromRequest($request);

        if ($region->isDuplicateName()) {
            $success = false;
            $message = 'LBL_TAX_REGION_ALREADY_EXISTS';
        } else {
            $region->save();
        }

        $response = new Vtiger_Response();
        $response->setResult([
            'success' => $success,
            'message' => vtranslate($message, $request->getModule(false)),
            'info'    => [
                'id'   => $region->getId(),
                'name' => $region->getName(),
            ],
        ]);
        $response->emit();
    }

    /**
     * @throws Exception
     */
    public function updateTaxes(Vtiger_Request $request)
    {
        $recordId = (int)$request->get('record');

        $tax = Core_Tax_Model::getInstanceById($recordId);
        $tax->updateRecordTaxes();

        $response = new Vtiger_Response();
        $response->setResult([
            'success' => true,
            'message' => vtranslate('LBL_UPDATED_RECORD_TAXES', $request->getModule(false)),
        ]);
        $response->emit();
    }
}