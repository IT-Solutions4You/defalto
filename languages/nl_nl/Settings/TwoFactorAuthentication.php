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
    'LBL_TAB_GENERAL' => 'Algemene instellingen',
    'LBL_STATUS' => 'Status',
    'LBL_STATUS_INTRO' => 'Schakel tweefactorauthenticatie voor het hele CRM in of uit. Bij inschakelen wordt de tweede aanmeldstap toegepast; bij uitschakelen wordt de standaard aanmelding hersteld.',
    'LBL_ACTIVE' => 'Actief',
    'LBL_INACTIVE' => 'Inactief',
    'LBL_ACTIVATE_CONFIRM' => 'Bij inschakelen is een tweede aanmeldstap vereist voor alle gebruikers (behalve de uitgezonderde). Zorg voordat u doorgaat dat uitgaande e-mail werkt en dat u back-upcodes hebt opgeslagen - anders kunt u buitengesloten raken. Doorgaan?',
    'LBL_DEACTIVATE_CONFIRM' => 'Bij uitschakelen wordt de tweede aanmeldstap voor iedereen verwijderd en de oorspronkelijke aanmelding hersteld. Doorgaan?',
    'LBL_ACTIVATE_FAILED' => 'Kan de aanmeldwijziging niet toepassen. Controleer of het aanmeldbestand schrijfbaar is.',
    'LBL_NOT_ACTIVE_NOTE' => 'Tweefactorauthenticatie is geïnstalleerd maar nog niet actief. Er is geen tweede aanmeldstap vereist totdat u deze hieronder inschakelt.',
    'LBL_ENFORCE_ALL_NOTE' => 'Tweefactorauthenticatie is actief en vereist voor alle gebruikers. Standaard ontvangt elke gebruiker bij het aanmelden een eenmalige code per e-mail. Voeg een gebruiker toe aan de uitzonderingslijst om deze uit te sluiten.',
    'LBL_ENFORCE_OFF_NOTE' => 'Tweefactorauthenticatie is momenteel uitgeschakeld voor alle gebruikers.',
    'LBL_AVAILABLE_METHODS' => 'Beschikbare methoden',
    'LBL_AVAILABLE_METHODS_INTRO' => 'Kies welke tweedefactormethoden gebruikers kunnen gebruiken. Laat beide ingeschakeld zodat elke gebruiker bij het aanmelden kan kiezen; schakel er slechts één in om deze voor iedereen te vereisen. Ten minste één methode moet ingeschakeld blijven.',
    'LBL_METHOD_EMAIL' => 'Eenmalige code per e-mail',
    'LBL_METHOD_TOTP' => 'Authenticator-app (TOTP)',
    'LBL_EMAIL_NEEDS_SERVER' => 'Stel een server voor uitgaande e-mail in (Instellingen > Uitgaande server) om e-mailcodes in te schakelen.',
    'LBL_TAB_USERS' => 'Gebruikers',
    'LBL_USERS_INTRO' => 'Elke actieve gebruiker heeft tweefactorauthenticatie standaard ingeschakeld. Schakel deze voor een gebruiker uit om deze uit te zonderen (alleen wachtwoord), wijzig zijn methode of stel zijn authenticator opnieuw in als hij zijn apparaat is verloren.',
    'LBL_USER' => 'Gebruiker',
    'LBL_USER_NOT_FOUND' => 'Gebruiker niet gevonden.',
    'LBL_2FA' => '2FA',
    'LBL_ACTIONS' => 'Acties',
    'LBL_RESET' => 'Resetten',
    'LBL_RESET_CONFIRM' => 'Tweefactorauthenticatie voor deze gebruiker resetten? Zijn authenticator-app en back-upcodes werken niet meer en hij stelt ze opnieuw in bij de volgende aanmelding.',
    'LBL_METHOD' => 'Methode',
    'LBL_UNUSED_CODES' => 'Ongebruikte back-upcodes',
    'LBL_EMAILS_INTRO' => 'Pas het onderwerp en de inhoud van deze e-mail aan. Gebruik de onderstaande variabelen - ze worden bij het verzenden vervangen door de echte waarden. Gebruik Terugzetten naar standaard om de oorspronkelijke tekst te herstellen.',
    'LBL_TPL_TITLE_LOGIN' => 'Sjabloon voor aanmeldverificatiecode',
    'LBL_TPL_TITLE_BACKUP' => 'Sjabloon voor back-upcodes',
    'LBL_TPL_SUBJECT' => 'Onderwerp',
    'LBL_TPL_BODY' => 'Inhoud',
    'LBL_TPL_VARS' => 'Beschikbare variabelen',
    'LBL_RESET_DEFAULT' => 'Terugzetten naar standaard',
];

$jsLanguageStrings = [
    'JS_SAVED' => 'Opgeslagen.',
    'JS_METHOD_SAVED' => 'Methode opgeslagen.',
    'JS_METHODS_SAVED' => 'Beschikbare methoden bijgewerkt.',
    'JS_AT_LEAST_ONE_METHOD' => 'Ten minste één methode moet ingeschakeld blijven.',
    'JS_RESET_TPL_CONFIRM' => 'Dit sjabloon terugzetten naar de standaardtekst?',
];
