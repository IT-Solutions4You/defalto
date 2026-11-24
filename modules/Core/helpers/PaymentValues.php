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
 * Checks a payment before the save and fills its read-only derived fields right after the record is written.
 * The entity save only writes fields with displaytype 1, 3, 4 and 6, so read-only (displaytype 2) columns such as
 * the status must be written with a direct update once the record exists. The currency, the conversion rate and the
 * amount in the base currency belong to the amount with its own currency (uitype 35) and are kept by the core
 * (Core_AmountCurrency_Model, called from CRMEntity::saveentity()).
 */
class Core_PaymentValues_Helper
{
    /**
     * Rejects adding documents beyond the limit (Core_PaymentLicense_Helper::getReferenceLimit()) on a payment
     * being saved. A payment that already references more documents (from an import or from the paid edition before
     * its licence expired) can be saved again as long as no document is added, so the daily status update keeps
     * working. The organisation and the contact are not taken from the reference document on save; only a payment
     * created from a document gets them prefilled in the form.
     *
     * @param VTEntityData $entityData Payment entity data of the before-save event
     * @return void
     * @throws Exception
     */
    public static function validateReferences(VTEntityData $entityData): void
    {
        $relationIds = Core_Multireference_UIType::getRecordIds(self::getValue($entityData, Core_PaymentConfig_Helper::FIELD_RELATION));

        if (count($relationIds) <= Core_PaymentLicense_Helper::getReferenceLimit()) {
            return;
        }

        $paymentModule = (string)$entityData->getModuleName();
        $paymentId = (int)$entityData->getId();
        $previousIds = $paymentId > 0 ? Core_PaymentConfig_Helper::getInstance($paymentModule)->getAssociatedModel()->fetchRecordIds($paymentId) : [];

        if (array_diff($relationIds, array_map('intval', $previousIds))) {
            throw new Exception(Core_PaymentLicense_Helper::getReferenceLimitMessage($paymentModule));
        }
    }

    /**
     * Returns the read-only derived columns of a saved payment: the status, the numbers of the reference documents
     * and the direction of its module.
     *
     * @param Vtiger_Record_Model $payment Saved payment record
     * @return array [column => value]
     * @throws Exception
     */
    public static function getReadonlyValues(Vtiger_Record_Model $payment): array
    {
        return [
            Core_PaymentConfig_Helper::FIELD_STATUS => Core_PaymentStatus_Helper::getStatus(
                Core_PaymentStatus_Helper::normalizeDate($payment->get(Core_PaymentConfig_Helper::FIELD_PAYMENT_DATE)),
                Core_PaymentStatus_Helper::normalizeDate($payment->get(Core_PaymentConfig_Helper::FIELD_DUE_DATE))
            ),
            Core_PaymentConfig_Helper::FIELD_REFERENCE_NO => implode(', ', self::getDocumentNumbers(self::getDocumentIds($payment))),
            Core_PaymentConfig_Helper::FIELD_DIRECTION => Core_PaymentConfig_Helper::getInstance($payment->getModuleName())::DIRECTION,
        ];
    }

    /**
     * Returns the existing documents of the reference field of a payment, in the stored order.
     *
     * @param Vtiger_Record_Model $payment Payment record
     * @return int[]
     */
    public static function getDocumentIds(Vtiger_Record_Model $payment): array
    {
        $documentIds = Core_Multireference_UIType::getRecordIds($payment->get(Core_PaymentConfig_Helper::FIELD_RELATION));

        return array_values(array_filter($documentIds, 'isRecordExists'));
    }

    /**
     * Returns the record numbers of documents.
     *
     * @param int[] $documentIds Document record ids
     * @return string[]
     * @throws Exception
     */
    private static function getDocumentNumbers(array $documentIds): array
    {
        $numbers = [];

        foreach ($documentIds as $documentId) {
            $numbers[] = self::getRecordNumber(Vtiger_Record_Model::getInstanceById($documentId, (string)getSalesEntityType($documentId)));
        }

        return $numbers;
    }

    /**
     * Returns the record number of a document (its auto-number field), or its name when it has none.
     *
     * @param Vtiger_Record_Model $recordModel Document record
     * @return string
     */
    public static function getRecordNumber(Vtiger_Record_Model $recordModel): string
    {
        foreach ($recordModel->getModule()->getFields() as $fieldName => $fieldModel) {
            if ((int)$fieldModel->get('uitype') === 4) {
                return (string)$recordModel->get($fieldName);
            }
        }

        return (string)$recordModel->getName();
    }

    /**
     * Returns a field value of the entity data as a trimmed string; empty when the field is not set.
     *
     * @param VTEntityData $entityData Entity data
     * @param string       $fieldName  Field name
     * @return string
     */
    private static function getValue(VTEntityData $entityData, string $fieldName): string
    {
        $fields = $entityData->getData();
        $value = isset($fields[$fieldName]) ? $fields[$fieldName] : '';

        return is_scalar($value) ? trim((string)$value) : '';
    }
}
