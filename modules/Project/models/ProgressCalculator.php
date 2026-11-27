<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Project_ProgressCalculator_Model extends Vtiger_Base_Model
{
    public static function getIntegerPercentage(mixed $value): int
    {
        return is_numeric($value) ? (int)round(max(0, min(100, (float)$value))) : 0;
    }

    public static function isValidOptionalIntegerPercentage(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (!is_numeric($value)) {
            return false;
        }

        $percentage = (float)$value;

        return is_finite($percentage) && $percentage >= 0 && $percentage <= 100 && floor($percentage) === $percentage;
    }

    public function getWeight(array $task, float $hoursPerDay, float $fallbackHours): float
    {
        $hours = (float)($task['planed_hours'] ?? 0);

        if (is_finite($hours) && $hours > 0) {
            return $hours;
        }

        $start = $this->getCalendarDate((string)($task['startdate'] ?? ''));
        $end = $this->getCalendarDate((string)($task['enddate'] ?? ''));

        if ($start && $end && $end >= $start) {
            return ((int)$start->diff($end)->format('%a') + 1) * $hoursPerDay;
        }

        return $fallbackHours;
    }

    protected function getCalendarDate(string $value): ?DateTimeImmutable
    {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', $value, $parts)
            || !checkdate((int)$parts[2], (int)$parts[3], (int)$parts[1])) {
            return null;
        }

        return new DateTimeImmutable($value, new DateTimeZone('UTC'));
    }

    public function hasValidWeights(float $hoursPerDay, float $fallbackHours): bool
    {
        return is_finite($hoursPerDay) && is_finite($fallbackHours) && $hoursPerDay > 0 && $fallbackHours > 0;
    }

    public function getProgress(iterable $tasks, array $completed, array $excluded, float $hoursPerDay, float $fallbackHours): int
    {
        if (!$this->hasValidWeights($hoursPerDay, $fallbackHours)) {
            throw new Exception(vtranslate('LBL_PROJECT_PROGRESS_TASK', 'ProjectTask') . ': ' . vJSTranslate('JS_PLEASE_ENTER_VALID_VALUE', 'Core'));
        }

        $totalWeight = 0.0;
        $weightedProgress = 0.0;

        foreach ($tasks as $task) {
            $status = (string)($task['projecttaskstatus'] ?? '');

            if (in_array($status, $excluded, true)) {
                continue;
            }

            $weight = $this->getWeight($task, $hoursPerDay, $fallbackHours);
            $progress = in_array($status, $completed, true) ? 100 : self::getIntegerPercentage($task['projecttaskprogress'] ?? '');
            $totalWeight += $weight;
            $weightedProgress += $weight * $progress;
        }

        return $totalWeight > 0 ? self::getIntegerPercentage($weightedProgress / $totalWeight) : 0;
    }
}
