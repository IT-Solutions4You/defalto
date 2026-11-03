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
    'IncomingPayment' => 'Prijaté platby',
    'SINGLE_IncomingPayment' => 'Prijatá platba',
    'LBL_INCOMING_PAYMENT_INTEGRATION' => 'Integrácia prijatých platieb',
    'LBL_INTEGRATION_INTRO' => 'Vyberte, ktoré doklady prijímajú platby a ktoré ich polia modul číta a aktualizuje.',
    'LBL_MODULE' => 'Modul',
    'LBL_ACTIVE' => 'Aktívne',
    'LBL_STATUS_FIELD' => 'Pole stavu',
    'LBL_STATUS_VALUE' => 'Stav po úplnom zaplatení',
    'LBL_STATUS_VALUE_BACK' => 'Stav po zrušení úhrady',
    'LBL_RELATED_TO_FIELD' => 'Pole organizácie',
    'LBL_TOTAL_FIELD' => 'Pole celkovej sumy',
    'LBL_PAID_FIELD' => 'Pole zaplatenej sumy',
    'LBL_BALANCE_FIELD' => 'Pole zostatku',
    'LBL_NONE' => 'Žiadne',
    'LBL_SAVE' => 'Uložiť',
    'LBL_FIELDS_NOTE' => 'Kým je integrácia aktívna, pole zaplatenej sumy dokladu je len na čítanie a počíta sa z prijatých platieb. Chýbajúce polia zaplatené a zostatok sa vytvoria automaticky.',
    'LBL_NO_MODULES' => 'Pre integráciu nie je dostupný žiadny skladový modul.',
    'LBL_UNDERPAYMENT_THRESHOLD' => 'Hranica nedoplatku',
    'LBL_UNDERPAYMENT_THRESHOLD_HELP' => 'Doklad, ktorého zostatok je najviac táto suma, dostane stav uhradený. Suma sa porovnáva v mene dokladu bez prepočtu a v zostatku ostáva skutočný nedoplatok. 0 znamená len úplne uhradené doklady.',
    'LBL_INVALID_UNDERPAYMENT_THRESHOLD' => 'Hranica nedoplatku musí byť číslo 0 alebo väčšie.',
];

$jsLanguageStrings = [
    'JS_NONE' => 'Žiadne',
    'JS_INTEGRATION_SAVED' => 'Nastavenia integrácie boli uložené',
    'JS_TOTAL_FIELD_REQUIRED' => 'Pre každý aktívny modul vyberte pole celkovej sumy',
];
