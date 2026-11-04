<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_QueryGenerator_Model extends Core_QueryGenerator_Model
{
    public function parseAdvFilterList($advFilterList, $glue = '')
    {
        $hasTodayEventRange = false;
        $groupKeys = array_keys($advFilterList);

        foreach ($advFilterList as $groupIndex => $group) {
            $columns = (array)($group['columns'] ?? []);
            $todayFields = [];
            $groupPosition = array_search($groupIndex, $groupKeys, true);
            $isLastGroup = $groupPosition === count($groupKeys) - 1;
            $previousGroupCondition = 0 === $groupPosition
                ? strtolower(QueryGenerator::$AND)
                : strtolower((string)($advFilterList[$groupKeys[$groupPosition - 1]]['condition'] ?? ''));

            foreach ($columns as $condition) {
                $column = explode(':', (string)($condition['columnname'] ?? ''));

                if ('today' === ($condition['comparator'] ?? '')
                    && in_array($column[2] ?? '', ['datetime_start', 'datetime_end'], true)) {
                    $todayFields[] = $column[2];
                }
            }

            sort($todayFields);

            if ($isLastGroup
                && strtolower(QueryGenerator::$AND) === $previousGroupCondition
                && 2 === count($columns)
                && ['datetime_end', 'datetime_start'] === array_values(array_unique($todayFields))) {
                unset($advFilterList[$groupIndex]);
                $hasTodayEventRange = true;
            }
        }

        parent::parseAdvFilterList($advFilterList, $glue);

        if ($hasTodayEventRange) {
            [$utcStart, $utcEnd] = Appointments_Dates_Helper::getTodayDatabaseRangeForUser();
            $this->addTodayCondition(Appointments_Dates_Helper::getTodayDateForUser(), $utcStart, $utcEnd);
        }
    }

    public function addCalendarRangeCondition(
        string $startDate,
        string $endDate,
        string $utcStart,
        string $utcEnd
    ): self {
        [$allDay, $start, $end] = $this->getAppointmentDateColumns();
        $calendarStart = $startDate . ' 00:00:00';
        $calendarEnd = (new DateTimeImmutable($endDate))->modify('-1 day')->format('Y-m-d 00:00:00');

        return $this->addStructuredWhereCondition(
            sprintf(
                '((%1$s IN (?,?) AND %2$s <= ? AND %3$s >= ?) OR '
                . '((%1$s IS NULL OR %1$s NOT IN (?,?)) AND %2$s < ? AND %3$s >= ?))',
                $allDay,
                $start,
                $end
            ),
            ['1', 'Yes', $calendarEnd, $calendarStart, '1', 'Yes', $utcEnd, $utcStart]
        );
    }

    public function addTodayCondition(string $date, string $utcStart, string $utcEnd): self
    {
        [$allDay, $start, $end] = $this->getAppointmentDateColumns();
        $calendarDate = $date . ' 00:00:00';

        return $this->addStructuredWhereCondition(
            sprintf(
                '((%1$s IN (?,?) AND %2$s <= ? AND %3$s >= ?) OR '
                . '((%1$s IS NULL OR %1$s NOT IN (?,?)) AND %2$s < ? AND %3$s >= ?))',
                $allDay,
                $start,
                $end
            ),
            ['1', 'Yes', $calendarDate, $calendarDate, '1', 'Yes', $utcEnd, $utcStart]
        );
    }

    public function addUpcomingTodayCondition(string $date, string $utcNow): self
    {
        [$allDay, $start, $end] = $this->getAppointmentDateColumns();
        $calendarDate = $date . ' 00:00:00';

        return $this->addStructuredWhereCondition(
            sprintf(
                '((%1$s IN (?,?) AND %2$s <= ? AND %3$s >= ?) OR '
                . '((%1$s IS NULL OR %1$s NOT IN (?,?)) AND %2$s >= ?))',
                $allDay,
                $start,
                $end
            ),
            ['1', 'Yes', $calendarDate, $calendarDate, '1', 'Yes', $utcNow]
        );
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    protected function getAppointmentDateColumns(): array
    {
        $fields = $this->getModuleFields();

        foreach (['is_all_day', 'datetime_start', 'datetime_end'] as $fieldName) {
            if (!isset($fields[$fieldName])) {
                throw new InvalidArgumentException('Unknown Appointments field: ' . $fieldName);
            }

            $this->addWhereField($fieldName);
        }

        return [
            $this->getQualifiedColumn($fields['is_all_day']->getTableName(), $fields['is_all_day']->getColumnName()),
            $this->getQualifiedColumn($fields['datetime_start']->getTableName(), $fields['datetime_start']->getColumnName()),
            $this->getQualifiedColumn($fields['datetime_end']->getTableName(), $fields['datetime_end']->getColumnName()),
        ];
    }
}
