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
 * Search of the multi reference field (uitype 34) in edit forms: records of search_module whose name or record number
 * contains search_value. Permissions are the same as for the reference search (BasicAjax).
 */
class Core_MultireferenceSearch_Action extends Vtiger_BasicAjax_Action
{
    /**
     * Returns the matching records as [{id, label, number}]; parent_id and parent_module (sent by module edit scripts,
     * e.g. the organization for contacts) limit the search like in BasicAjax.
     *
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $searchModel = Core_MultireferenceSearch_Model::getInstance((string)$request->get('search_module'));
        $searchValue = trim((string)$request->get('search_value'));
        $records = [];

        if ($searchModel && '' !== $searchValue) {
            $records = $searchModel->getRecords(
                $searchValue,
                (int)$request->get('base_record'),
                (int)$request->get('parent_id'),
                (string)$request->get('parent_module'),
            );
        }

        $response = new Vtiger_Response();
        $response->setResult($records);
        $response->emit();
    }
}
