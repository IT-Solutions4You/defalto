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
    'IncomingPayment' => 'Saapuvat maksut',
    'SINGLE_IncomingPayment' => 'Saapuva maksu',
    'LBL_INCOMING_PAYMENT_INTEGRATION' => 'Saapuvien maksujen integraatio',
    'LBL_INTEGRATION_INTRO' => 'Valitse, mitkä asiakirjat vastaanottavat maksuja ja mitä niiden kenttiä moduuli lukee ja päivittää.',
    'LBL_MODULE' => 'Moduuli',
    'LBL_ACTIVE' => 'Aktiivinen',
    'LBL_STATUS_FIELD' => 'Tilakenttä',
    'LBL_STATUS_VALUE' => 'Tila, kun maksettu kokonaan',
    'LBL_STATUS_VALUE_BACK' => 'Tila, kun ei enää maksettu',
    'LBL_RELATED_TO_FIELD' => 'Organisaatiokenttä',
    'LBL_TOTAL_FIELD' => 'Kokonaissumman kenttä',
    'LBL_PAID_FIELD' => 'Maksetun summan kenttä',
    'LBL_BALANCE_FIELD' => 'Saldokenttä',
    'LBL_NONE' => 'Ei mitään',
    'LBL_SAVE' => 'Tallenna',
    'LBL_FIELDS_NOTE' => 'Kun integraatio on aktiivinen, asiakirjan maksetun summan kenttä on vain luku -tilassa ja se lasketaan vastaanotetuista maksuista. Puuttuvat maksettu- ja saldokentät luodaan automaattisesti.',
    'LBL_NO_MODULES' => 'Integraatiota varten ei ole käytettävissä varastomoduulia.',
    'LBL_UNDERPAYMENT_THRESHOLD' => 'Vajaamaksun raja',
    'LBL_UNDERPAYMENT_THRESHOLD_HELP' => 'Asiakirja, jonka saldo on enintään tämä summa, saa tilan maksettu. Summaa verrataan asiakirjan valuutassa ilman muuntoa, ja saldo näyttää edelleen todellisen vajaamaksun. 0 tarkoittaa vain kokonaan maksettuja asiakirjoja.',
    'LBL_INVALID_UNDERPAYMENT_THRESHOLD' => 'Vajaamaksun rajan on oltava luku, joka on 0 tai suurempi.',
];

$jsLanguageStrings = [
    'JS_NONE' => 'Ei mitään',
    'JS_INTEGRATION_SAVED' => 'Integraatioasetukset tallennettu',
    'JS_TOTAL_FIELD_REQUIRED' => 'Valitse kokonaissumman kenttä jokaiselle aktiiviselle moduulille',
];
