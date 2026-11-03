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
 * Fills the derived fields of a payment: editable ones before the save, read-only ones right after the record is written.
 * The entity save only writes fields with displaytype 1, 3, 4 and 6, so read-only (displaytype 2)
 * columns such as the status must be written with a direct update once the record exists.
 */
class IncomingPayment_PaymentValues_Helper
{
    /**
     * Sets the editable derived values on the entity data of a payment being saved:
     * currency fallback, conversion rate, organisation and contact of the reference document.
     *
     * @param VTEntityData $entityData Payment entity data of the before-save event
     * @return void
     * @throws Exception
     */
    public static function setEditableValues(VTEntityData $entityData): void
    {
        $currencyId = (int)self::getValue($entityData, 'currency_id');

        if ($currencyId <= 0) {
            $currencyId = (int)CurrencyField::getDBCurrencyId();
            $entityData->set('currency_id', $currencyId);
        }

        if (self::isRateRefreshRequired($entityData, $currencyId)) {
            $entityData->set('conversion_rate', IncomingPayment_Calculation_Helper::getConversionRate($currencyId));
        }

        $relationIds = Core_Multireference_UIType::getRecordIds(self::getValue($entityData, IncomingPayment_Config_Helper::FIELD_RELATION));

        if (count($relationIds) > IncomingPayment_Config_Helper::REFERENCE_LIMIT) {
            throw new Exception(vtranslate(IncomingPayment_Config_Helper::LBL_REFERENCE_LIMIT, IncomingPayment_Config_Helper::MODULE));
        }

        $relationId = (int)($relationIds[0] ?? 0);

        if ($relationId <= 0 || !isRecordExists($relationId)) {
            return;
        }

        $module = (string)getSalesEntityType($relationId);
        $recordModel = Vtiger_Record_Model::getInstanceById($relationId, $module);
        $config = IncomingPayment_Integration_Model::getInstance()->retrieveByModule($module);
        $relatedToField = (string)($config['relatedto_field'] ?? '');

        if (self::getValue($entityData, IncomingPayment_Config_Helper::FIELD_RELATED_TO) === '' && $relatedToField !== '' && $recordModel->getModule()->getField($relatedToField)) {
            $entityData->set(IncomingPayment_Config_Helper::FIELD_RELATED_TO, $recordModel->get($relatedToField));
        }

        if (self::getValue($entityData, IncomingPayment_Config_Helper::FIELD_CONTACT) === '' && $recordModel->getModule()->getField('contact_id')) {
            $entityData->set(IncomingPayment_Config_Helper::FIELD_CONTACT, $recordModel->get('contact_id'));
        }
    }

    /**
     * Tells whether the payment needs the current rate of its currency. An existing payment keeps its stored rate,
     * so editing an old payment does not change its base currency amount or the paid amount of its document.
     *
     * @param VTEntityData $entityData Payment entity data
     * @param int          $currencyId Currency id being saved
     * @return bool
     */
    private static function isRateRefreshRequired(VTEntityData $entityData, int $currencyId): bool
    {
        if ($entityData->isNew() || (float)self::getValue($entityData, 'conversion_rate') <= 0) {
            return true;
        }

        return IncomingPayment_DocumentSync_Model::getInstance()->fetchStoredCurrencyId((int)$entityData->getId()) !== $currencyId;
    }

    /**
     * Returns the read-only derived columns of a saved payment: conversion rate, status, base currency amount
     * and the number of the reference document.
     *
     * @param Vtiger_Record_Model $payment Saved payment record
     * @return array [column => value]
     * @throws Exception
     */
    public static function getReadonlyValues(Vtiger_Record_Model $payment): array
    {
        $amount = IncomingPayment_Calculation_Helper::parseAmount($payment->get(IncomingPayment_Config_Helper::FIELD_AMOUNT));
        $rate = (float)$payment->get('conversion_rate');

        if ($rate <= 0) {
            $rate = IncomingPayment_Calculation_Helper::getConversionRate((int)$payment->get('currency_id'));
        }

        $values = [
            'conversion_rate' => $rate,
            IncomingPayment_Config_Helper::FIELD_STATUS => IncomingPayment_Status_Helper::getStatus(
                IncomingPayment_Status_Helper::normalizeDate($payment->get('paymentdate')),
                IncomingPayment_Status_Helper::normalizeDate($payment->get('due_date'))
            ),
            'payamount_main' => IncomingPayment_Calculation_Helper::roundStoredAmount(IncomingPayment_Calculation_Helper::convertToBaseCurrency($amount, $rate)),
            'associated_no' => implode(', ', self::getDocumentNumbers(self::getDocumentIds($payment))),
        ];

        return $values;
    }

    /**
     * Returns the existing documents of the reference field of a payment, in the stored order.
     *
     * @param Vtiger_Record_Model $payment Payment record
     * @return int[]
     */
    public static function getDocumentIds(Vtiger_Record_Model $payment): array
    {
        $documentIds = Core_Multireference_UIType::getRecordIds($payment->get(IncomingPayment_Config_Helper::FIELD_RELATION));

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
