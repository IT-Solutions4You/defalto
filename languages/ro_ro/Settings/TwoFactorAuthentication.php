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
    'LBL_TAB_GENERAL' => 'Setări generale',
    'LBL_STATUS' => 'Stare',
    'LBL_STATUS_INTRO' => 'Activați sau dezactivați autentificarea în doi factori pentru întregul CRM. Activarea aplică al doilea pas de autentificare; dezactivarea restabilește autentificarea standard.',
    'LBL_ACTIVE' => 'Activ',
    'LBL_INACTIVE' => 'Inactiv',
    'LBL_ACTIVATE_CONFIRM' => 'Activarea va impune un al doilea pas de autentificare pentru toți utilizatorii (cu excepția celor exceptați). Înainte de a continua, asigurați-vă că trimiterea e-mailurilor funcționează și că ați salvat coduri de rezervă - altfel puteți rămâne blocat. Continuați?',
    'LBL_DEACTIVATE_CONFIRM' => 'Dezactivarea va elimina al doilea pas de autentificare pentru toți și va restabili autentificarea originală. Continuați?',
    'LBL_ACTIVATE_FAILED' => 'Nu s-a putut aplica modificarea autentificării. Verificați dacă fișierul de autentificare poate fi scris.',
    'LBL_NOT_ACTIVE_NOTE' => 'Autentificarea în doi factori este instalată, dar încă nu este activă. Nu este necesar niciun al doilea pas de autentificare până când nu o activați mai jos.',
    'LBL_ENFORCE_ALL_NOTE' => 'Autentificarea în doi factori este activă și obligatorie pentru toți utilizatorii. În mod implicit, fiecare utilizator primește un cod de unică folosință prin e-mail la autentificare. Adăugați un utilizator în lista de excepții pentru a-l exclude.',
    'LBL_ENFORCE_OFF_NOTE' => 'Autentificarea în doi factori este momentan dezactivată pentru toți utilizatorii.',
    'LBL_AVAILABLE_METHODS' => 'Metode disponibile',
    'LBL_AVAILABLE_METHODS_INTRO' => 'Alegeți ce metode de al doilea factor pot folosi utilizatorii. Lăsați ambele activate pentru ca fiecare utilizator să aleagă la autentificare; activați doar una pentru a o impune tuturor. Cel puțin o metodă trebuie să rămână activată.',
    'LBL_METHOD_EMAIL' => 'Cod de unică folosință prin e-mail',
    'LBL_METHOD_TOTP' => 'Aplicație de autentificare (TOTP)',
    'LBL_EMAIL_NEEDS_SERVER' => 'Configurați un server de e-mail pentru trimitere (Setări > Server de trimitere) pentru a activa codurile prin e-mail.',
    'LBL_TAB_USERS' => 'Utilizatori',
    'LBL_USERS_INTRO' => 'Fiecare utilizator activ are autentificarea în doi factori activată în mod implicit. Dezactivați-o pentru un utilizator pentru a-l excepta (doar parolă), schimbați-i metoda sau resetați-i autentificatorul dacă și-a pierdut dispozitivul.',
    'LBL_USER' => 'Utilizator',
    'LBL_USER_NOT_FOUND' => 'Utilizatorul nu a fost găsit.',
    'LBL_2FA' => '2FA',
    'LBL_ACTIONS' => 'Acțiuni',
    'LBL_RESET' => 'Resetează',
    'LBL_RESET_CONFIRM' => 'Resetați autentificarea în doi factori pentru acest utilizator? Aplicația sa de autentificare și codurile de rezervă nu vor mai funcționa, iar el le va configura din nou la următoarea autentificare.',
    'LBL_METHOD' => 'Metodă',
    'LBL_UNUSED_CODES' => 'Coduri de rezervă nefolosite',
    'LBL_EMAILS_INTRO' => 'Personalizați subiectul și conținutul acestui e-mail. Folosiți variabilele de mai jos - sunt înlocuite cu valorile reale la trimiterea e-mailului. Folosiți Resetare la valorile implicite pentru a restabili textul original.',
    'LBL_TPL_TITLE_LOGIN' => 'Șablon pentru codul de verificare la autentificare',
    'LBL_TPL_TITLE_BACKUP' => 'Șablon pentru codurile de rezervă',
    'LBL_TPL_SUBJECT' => 'Subiect',
    'LBL_TPL_BODY' => 'Conținut',
    'LBL_TPL_VARS' => 'Variabile disponibile',
    'LBL_RESET_DEFAULT' => 'Resetare la valorile implicite',
];

$jsLanguageStrings = [
    'JS_SAVED' => 'Salvat.',
    'JS_METHOD_SAVED' => 'Metodă salvată.',
    'JS_METHODS_SAVED' => 'Metode disponibile actualizate.',
    'JS_AT_LEAST_ONE_METHOD' => 'Cel puțin o metodă trebuie să rămână activată.',
    'JS_RESET_TPL_CONFIRM' => 'Resetați acest șablon la textul implicit?',
];
