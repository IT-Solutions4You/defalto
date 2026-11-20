<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'include/Webservices/VtigerModuleOperation.php';

/**
 * Webservice handler of Payments, registered in vtiger_ws_entity by Payments_Install_Model.
 * Core code such as CustomView loads the webservice metadata of the list module; Payments has no fields or tables,
 * so it returns the metadata of the primary payment module, whose field names all payment modules share.
 * Payments has no records, so every write operation is refused.
 */
class Payments_WebserviceOperation_Helper extends VtigerModuleOperation
{
    /**
     * Returns the webservice metadata of the primary payment module.
     *
     * @return VtigerCRMObjectMeta
     */
    protected function getMetaInstance()
    {
        $moduleName = Payments_Sources_Helper::getPrimaryModule();

        if (empty(WebserviceEntityOperation::$metaCache[$moduleName][$this->user->id])) {
            $webserviceObject = VtigerWebserviceObject::fromName($this->pearDB, $moduleName);
            WebserviceEntityOperation::$metaCache[$moduleName][$this->user->id] = new VtigerCRMObjectMeta($webserviceObject, $this->user);
        }

        return WebserviceEntityOperation::$metaCache[$moduleName][$this->user->id];
    }

    /**
     * Payments has no records to create.
     *
     * @param string $elementType Webservice element type
     * @param array  $element     Record values
     * @return array
     * @throws WebServiceException
     */
    public function create($elementType, $element)
    {
        throw new WebServiceException(WebServiceErrorCode::$OPERATIONNOTSUPPORTED, 'Payments has no records');
    }

    /**
     * Payments has no records to update.
     *
     * @param array $element Record values
     * @return array
     * @throws WebServiceException
     */
    public function update($element)
    {
        throw new WebServiceException(WebServiceErrorCode::$OPERATIONNOTSUPPORTED, 'Payments has no records');
    }

    /**
     * Payments has no records to revise.
     *
     * @param array $element Record values
     * @return array
     * @throws WebServiceException
     */
    public function revise($element)
    {
        throw new WebServiceException(WebServiceErrorCode::$OPERATIONNOTSUPPORTED, 'Payments has no records');
    }

    /**
     * Payments has no records to delete.
     *
     * @param string $id Webservice record id
     * @return array
     * @throws WebServiceException
     */
    public function delete($id)
    {
        throw new WebServiceException(WebServiceErrorCode::$OPERATIONNOTSUPPORTED, 'Payments has no records');
    }
}
