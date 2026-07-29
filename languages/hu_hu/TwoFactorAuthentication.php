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
    'TwoFactorAuthentication' => '2FA',
    'LBL_2FA_TITLE' => 'Kétlépcsős azonosítás',
    'LBL_EMAIL_PROMPT' => 'Ellenőrző kódot küldtünk az e-mail-címére. A folytatáshoz adja meg alább.',
    'LBL_TOTP_PROMPT' => 'A folytatáshoz adja meg a hitelesítő alkalmazásában látható kódot.',
    'LBL_CODE_PLACEHOLDER' => 'Ellenőrző kód',
    'LBL_VERIFY' => 'Ellenőrzés',
    'LBL_RESEND_CODE' => 'Kód újraküldése',
    'LBL_BACK_TO_LOGIN' => 'Vissza a bejelentkezéshez',
    'LBL_BACKUP_HINT' => 'Elvesztette a hozzáférést? Helyette megadhatja az egyik tartalék kódját.',
    'LBL_USE_OTHER_METHOD' => 'Másik módszer használata',
    'LBL_CHOOSE_METHOD' => 'Válassza ki, hogyan szeretné igazolni a személyazonosságát.',
    'LBL_CHOICE_EMAIL' => 'Kód küldése az e-mail-címemre',
    'LBL_CHOICE_EMAIL_SUB' => 'Egyszer használatos kódot küldünk az e-mail-címére',
    'LBL_CHOICE_TOTP' => 'A hitelesítő alkalmazásom használata',
    'LBL_CHOICE_TOTP_SUB' => 'Google Authenticator, Microsoft Authenticator, Authy...',
    'LBL_ENROLL_PROMPT' => 'Olvassa be ezt a QR-kódot a hitelesítő alkalmazásával, majd a beállítás befejezéséhez adja meg a megjelenített 6 jegyű kódot.',
    'LBL_MANUAL_KEY' => 'Nem tudja beolvasni? Adja meg ezt a kulcsot kézzel:',
    'LBL_ENROLL_VERIFY' => 'Megerősítés és folytatás',
    'LBL_BACKUP_CODES_WARNING' => 'Tárolja ezeket a tartalék kódokat biztonságos helyen. Mindegyik egyszer működik, és lehetővé teszi a bejelentkezést, ha elveszíti a hozzáférést az alkalmazásához. Csak most jelennek meg.',
    'LBL_BACKUP_CODES' => 'Tartalék kódok',
    'LBL_MANAGE_TITLE' => 'Kétfaktoros azonosítás',
    'LBL_AUTH_METHOD' => 'Azonosítási módszer',
    'LBL_OPT_EMAIL' => 'Egyszer használatos kód e-mailben',
    'LBL_OPT_APP' => 'Hitelesítő alkalmazás',
    'LBL_METHOD_NOT_SET' => '— (bejelentkezéskor választ)',
    'LBL_ADMIN' => 'Admin',
    'LBL_METHOD_SAVED' => 'Az azonosítási módszere mentve.',
    'LBL_APP_STATUS_ON' => 'A hitelesítő alkalmazása be van állítva.',
    'LBL_APP_STATUS_OFF' => 'Még nem állított be hitelesítő alkalmazást.',
    'LBL_SETUP_APP' => 'Hitelesítő alkalmazás beállítása',
    'LBL_RECONFIGURE_APP' => 'Hitelesítő alkalmazás újbóli beállítása',
    'LBL_REGEN_BACKUP' => 'Új tartalék kódok létrehozása',
    'LBL_UNUSED_CODES' => 'Fel nem használt tartalék kódok',
    'LBL_BACKUP_CODES_INTRO_USER' => 'A tartalék kódok lehetővé teszik a bejelentkezést, ha elveszíti a hozzáférést a második faktorhoz. Minden kód egyszer működik.',
    'LBL_INVALID_CODE' => 'A megadott kód helytelen vagy lejárt. Kérjük, próbálja újra.',
    'LBL_TOO_MANY_ATTEMPTS' => 'Túl sok sikertelen próbálkozás. Kérjük, várjon %d percet az újbóli próbálkozás előtt.',
    'LBL_CODE_RESENT' => 'Új ellenőrző kódot küldtünk az e-mail-címére.',
    'LBL_TPL_LOGIN_SUBJECT' => 'Az Ön ellenőrző kódja',
    'LBL_TPL_LOGIN_BODY' => '<p>Üdvözöljük, $username$!</p><p>Az egyszer használatos ellenőrző kódja: <strong>$code$</strong></p><p>$minutes$ percig érvényes. Ha nem Ön próbált bejelentkezni, forduljon a rendszergazdához.</p><p>$company$</p>',
    'LBL_TPL_BACKUP_SUBJECT' => 'Az Ön kétfaktoros tartalék kódjai',
    'LBL_TPL_BACKUP_BODY' => '<p>Üdvözöljük, $username$!</p><p>Íme a tartalék kódjai. Minden kód egyszer működik:</p><p>$codes$</p><p>Tárolja őket biztonságos helyen.</p><p>$company$</p>',
    'LBL_DONE' => 'Kész',
];
