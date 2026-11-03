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
    'IncomingPayment' => 'Gelen Ödemeler',
    'SINGLE_IncomingPayment' => 'Gelen Ödeme',
    'LBL_INCOMING_PAYMENT_INTEGRATION' => 'Gelen ödemeler entegrasyonu',
    'LBL_INTEGRATION_INTRO' => 'Hangi belgelerin ödeme alacağını ve modülün hangi alanlarını okuyup güncelleyeceğini seçin.',
    'LBL_MODULE' => 'Modül',
    'LBL_ACTIVE' => 'Etkin',
    'LBL_STATUS_FIELD' => 'Durum alanı',
    'LBL_STATUS_VALUE' => 'Tamamen ödendiğinde durum',
    'LBL_STATUS_VALUE_BACK' => 'Artık ödenmediğinde durum',
    'LBL_RELATED_TO_FIELD' => 'Kuruluş alanı',
    'LBL_TOTAL_FIELD' => 'Toplam tutar alanı',
    'LBL_PAID_FIELD' => 'Ödenen tutar alanı',
    'LBL_BALANCE_FIELD' => 'Bakiye alanı',
    'LBL_NONE' => 'Yok',
    'LBL_SAVE' => 'Kaydet',
    'LBL_FIELDS_NOTE' => 'Bir entegrasyon etkinken belgenin ödenen tutar alanı salt okunurdur ve alınan ödemelerden hesaplanır. Eksik ödenen ve bakiye alanları otomatik olarak oluşturulur.',
    'LBL_NO_MODULES' => 'Entegrasyon için kullanılabilir envanter modülü yok.',
    'LBL_UNDERPAYMENT_THRESHOLD' => 'Eksik ödeme sınırı',
    'LBL_UNDERPAYMENT_THRESHOLD_HELP' => 'Bakiyesi en fazla bu tutar olan belge ödendi durumunu alır. Tutar, belgenin para biriminde dönüştürülmeden karşılaştırılır ve bakiye gerçek eksik ödemeyi göstermeye devam eder. 0 yalnızca tamamen ödenmiş belgeler anlamına gelir.',
    'LBL_INVALID_UNDERPAYMENT_THRESHOLD' => 'Eksik ödeme sınırı 0 veya daha büyük bir sayı olmalıdır.',
];

$jsLanguageStrings = [
    'JS_NONE' => 'Yok',
    'JS_INTEGRATION_SAVED' => 'Entegrasyon ayarları kaydedildi',
    'JS_TOTAL_FIELD_REQUIRED' => 'Her etkin modül için toplam tutar alanını seçin',
];
