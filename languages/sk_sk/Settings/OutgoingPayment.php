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
    'OutgoingPayment' => 'Odchádzajúce platby',
    'SINGLE_OutgoingPayment' => 'Odchádzajúca platba',
    'LBL_OUTGOING_PAYMENT_INTEGRATION' => 'Integrácia odchádzajúcich platieb',
    'LBL_INTEGRATION_INTRO' => 'Vyberte, ktoré doklady prijímajú platby a ktoré ich polia modul číta a aktualizuje.',
    'LBL_MODULE' => 'Modul',
    'LBL_ACTIVE' => 'Aktívne',
    'LBL_STATUS_FIELD' => 'Pole stavu',
    'LBL_STATUS_VALUE' => 'Stav po úplnom zaplatení',
    'LBL_STATUS_VALUE_BACK' => 'Stav po zrušení úhrady',
    'LBL_RELATED_TO_FIELD' => 'Pole dodávateľa alebo organizácie',
    'LBL_TOTAL_FIELD' => 'Pole celkovej sumy',
    'LBL_PAID_FIELD' => 'Pole zaplatenej sumy',
    'LBL_BALANCE_FIELD' => 'Pole zostatku',
    'LBL_NONE' => 'Žiadne',
    'LBL_SAVE' => 'Uložiť',
    'LBL_FIELDS_NOTE' => 'Kým je integrácia aktívna, pole zaplatenej sumy dokladu je len na čítanie a počíta sa z odchádzajúcich platieb. Chýbajúce polia zaplatené a zostatok sa vytvoria automaticky.',
    'LBL_NO_MODULES' => 'Pre integráciu nie je dostupný žiadny skladový modul.',
    'LBL_UNDERPAYMENT_THRESHOLD' => 'Hranica nedoplatku',
    'LBL_UNDERPAYMENT_THRESHOLD_HELP' => 'Doklad, ktorého zostatok je najviac táto suma, dostane stav uhradený. Suma sa porovnáva v mene dokladu bez prepočtu a v zostatku ostáva skutočný nedoplatok. 0 znamená len úplne uhradené doklady.',
    'LBL_INVALID_UNDERPAYMENT_THRESHOLD' => 'Hranica nedoplatku musí byť číslo 0 alebo väčšie.',
    'LBL_REFUND' => 'Vratka',
    'LBL_REFUND_HELP' => 'Platby tohto modulu sú vratky k dokladu a odpočítajú sa od zaplatenej sumy.',
    'LBL_REFUND_WITHOUT_PAYING' => 'Doklad neuhrádza žiadny aktívny modul platieb, vratky sa nezapočítajú.',
    'LBL_TWO_PAYING_MODULES' => 'Iný modul platieb už uhrádza %s. Platby jedného z nich označte ako vratky.',
    'LBL_NO_PAYING_MODULE' => 'Žiadny modul platieb neuhrádza %s. Zrušte tu Vratku alebo nastavte, aby ho uhrádzal iný modul platieb.',
];

$jsLanguageStrings = [
    'JS_NONE' => 'Žiadne',
    'JS_INTEGRATION_SAVED' => 'Nastavenia integrácie boli uložené',
    'JS_TOTAL_FIELD_REQUIRED' => 'Pre každý aktívny modul vyberte pole celkovej sumy',
];
