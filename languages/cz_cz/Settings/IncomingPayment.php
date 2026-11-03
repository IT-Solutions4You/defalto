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
    'IncomingPayment' => 'Přijaté platby',
    'SINGLE_IncomingPayment' => 'Přijatá platba',
    'LBL_INCOMING_PAYMENT_INTEGRATION' => 'Integrace přijatých plateb',
    'LBL_INTEGRATION_INTRO' => 'Vyberte, které doklady přijímají platby a která jejich pole modul čte a aktualizuje.',
    'LBL_MODULE' => 'Modul',
    'LBL_ACTIVE' => 'Aktivní',
    'LBL_STATUS_FIELD' => 'Pole stavu',
    'LBL_STATUS_VALUE' => 'Stav po úplném zaplacení',
    'LBL_STATUS_VALUE_BACK' => 'Stav po zrušení úhrady',
    'LBL_RELATED_TO_FIELD' => 'Pole organizace',
    'LBL_TOTAL_FIELD' => 'Pole celkové částky',
    'LBL_PAID_FIELD' => 'Pole zaplacené částky',
    'LBL_BALANCE_FIELD' => 'Pole zůstatku',
    'LBL_NONE' => 'Žádné',
    'LBL_SAVE' => 'Uložit',
    'LBL_FIELDS_NOTE' => 'Dokud je integrace aktivní, pole zaplacené částky dokladu je pouze pro čtení a počítá se z přijatých plateb. Chybějící pole zaplaceno a zůstatek se vytvoří automaticky.',
    'LBL_NO_MODULES' => 'Pro integraci není k dispozici žádný skladový modul.',
    'LBL_UNDERPAYMENT_THRESHOLD' => 'Hranice nedoplatku',
    'LBL_UNDERPAYMENT_THRESHOLD_HELP' => 'Doklad, jehož zůstatek je nejvýše tato částka, dostane stav uhrazený. Částka se porovnává v měně dokladu bez přepočtu a v zůstatku zůstává skutečný nedoplatek. 0 znamená jen zcela uhrazené doklady.',
    'LBL_INVALID_UNDERPAYMENT_THRESHOLD' => 'Hranice nedoplatku musí být číslo 0 nebo větší.',
];

$jsLanguageStrings = [
    'JS_NONE' => 'Žádné',
    'JS_INTEGRATION_SAVED' => 'Nastavení integrace bylo uloženo',
    'JS_TOTAL_FIELD_REQUIRED' => 'Pro každý aktivní modul vyberte pole celkové částky',
];
