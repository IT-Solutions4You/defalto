<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_DateFilter_Helper
{
    /**
     * Resolve an inclusive relative period ending on the supplied date.
     *
     * The stored value uses the format "quantity|unit", for example "3|month".
     */
    public static function getRelativePeriod(
        string $value,
        DateTimeInterface $today,
        string|false|null $userPreferredDayOfTheWeek = false
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

    public static function getGroupingPeriod(
        DateTimeInterface $date,
        string $interval,
        string|false|null $userPreferredDayOfTheWeek = false
    ): array {
        $start = DateTime::createFromInterface($date);
        $end = DateTime::createFromInterface($date);

        if ('week' === $interval) {
            $weekStart = $userPreferredDayOfTheWeek ?: 'Sunday';

            if ($start->format('l') !== $weekStart) {
                $start->modify(sprintf('previous %s', $weekStart));
            }

            $end = clone $start;
            $end->modify('+6 days');
        } elseif ('month' === $interval) {
            $start->modify('first day of this month');
            $end->modify('last day of this month');
        } elseif ('quarter' === $interval) {
            $quarterStartMonth = ((int)floor(((int)$start->format('n') - 1) / 3) * 3) + 1;
            $start->setDate((int)$start->format('Y'), $quarterStartMonth, 1);
            $end = clone $start;
            $end->modify('+2 months')->modify('last day of this month');
        } elseif ('year' === $interval) {
            $start->setDate((int)$start->format('Y'), 1, 1);
            $end->setDate((int)$end->format('Y'), 12, 31);
        }

        return [$start->format('Y-m-d'), $end->format('Y-m-d')];
    }
}
