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
    'IncomingPayment' => 'Inkomende betalingen',
    'SINGLE_IncomingPayment' => 'Inkomende betaling',
    'LBL_INCOMING_PAYMENT_INTEGRATION' => 'Integratie van inkomende betalingen',
    'LBL_INTEGRATION_INTRO' => 'Kies welke documenten inkomende betalingen ontvangen en welke velden de module leest en bijwerkt.',
    'LBL_MODULE' => 'Module',
    'LBL_ACTIVE' => 'Actief',
    'LBL_STATUS_FIELD' => 'Statusveld',
    'LBL_STATUS_VALUE' => 'Status bij volledige betaling',
    'LBL_STATUS_VALUE_BACK' => 'Status wanneer niet meer betaald',
    'LBL_RELATED_TO_FIELD' => 'Organisatieveld',
    'LBL_TOTAL_FIELD' => 'Veld totaalbedrag',
    'LBL_PAID_FIELD' => 'Veld betaald bedrag',
    'LBL_BALANCE_FIELD' => 'Veld saldo',
    'LBL_NONE' => 'Geen',
    'LBL_SAVE' => 'Opslaan',
    'LBL_FIELDS_NOTE' => 'Zolang een integratie actief is, is het veld betaald bedrag van het document alleen-lezen en wordt het berekend uit de ontvangen betalingen. Ontbrekende velden voor betaald en saldo worden automatisch aangemaakt.',
    'LBL_NO_MODULES' => 'Er is geen voorraadmodule beschikbaar voor de integratie.',
    'LBL_UNDERPAYMENT_THRESHOLD' => 'Drempel voor onderbetaling',
    'LBL_UNDERPAYMENT_THRESHOLD_HELP' => 'Een document waarvan het saldo hoogstens dit bedrag is, krijgt de status betaald. Het bedrag wordt zonder omrekening in de valuta van het document vergeleken en het saldo blijft de werkelijke onderbetaling tonen. 0 betekent alleen volledig betaalde documenten.',
    'LBL_INVALID_UNDERPAYMENT_THRESHOLD' => 'De drempel voor onderbetaling moet een getal van 0 of hoger zijn.',
];

$jsLanguageStrings = [
    'JS_NONE' => 'Geen',
    'JS_INTEGRATION_SAVED' => 'Integratie-instellingen opgeslagen',
    'JS_TOTAL_FIELD_REQUIRED' => 'Selecteer het veld totaalbedrag voor elke actieve module',
];
