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
    'IncomingPayment' => 'Beérkező fizetések',
    'SINGLE_IncomingPayment' => 'Beérkező fizetés',
    'LBL_INCOMING_PAYMENT_INTEGRATION' => 'Beérkező fizetések integrációja',
    'LBL_INTEGRATION_INTRO' => 'Válassza ki, mely bizonylatok fogadnak fizetéseket, és mely mezőiket olvassa és frissíti a modul.',
    'LBL_MODULE' => 'Modul',
    'LBL_ACTIVE' => 'Aktív',
    'LBL_STATUS_FIELD' => 'Állapot mező',
    'LBL_STATUS_VALUE' => 'Állapot teljes kifizetéskor',
    'LBL_STATUS_VALUE_BACK' => 'Állapot, ha már nincs kifizetve',
    'LBL_RELATED_TO_FIELD' => 'Szervezet mező',
    'LBL_TOTAL_FIELD' => 'Teljes összeg mező',
    'LBL_PAID_FIELD' => 'Kifizetett összeg mező',
    'LBL_BALANCE_FIELD' => 'Egyenleg mező',
    'LBL_NONE' => 'Nincs',
    'LBL_SAVE' => 'Mentés',
    'LBL_FIELDS_NOTE' => 'Amíg az integráció aktív, a bizonylat kifizetett összeg mezője csak olvasható, és a beérkezett fizetésekből számítódik. A hiányzó kifizetett és egyenleg mezők automatikusan jönnek létre.',
    'LBL_NO_MODULES' => 'Nincs elérhető készletmodul az integrációhoz.',
    'LBL_UNDERPAYMENT_THRESHOLD' => 'Alulfizetési határ',
    'LBL_UNDERPAYMENT_THRESHOLD_HELP' => 'Az a bizonylat, amelynek egyenlege legfeljebb ennyi, fizetett állapotot kap. Az összehasonlítás átváltás nélkül a bizonylat pénznemében történik, az egyenleg továbbra is a valós hátralékot mutatja. A 0 csak a teljesen kifizetett bizonylatokat jelenti.',
    'LBL_INVALID_UNDERPAYMENT_THRESHOLD' => 'Az alulfizetési határnak 0 vagy annál nagyobb számnak kell lennie.',
];

$jsLanguageStrings = [
    'JS_NONE' => 'Nincs',
    'JS_INTEGRATION_SAVED' => 'Integrációs beállítások mentve',
    'JS_TOTAL_FIELD_REQUIRED' => 'Válassza ki a teljes összeg mezőt minden aktív modulhoz',
];
