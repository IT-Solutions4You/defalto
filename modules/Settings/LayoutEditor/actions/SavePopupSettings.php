<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_LayoutEditor_SavePopupSettings_Action extends Settings_Vtiger_Basic_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request): void
    {
        $response = new Vtiger_Response();

        try {
            $sourceModule = $request->get('sourceModule');
            $columnslist = $request->get('columnslist');

            if (!is_array($columnslist)) {
                $columnslist = !empty($columnslist) ? json_decode($columnslist, true) : [];
            }

            $model = Settings_LayoutEditor_PopupSettings_Model::getInstance();
            $model->set('moduleName', $sourceModule);
            $model->set('columnslist', $columnslist);
            $model->save();

            $response->setResult(true);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }

        $response->emit();
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}