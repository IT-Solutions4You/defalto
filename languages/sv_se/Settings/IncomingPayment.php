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
    'IncomingPayment' => 'Inkommande betalningar',
    'SINGLE_IncomingPayment' => 'Inkommande betalning',
    'LBL_INCOMING_PAYMENT_INTEGRATION' => 'Integration av inkommande betalningar',
    'LBL_INTEGRATION_INTRO' => 'Välj vilka dokument som tar emot betalningar och vilka av deras fält modulen läser och uppdaterar.',
    'LBL_MODULE' => 'Modul',
    'LBL_ACTIVE' => 'Aktiv',
    'LBL_STATUS_FIELD' => 'Statusfält',
    'LBL_STATUS_VALUE' => 'Status när fullt betald',
    'LBL_STATUS_VALUE_BACK' => 'Status när inte längre betald',
    'LBL_RELATED_TO_FIELD' => 'Organisationsfält',
    'LBL_TOTAL_FIELD' => 'Fält för totalbelopp',
    'LBL_PAID_FIELD' => 'Fält för betalt belopp',
    'LBL_BALANCE_FIELD' => 'Fält för saldo',
    'LBL_NONE' => 'Inget',
    'LBL_SAVE' => 'Spara',
    'LBL_FIELDS_NOTE' => 'Så länge en integration är aktiv är dokumentets fält för betalt belopp skrivskyddat och beräknas från mottagna betalningar. Saknade fält för betalt och saldo skapas automatiskt.',
    'LBL_NO_MODULES' => 'Ingen lagermodul är tillgänglig för integrationen.',
    'LBL_UNDERPAYMENT_THRESHOLD' => 'Gräns för underbetalning',
    'LBL_UNDERPAYMENT_THRESHOLD_HELP' => 'Ett dokument vars saldo är högst detta belopp får statusen betald. Beloppet jämförs i dokumentets valuta utan omräkning och saldot visar fortfarande den verkliga underbetalningen. 0 betyder endast helt betalda dokument.',
    'LBL_INVALID_UNDERPAYMENT_THRESHOLD' => 'Gränsen för underbetalning måste vara ett tal som är 0 eller större.',
];

$jsLanguageStrings = [
    'JS_NONE' => 'Inget',
    'JS_INTEGRATION_SAVED' => 'Integrationsinställningar sparade',
    'JS_TOTAL_FIELD_REQUIRED' => 'Välj fältet för totalbelopp för varje aktiv modul',
];
