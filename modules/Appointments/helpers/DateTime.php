<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_DateTime_Helper
{
    public static function setAllDayDatesFromRequest(Vtiger_Request $request, Appointments_Record_Model $recordModel): void
    {
        if (!$recordModel->isAllDayEvent()) {
            return;
        }

        $recordModel->setAllDayDates(
            self::getDateFromRequest($request, 'datetime_start'),
            self::getDateFromRequest($request, 'datetime_end')
        );
    }

    protected static function getDateFromRequest(Vtiger_Request $request, string $fieldName): string
    {
        $date = trim((string)$request->get($fieldName . '_date'));

        if ('' !== $date) {
            return $date;
        }

        return preg_split('/\s+/', trim((string)$request->get($fieldName)), 2)[0] ?? '';
    }
}
