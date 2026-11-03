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
    'IncomingPayment' => 'Pagamenti in entrata',
    'SINGLE_IncomingPayment' => 'Pagamento in entrata',
    'LBL_INCOMING_PAYMENT_INTEGRATION' => 'Integrazione dei pagamenti in entrata',
    'LBL_INTEGRATION_INTRO' => 'Scegli quali documenti ricevono pagamenti e quali loro campi il modulo legge e aggiorna.',
    'LBL_MODULE' => 'Modulo',
    'LBL_ACTIVE' => 'Attivo',
    'LBL_STATUS_FIELD' => 'Campo stato',
    'LBL_STATUS_VALUE' => 'Stato quando interamente pagato',
    'LBL_STATUS_VALUE_BACK' => 'Stato quando non è più pagato',
    'LBL_RELATED_TO_FIELD' => 'Campo organizzazione',
    'LBL_TOTAL_FIELD' => 'Campo importo totale',
    'LBL_PAID_FIELD' => 'Campo importo pagato',
    'LBL_BALANCE_FIELD' => 'Campo saldo',
    'LBL_NONE' => 'Nessuno',
    'LBL_SAVE' => 'Salva',
    'LBL_FIELDS_NOTE' => 'Finché un\'integrazione è attiva, il campo dell\'importo pagato del documento è di sola lettura e viene calcolato dai pagamenti ricevuti. I campi pagato e saldo mancanti vengono creati automaticamente.',
    'LBL_NO_MODULES' => 'Nessun modulo di inventario è disponibile per l\'integrazione.',
    'LBL_UNDERPAYMENT_THRESHOLD' => 'Soglia di pagamento insufficiente',
    'LBL_UNDERPAYMENT_THRESHOLD_HELP' => 'Un documento il cui saldo è al massimo pari a questo importo riceve lo stato pagato. L\'importo viene confrontato nella valuta del documento senza conversione e il saldo mantiene l\'importo realmente non pagato. 0 indica solo documenti pagati per intero.',
    'LBL_INVALID_UNDERPAYMENT_THRESHOLD' => 'La soglia di pagamento insufficiente deve essere un numero pari o superiore a 0.',
];

$jsLanguageStrings = [
    'JS_NONE' => 'Nessuno',
    'JS_INTEGRATION_SAVED' => 'Impostazioni di integrazione salvate',
    'JS_TOTAL_FIELD_REQUIRED' => 'Seleziona il campo importo totale per ogni modulo attivo',
];
