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
 * Derives the status of an incoming payment from its payment date and due date.
 */
class IncomingPayment_Status_Helper
{
    /**
     * Returns the payment status for the given dates (database format Y-m-d or empty).
     *
     * Received: payment date is today or in the past.
     * Waiting:  payment date is in the future, or no payment date and the due date has not passed yet.
     * Pending:  no payment date and the due date has passed.
     * Created:  no dates.
     *
     * @param string      $paymentDate Payment date, Y-m-d or empty
     * @param string      $dueDate     Due date, Y-m-d or empty
     * @param string|null $today       Today's date, Y-m-d; the current user's date when null
     * @return string
     */
    public static function getStatus(string $paymentDate, string $dueDate, ?string $today = null): string
    {
        $today = $today ?? self::getToday();

        if ($paymentDate !== '') {
            return $paymentDate <= $today
                ? IncomingPayment_Config_Helper::STATUS_RECEIVED
                : IncomingPayment_Config_Helper::STATUS_WAITING;
        }

        if ($dueDate !== '') {
            return $dueDate >= $today
                ? IncomingPayment_Config_Helper::STATUS_WAITING
                : IncomingPayment_Config_Helper::STATUS_PENDING;
        }

        return IncomingPayment_Config_Helper::STATUS_CREATED;
    }

    /**
     * Returns today's date (Y-m-d) in the time zone of the current user, or of the server when the user has none.
     * Date-only payment fields are never time-zone converted, so they are compared with the user's calendar date.
     *
     * @return string
     */
    public static function getToday(): string
    {
        $timeZone = '';
        $currentUser = Users_Record_Model::getCurrentUserModel();

        if ($currentUser) {
            $timeZone = (string)$currentUser->get('time_zone');
        }

        if (!in_array($timeZone, DateTimeZone::listIdentifiers(), true)) {
            $timeZone = date_default_timezone_get();
        }

        return (new DateTime('now', new DateTimeZone($timeZone)))->format('Y-m-d');
    }

    /**
     * Normalises a date coming from the request (user format) or the database (Y-m-d) into Y-m-d; empty when unknown.
     *
     * @param mixed $value Date value
     * @return string
     */
    public static function normalizeDate(mixed $value): string
    {
        $value = trim((string)$value);

        if ($value === '' || $value === '0000-00-00') {
            return '';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        $converted = (string)getValidDBInsertDateValue($value);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $converted) ? $converted : '';
    }
}
