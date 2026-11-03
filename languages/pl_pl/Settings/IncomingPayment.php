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
    'IncomingPayment' => 'Płatności przychodzące',
    'SINGLE_IncomingPayment' => 'Płatność przychodząca',
    'LBL_INCOMING_PAYMENT_INTEGRATION' => 'Integracja płatności przychodzących',
    'LBL_INTEGRATION_INTRO' => 'Wybierz, które dokumenty przyjmują płatności i które ich pola moduł odczytuje i aktualizuje.',
    'LBL_MODULE' => 'Moduł',
    'LBL_ACTIVE' => 'Aktywne',
    'LBL_STATUS_FIELD' => 'Pole statusu',
    'LBL_STATUS_VALUE' => 'Status po pełnej zapłacie',
    'LBL_STATUS_VALUE_BACK' => 'Status, gdy nie jest już zapłacone',
    'LBL_RELATED_TO_FIELD' => 'Pole organizacji',
    'LBL_TOTAL_FIELD' => 'Pole kwoty całkowitej',
    'LBL_PAID_FIELD' => 'Pole kwoty zapłaconej',
    'LBL_BALANCE_FIELD' => 'Pole salda',
    'LBL_NONE' => 'Brak',
    'LBL_SAVE' => 'Zapisz',
    'LBL_FIELDS_NOTE' => 'Gdy integracja jest aktywna, pole kwoty zapłaconej dokumentu jest tylko do odczytu i obliczane z otrzymanych płatności. Brakujące pola zapłacono i saldo są tworzone automatycznie.',
    'LBL_NO_MODULES' => 'Brak dostępnego modułu magazynowego do integracji.',
    'LBL_UNDERPAYMENT_THRESHOLD' => 'Próg niedopłaty',
    'LBL_UNDERPAYMENT_THRESHOLD_HELP' => 'Dokument, którego saldo wynosi najwyżej tę kwotę, otrzymuje status opłacony. Kwota jest porównywana w walucie dokumentu bez przeliczania, a saldo nadal pokazuje rzeczywistą niedopłatę. 0 oznacza tylko w pełni opłacone dokumenty.',
    'LBL_INVALID_UNDERPAYMENT_THRESHOLD' => 'Próg niedopłaty musi być liczbą 0 lub większą.',
];

$jsLanguageStrings = [
    'JS_NONE' => 'Brak',
    'JS_INTEGRATION_SAVED' => 'Ustawienia integracji zapisane',
    'JS_TOTAL_FIELD_REQUIRED' => 'Wybierz pole kwoty całkowitej dla każdego aktywnego modułu',
];
