<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

$languageStrings = [
    'OutgoingPayment' => 'Исходящие платежи',
    'SINGLE_OutgoingPayment' => 'Исходящий платёж',
    'LBL_OUTGOING_PAYMENT_INTEGRATION' => 'Интеграция исходящих платежей',
    'LBL_INTEGRATION_INTRO' => 'Выберите, какие документы оплачиваются исходящими платежами и какие их поля модуль читает и обновляет.',
    'LBL_MODULE' => 'Модуль',
    'LBL_ACTIVE' => 'Активно',
    'LBL_STATUS_FIELD' => 'Поле статуса',
    'LBL_STATUS_VALUE' => 'Статус при полной оплате',
    'LBL_STATUS_VALUE_BACK' => 'Статус, когда оплата отменена',
    'LBL_RELATED_TO_FIELD' => 'Поле поставщика или организации',
    'LBL_TOTAL_FIELD' => 'Поле общей суммы',
    'LBL_PAID_FIELD' => 'Поле оплаченной суммы',
    'LBL_BALANCE_FIELD' => 'Поле остатка',
    'LBL_NONE' => 'Нет',
    'LBL_SAVE' => 'Сохранить',
    'LBL_FIELDS_NOTE' => 'Пока интеграция активна, поле оплаченной суммы документа доступно только для чтения и рассчитывается из оплаченных исходящих платежей. Отсутствующие поля «оплачено» и «остаток» создаются автоматически.',
    'LBL_NO_MODULES' => 'Нет доступного складского модуля для интеграции.',
    'LBL_UNDERPAYMENT_THRESHOLD' => 'Порог недоплаты',
    'LBL_UNDERPAYMENT_THRESHOLD_HELP' => 'Документ, остаток по которому не превышает эту сумму, получает статус оплаченного. Сумма сравнивается в валюте документа без пересчёта, а остаток по-прежнему показывает фактическую недоплату. 0 означает только полностью оплаченные документы.',
    'LBL_INVALID_UNDERPAYMENT_THRESHOLD' => 'Порог недоплаты должен быть числом не меньше 0.',
    'LBL_REFUND' => 'Возврат',
    'LBL_REFUND_HELP' => 'Платежи этого модуля являются возвратами по документу и вычитаются из оплаченной суммы.',
    'LBL_REFUND_WITHOUT_PAYING' => 'Ни один активный модуль платежей не оплачивает этот документ; возвраты не учитываются.',
    'LBL_TWO_PAYING_MODULES' => 'Другой модуль платежей уже оплачивает %s. Отметьте платежи одного из них как возвраты.',
    'LBL_NO_PAYING_MODULE' => 'Ни один модуль платежей не оплачивает %s. Снимите здесь Возврат или пусть его оплачивает другой модуль платежей.',
];

$jsLanguageStrings = [
    'JS_NONE' => 'Нет',
    'JS_INTEGRATION_SAVED' => 'Настройки интеграции сохранены',
    'JS_TOTAL_FIELD_REQUIRED' => 'Выберите поле общей суммы для каждого активного модуля',
];
