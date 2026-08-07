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
    'LBL_2FA_TITLE' => 'Verifiering i två steg',
    'LBL_EMAIL_PROMPT' => 'Vi har skickat en verifieringskod till din e-postadress. Ange den nedan för att fortsätta.',
    'LBL_TOTP_PROMPT' => 'Ange koden från din autentiseringsapp för att fortsätta.',
    'LBL_CODE_PLACEHOLDER' => 'Verifieringskod',
    'LBL_VERIFY' => 'Verifiera',
    'LBL_RESEND_CODE' => 'Skicka koden igen',
    'LBL_BACK_TO_LOGIN' => 'Tillbaka till inloggning',
    'LBL_BACKUP_HINT' => 'Förlorat åtkomsten? Du kan ange en av dina reservkoder istället.',
    'LBL_USE_OTHER_METHOD' => 'Använd en annan metod',
    'LBL_CHOOSE_METHOD' => 'Välj hur du vill verifiera din identitet.',
    'LBL_CHOICE_EMAIL' => 'Skicka en kod till min e-post',
    'LBL_CHOICE_EMAIL_SUB' => 'En engångskod skickas till din e-postadress',
    'LBL_CHOICE_TOTP' => 'Använd min autentiseringsapp',
    'LBL_CHOICE_TOTP_SUB' => 'Google Authenticator, Microsoft Authenticator, Authy...',
    'LBL_ENROLL_PROMPT' => 'Skanna denna QR-kod med din autentiseringsapp och ange sedan den 6-siffriga koden som visas för att slutföra konfigurationen.',
    'LBL_MANUAL_KEY' => 'Kan du inte skanna? Ange denna nyckel manuellt:',
    'LBL_ENROLL_VERIFY' => 'Bekräfta och fortsätt',
    'LBL_BACKUP_CODES_WARNING' => 'Spara dessa reservkoder på ett säkert ställe. Varje kod fungerar en gång och låter dig logga in om du förlorar åtkomsten till din app. De visas endast nu.',
    'LBL_BACKUP_CODES' => 'Reservkoder',
    'LBL_MANAGE_TITLE' => 'Tvåfaktorsautentisering',
    'LBL_AUTH_METHOD' => 'Autentiseringsmetod',
    'LBL_OPT_EMAIL' => 'Engångskod via e-post',
    'LBL_OPT_APP' => 'Autentiseringsapp',
    'LBL_METHOD_NOT_SET' => '— (väljer vid inloggning)',
    'LBL_ADMIN' => 'Admin',
    'LBL_METHOD_SAVED' => 'Din autentiseringsmetod har sparats.',
    'LBL_APP_STATUS_ON' => 'Din autentiseringsapp är konfigurerad.',
    'LBL_APP_STATUS_OFF' => 'Du har inte konfigurerat någon autentiseringsapp ännu.',
    'LBL_SETUP_APP' => 'Konfigurera autentiseringsapp',
    'LBL_RECONFIGURE_APP' => 'Konfigurera om autentiseringsapp',
    'LBL_REGEN_BACKUP' => 'Generera nya reservkoder',
    'LBL_UNUSED_CODES' => 'Oanvända reservkoder',
    'LBL_BACKUP_CODES_INTRO_USER' => 'Reservkoder låter dig logga in om du förlorar åtkomsten till din andra faktor. Varje kod fungerar en gång.',
    'LBL_INVALID_CODE' => 'Koden du angav är felaktig eller har upphört att gälla. Försök igen.',
    'LBL_TOO_MANY_ATTEMPTS' => 'För många misslyckade försök. Vänta %d minut(er) innan du försöker igen.',
    'LBL_CODE_RESENT' => 'En ny verifieringskod har skickats till din e-postadress.',
    'LBL_TPL_LOGIN_SUBJECT' => 'Din verifieringskod',
    'LBL_TPL_LOGIN_BODY' => '<p>Hej $username$,</p><p>Din engångskod för verifiering är: <strong>$code$</strong></p><p>Den är giltig i $minutes$ minut(er). Om du inte försökte logga in, kontakta din administratör.</p><p>$company$</p>',
    'LBL_TPL_BACKUP_SUBJECT' => 'Dina reservkoder för tvåfaktorsautentisering',
    'LBL_TPL_BACKUP_BODY' => '<p>Hej $username$,</p><p>Här är dina reservkoder för tvåfaktorsautentisering. Varje kod fungerar en gång:</p><p>$codes$</p><p>Förvara dem på ett säkert ställe.</p><p>$company$</p>',
    'LBL_DONE' => 'Klar',
];
