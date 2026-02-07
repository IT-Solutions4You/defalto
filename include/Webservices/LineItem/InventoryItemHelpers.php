<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'modules/InventoryItem/helpers/Utils.php';
require_once 'modules/Core/models/Tax.php';

class InventoryItem_Webservice_Helpers
{
    /**
     * @param $value
     * @return int|null
     */
    public static function getCrmIdFromWsId($value): ?int
    {
        if (empty($value)) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int)$value;
        }

        if (is_string($value) && strpos($value, 'x') !== false) {
            [, $id] = explode('x', $value, 2);

            if (is_numeric($id)) {
                return (int)$id;
            }
        }

        return null;
    }

    /**
     * @param array $lineItem
     * @param int|null $productId
     * @return array
     * @throws Exception
     */
    public static function resolveTaxForLineItem(array $lineItem, ?int $productId): array
    {
        $taxId = self::getCrmIdFromWsId($lineItem['tax_id'] ?? $lineItem['taxid'] ?? null);
        $taxPercentage = $lineItem['tax'] ?? $lineItem['tax1'] ?? null;

        if ($taxId && $taxPercentage === null) {
            $taxModel = Core_Tax_Model::getInstanceById($taxId);

            if ($taxModel) {
                $taxPercentage = $taxModel->getTax();
            }
        }

        if (!$taxId && $productId) {
            try {
                $taxes = InventoryItem_Utils_Helper::getTaxesForProduct($productId);

                if ($taxPercentage !== null) {
                    $match = number_format((float)$taxPercentage, 2, '.', '');

                    foreach ($taxes as $candidateId => $taxData) {
                        if (number_format((float)$taxData['percentage'], 2, '.', '') === $match) {
                            $taxId = (int)$candidateId;
                            break;
                        }
                    }
                }

                if (!$taxId && count($taxes) === 1) {
                    $taxId = (int)array_key_first($taxes);

                    if ($taxPercentage === null) {
                        $taxPercentage = $taxes[$taxId]['percentage'] ?? null;
                    }
                }
            } catch (Exception $e) {
                // ignore tax lookup failure
            }
        }

        if (!$taxId && $taxPercentage !== null) {
            try {
                foreach (Core_Tax_Model::getAllTaxes() as $candidate) {
                    if (!$candidate->isActive()) {
                        continue;
                    }

                    $candidatePercentage = number_format((float)$candidate->getTax(), 2, '.', '');

                    if ($candidatePercentage === number_format((float)$taxPercentage, 2, '.', '')) {
                        $taxId = $candidate->getId();
                        break;
                    }
                }
            } catch (Exception $e) {
                // ignore tax lookup failure
            }
        }

        return [
            'taxId' => $taxId,
            'percentage' => $taxPercentage !== null ? (float)$taxPercentage : null,
        ];
    }

    /**
     * @param array $lineItem
     * @param int|null $productId
     * @param array $options
     * @return string|null
     */
    public static function resolveItemText(array $lineItem, ?int $productId, array $options = []): ?string
    {
        if (!empty($lineItem['item_text'])) {
            return $lineItem['item_text'];
        }

        if (!empty($lineItem['product_name'])) {
            return $lineItem['product_name'];
        }

        if (!empty($lineItem['productname'])) {
            return $lineItem['productname'];
        }

        if ($productId) {
            $entityType = getSalesEntityType($productId);
            $name = getEntityName($entityType, $productId)[$productId] ?? null;

            if (!empty($name)) {
                return $name;
            }
        }

        $includeDescription = !empty($options['include_description']);

        if ($includeDescription) {
            if (!empty($lineItem['description'])) {
                return $lineItem['description'];
            }

            if (!empty($lineItem['comment'])) {
                return $lineItem['comment'];
            }
        }

        if (array_key_exists('default', $options)) {
            return $options['default'];
        }

        return null;
    }

    /**
     * @param Vtiger_Record_Model $itemModel
     * @param array $lineItem
     * @return void
     */
    public static function applyDiscountData(Vtiger_Record_Model $itemModel, array $lineItem): void
    {
        $discountType = $lineItem['discount_type'] ?? null;
        $discountPercent = $lineItem['discount_percent'] ?? null;
        $discountAmount = $lineItem['discount_amount'] ?? null;
        $discount = $lineItem['discount'] ?? null;

        if ($discountType) {
            $itemModel->set('discount_type', $discountType);
        } elseif ($discountPercent !== null) {
            $itemModel->set('discount_type', 'Percentage');
        } elseif ($discountAmount !== null) {
            $itemModel->set('discount_type', 'Direct');
        } elseif ($discount !== null) {
            $itemModel->set('discount_type', 'Percentage');
        }

        if ($discountPercent !== null) {
            $itemModel->set('discount', $discountPercent);
        } elseif ($discount !== null) {
            $itemModel->set('discount', $discount);
        }

        if ($discountAmount !== null) {
            $itemModel->set('discount_amount', $discountAmount);
        }

        if (!empty($lineItem['overall_discount'])) {
            $itemModel->set('overall_discount', $lineItem['overall_discount']);
        }
    }
}