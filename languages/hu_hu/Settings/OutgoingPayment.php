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
    'OutgoingPayment' => 'Kimenő fizetések',
    'SINGLE_OutgoingPayment' => 'Kimenő fizetés',
    'LBL_OUTGOING_PAYMENT_INTEGRATION' => 'Kimenő fizetések integrációja',
    'LBL_INTEGRATION_INTRO' => 'Válassza ki, mely bizonylatokat fizetik kimenő fizetésekkel, és mely mezőiket olvassa és frissíti a modul.',
    'LBL_MODULE' => 'Modul',
    'LBL_ACTIVE' => 'Aktív',
    'LBL_STATUS_FIELD' => 'Állapot mező',
    'LBL_STATUS_VALUE' => 'Állapot teljes kifizetéskor',
    'LBL_STATUS_VALUE_BACK' => 'Állapot, ha már nincs kifizetve',
    'LBL_RELATED_TO_FIELD' => 'Szállító vagy szervezet mező',
    'LBL_TOTAL_FIELD' => 'Teljes összeg mező',
    'LBL_PAID_FIELD' => 'Kifizetett összeg mező',
    'LBL_BALANCE_FIELD' => 'Egyenleg mező',
    'LBL_NONE' => 'Nincs',
    'LBL_SAVE' => 'Mentés',
    'LBL_FIELDS_NOTE' => 'Amíg az integráció aktív, a bizonylat kifizetett összeg mezője csak olvasható, és a kifizetett kimenő fizetésekből számítódik. A hiányzó kifizetett és egyenleg mezők automatikusan jönnek létre.',
    'LBL_NO_MODULES' => 'Nincs elérhető készletmodul az integrációhoz.',
    'LBL_UNDERPAYMENT_THRESHOLD' => 'Alulfizetési határ',
    'LBL_UNDERPAYMENT_THRESHOLD_HELP' => 'Az a bizonylat, amelynek egyenlege legfeljebb ennyi, fizetett állapotot kap. Az összehasonlítás átváltás nélkül a bizonylat pénznemében történik, az egyenleg továbbra is a valós hátralékot mutatja. A 0 csak a teljesen kifizetett bizonylatokat jelenti.',
    'LBL_INVALID_UNDERPAYMENT_THRESHOLD' => 'Az alulfizetési határnak 0 vagy annál nagyobb számnak kell lennie.',
    'LBL_REFUND' => 'Visszatérítés',
    'LBL_REFUND_HELP' => 'Ennek a modulnak a fizetései a bizonylat visszatérítései, és levonódnak a kifizetett összegből.',
    'LBL_REFUND_WITHOUT_PAYING' => 'Egyetlen aktív fizetési modul sem fizeti ezt a bizonylatot; a visszatérítések nem számítanak.',
    'LBL_TWO_PAYING_MODULES' => 'Egy másik fizetési modul már fizeti ezt: %s. Jelölje az egyik modul fizetéseit visszatérítésként.',
];

$jsLanguageStrings = [
    'JS_NONE' => 'Nincs',
    'JS_INTEGRATION_SAVED' => 'Integrációs beállítások mentve',
    'JS_TOTAL_FIELD_REQUIRED' => 'Válassza ki a teljes összeg mezőt minden aktív modulhoz',
];
