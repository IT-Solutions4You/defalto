<?php
/*
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 */

$languageStrings = [
    'TwoFactorAuthentication' => '2FA',
    'LBL_TAB_GENERAL' => 'Allmänna inställningar',
    'LBL_STATUS' => 'Status',
    'LBL_STATUS_INTRO' => 'Aktivera eller inaktivera tvåfaktorsautentisering för hela CRM:et. Aktivering lägger till det andra inloggningssteget; inaktivering återställer standardinloggningen.',
    'LBL_ACTIVE' => 'Aktiv',
    'LBL_INACTIVE' => 'Inaktiv',
    'LBL_ACTIVATE_CONFIRM' => 'Aktivering kräver ett andra inloggningssteg för alla användare (utom undantagna). Innan du fortsätter, se till att utgående e-post fungerar och att du har sparat reservkoder - annars kan du bli utelåst. Fortsätta?',
    'LBL_DEACTIVATE_CONFIRM' => 'Inaktivering tar bort det andra inloggningssteget för alla och återställer den ursprungliga inloggningen. Fortsätta?',
    'LBL_ACTIVATE_FAILED' => 'Det gick inte att tillämpa inloggningsändringen. Kontrollera att inloggningsfilen är skrivbar.',
    'LBL_NOT_ACTIVE_NOTE' => 'Tvåfaktorsautentisering är installerad men ännu inte aktiv. Inget andra inloggningssteg krävs förrän du aktiverar den nedan.',
    'LBL_ENFORCE_ALL_NOTE' => 'Tvåfaktorsautentisering är aktiv och obligatorisk för alla användare. Som standard får varje användare en engångskod via e-post vid inloggning. Lägg till en användare i undantagslistan för att undanta denna.',
    'LBL_ENFORCE_OFF_NOTE' => 'Tvåfaktorsautentisering är för närvarande avstängd för alla användare.',
    'LBL_AVAILABLE_METHODS' => 'Tillgängliga metoder',
    'LBL_AVAILABLE_METHODS_INTRO' => 'Välj vilka metoder för andra faktorn användare kan använda. Låt båda vara aktiverade så att varje användare väljer vid inloggning; aktivera endast en för att kräva den av alla. Minst en metod måste förbli aktiverad.',
    'LBL_METHOD_EMAIL' => 'Engångskod via e-post',
    'LBL_METHOD_TOTP' => 'Autentiseringsapp (TOTP)',
    'LBL_EMAIL_NEEDS_SERVER' => 'Konfigurera en server för utgående e-post (Inställningar > Utgående server) för att aktivera e-postkoder.',
    'LBL_TAB_USERS' => 'Användare',
    'LBL_USERS_INTRO' => 'Varje aktiv användare har tvåfaktorsautentisering aktiverad som standard. Stäng av den för en användare för att undanta denna (endast lösenord), ändra användarens metod eller återställ användarens autentiserare om enheten har tappats bort.',
    'LBL_USER' => 'Användare',
    'LBL_USER_NOT_FOUND' => 'Användaren hittades inte.',
    'LBL_2FA' => '2FA',
    'LBL_ACTIONS' => 'Åtgärder',
    'LBL_RESET' => 'Återställ',
    'LBL_RESET_CONFIRM' => 'Återställa tvåfaktorsautentisering för den här användaren? Användarens autentiseringsapp och reservkoder slutar fungera och användaren konfigurerar dem igen vid nästa inloggning.',
    'LBL_METHOD' => 'Metod',
    'LBL_UNUSED_CODES' => 'Oanvända reservkoder',
    'LBL_EMAILS_INTRO' => 'Anpassa ämnet och innehållet i det här e-postmeddelandet. Använd variablerna nedan - de ersätts med de verkliga värdena när e-postmeddelandet skickas. Använd Återställ till standard för att återställa den ursprungliga texten.',
    'LBL_TPL_TITLE_LOGIN' => 'Mall för verifieringskod vid inloggning',
    'LBL_TPL_TITLE_BACKUP' => 'Mall för reservkoder',
    'LBL_TPL_SUBJECT' => 'Ämne',
    'LBL_TPL_BODY' => 'Innehåll',
    'LBL_TPL_VARS' => 'Tillgängliga variabler',
    'LBL_RESET_DEFAULT' => 'Återställ till standard',
];

$jsLanguageStrings = [
    'JS_SAVED' => 'Sparat.',
    'JS_METHOD_SAVED' => 'Metod sparad.',
    'JS_METHODS_SAVED' => 'Tillgängliga metoder uppdaterade.',
    'JS_AT_LEAST_ONE_METHOD' => 'Minst en metod måste förbli aktiverad.',
    'JS_RESET_TPL_CONFIRM' => 'Återställa den här mallen till standardtexten?',
];
