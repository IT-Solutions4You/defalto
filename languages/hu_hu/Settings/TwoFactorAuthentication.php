<?php
/*
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 */

$languageStrings = [
    'TwoFactorAuthentication' => '2FA',
    'LBL_TAB_GENERAL' => 'Általános beállítások',
    'LBL_STATUS' => 'Állapot',
    'LBL_STATUS_INTRO' => 'Kapcsolja be vagy ki a kétfaktoros azonosítást az egész CRM-hez. A bekapcsolás alkalmazza a második bejelentkezési lépést; a kikapcsolás visszaállítja a szokásos bejelentkezést.',
    'LBL_ACTIVE' => 'Aktív',
    'LBL_INACTIVE' => 'Inaktív',
    'LBL_ACTIVATE_CONFIRM' => 'A bekapcsolás második bejelentkezési lépést igényel minden felhasználótól (a mentesítettek kivételével). A folytatás előtt győződjön meg róla, hogy a kimenő e-mail működik, és hogy elmentette a tartalék kódokat - különben kizárhatja magát. Folytatja?',
    'LBL_DEACTIVATE_CONFIRM' => 'A kikapcsolás eltávolítja a második bejelentkezési lépést mindenkitől, és visszaállítja az eredeti bejelentkezést. Folytatja?',
    'LBL_ACTIVATE_FAILED' => 'A bejelentkezési módosítást nem sikerült alkalmazni. Ellenőrizze, hogy a bejelentkezési fájl írható-e.',
    'LBL_NOT_ACTIVE_NOTE' => 'A kétfaktoros azonosítás telepítve van, de még nem aktív. Nincs szükség második bejelentkezési lépésre, amíg alább be nem kapcsolja.',
    'LBL_ENFORCE_ALL_NOTE' => 'A kétfaktoros azonosítás aktív és minden felhasználó számára kötelező. Alapértelmezés szerint minden felhasználó egyszer használatos kódot kap e-mailben a bejelentkezéskor. Adjon hozzá egy felhasználót a mentesítési listához a kizárásához.',
    'LBL_ENFORCE_OFF_NOTE' => 'A kétfaktoros azonosítás jelenleg ki van kapcsolva minden felhasználó számára.',
    'LBL_AVAILABLE_METHODS' => 'Elérhető módszerek',
    'LBL_AVAILABLE_METHODS_INTRO' => 'Válassza ki, hogy a felhasználók mely második faktoros módszereket használhatják. Hagyja mindkettőt bekapcsolva, hogy minden felhasználó a bejelentkezéskor válasszon; csak egyet kapcsoljon be, hogy mindenkitől megkövetelje. Legalább egy módszernek bekapcsolva kell maradnia.',
    'LBL_METHOD_EMAIL' => 'Egyszer használatos kód e-mailben',
    'LBL_METHOD_TOTP' => 'Hitelesítő alkalmazás (TOTP)',
    'LBL_EMAIL_NEEDS_SERVER' => 'Az e-mailes kódok engedélyezéséhez állítson be egy kimenő levelezőszervert (Beállítások > Kimenő szerver).',
    'LBL_TAB_USERS' => 'Felhasználók',
    'LBL_USERS_INTRO' => 'Minden aktív felhasználónál alapértelmezés szerint be van kapcsolva a kétfaktoros azonosítás. Kapcsolja ki egy felhasználónál a mentesítéséhez (csak jelszó), módosítsa a módszerét, vagy állítsa vissza a hitelesítőjét, ha elvesztette az eszközét.',
    'LBL_USER' => 'Felhasználó',
    'LBL_USER_NOT_FOUND' => 'A felhasználó nem található.',
    'LBL_2FA' => '2FA',
    'LBL_ACTIONS' => 'Műveletek',
    'LBL_RESET' => 'Visszaállítás',
    'LBL_RESET_CONFIRM' => 'Visszaállítja a kétfaktoros azonosítást ehhez a felhasználóhoz? A hitelesítő alkalmazása és a tartalék kódjai nem fognak működni, és a következő bejelentkezéskor újra be kell állítania őket.',
    'LBL_METHOD' => 'Módszer',
    'LBL_UNUSED_CODES' => 'Fel nem használt tartalék kódok',
    'LBL_EMAILS_INTRO' => 'Testreszabhatja ennek az e-mailnek a tárgyát és tartalmát. Használja az alábbi változókat - küldéskor a valós értékekre cserélődnek. Az eredeti szöveg visszaállításához használja az Alapértelmezett visszaállítása gombot.',
    'LBL_TPL_TITLE_LOGIN' => 'Bejelentkezési ellenőrző kód sablonja',
    'LBL_TPL_TITLE_BACKUP' => 'Tartalék kódok sablonja',
    'LBL_TPL_SUBJECT' => 'Tárgy',
    'LBL_TPL_BODY' => 'Tartalom',
    'LBL_TPL_VARS' => 'Elérhető változók',
    'LBL_RESET_DEFAULT' => 'Alapértelmezett visszaállítása',
];

$jsLanguageStrings = [
    'JS_SAVED' => 'Mentve.',
    'JS_METHOD_SAVED' => 'Módszer mentve.',
    'JS_METHODS_SAVED' => 'Az elérhető módszerek frissítve.',
    'JS_AT_LEAST_ONE_METHOD' => 'Legalább egy módszernek bekapcsolva kell maradnia.',
    'JS_RESET_TPL_CONFIRM' => 'Visszaállítja ezt a sablont az alapértelmezett szövegére?',
];
