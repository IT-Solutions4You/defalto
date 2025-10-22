<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_Block_Model extends Vtiger_Block_Model
{
    public static array $customUITypeNames = [
        'LBL_TABS'           => 'Tabs',
        'LBL_RENDERED_TABLE' => 'Table',
    ];

    public static function isNavigationTab(string $value): bool
    {
        return !isset(self::$customUITypeNames[$value]);
    }

    public function getUITypeName(): string
    {
        return self::$customUITypeNames[$this->getLabel()] ?? parent::getUITypeName();
    }

    public function getIcon(): string
    {
        $label = $this->getLabel();

        return match ($label) {
            'LBL_COLUMNS' => '<i class="bi bi-layout-three-columns"></i>',
            'LBL_CALCULATIONS' => '<i class="bi bi-calculator"></i>',
            'LBL_LABELS' => '<i class="bi bi-bookmark"></i>',
            'LBL_FILTERS' => '<i class="bi bi-funnel"></i>',
            'LBL_SHARING' => '<i class="bi bi-share"></i>',
            default => '<i class="bi bi-list"></i>',
        };
    }
}