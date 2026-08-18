<?php
/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Vtiger_DateFilter_Helper
{
    /**
     * Resolve an inclusive relative period ending on the supplied date.
     *
     * The stored value uses the format "quantity|unit", for example "3|month".
     */
    public static function getRelativePeriod(
        string $value,
        DateTimeInterface $today,
        string|false $userPreferredDayOfTheWeek = false
    ): array {
        [$quantity, $unit] = array_pad(explode('|', $value, 2), 2, '');
        $quantity = max(1, min(10000, (int)$quantity));
        $units = ['day', 'week', 'month', 'quarter', 'year'];

        if (!in_array($unit, $units, true)) {
            $unit = 'day';
        }

        $start = DateTime::createFromInterface($today);
        $end = $today->format('Y-m-d');

        if ('day' === $unit) {
            $start->modify(sprintf('-%d days', $quantity - 1));
        } elseif ('week' === $unit) {
            $weekStart = $userPreferredDayOfTheWeek ?: 'Sunday';
            $todayName = $start->format('l');

            if ($todayName !== $weekStart) {
                $start->modify(sprintf('previous %s', $weekStart));
            }

            $start->modify(sprintf('-%d weeks', $quantity - 1));
        } elseif ('month' === $unit) {
            $start->modify('first day of this month')->modify(sprintf('-%d months', $quantity - 1));
        } elseif ('quarter' === $unit) {
            $quarterStartMonth = ((int)floor(((int)$start->format('n') - 1) / 3) * 3) + 1;
            $start->setDate((int)$start->format('Y'), $quarterStartMonth, 1);
            $start->modify(sprintf('-%d months', ($quantity - 1) * 3));
        } else {
            $start->setDate((int)$start->format('Y') - $quantity + 1, 1, 1);
        }

        return [$start->format('Y-m-d'), $end];
    }
}
