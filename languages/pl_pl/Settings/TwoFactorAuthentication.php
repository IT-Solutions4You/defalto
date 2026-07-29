<?php
/*
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 */

$languageStrings = [
    'TwoFactorAuthentication' => '2FA',
    'LBL_TAB_GENERAL' => 'Ustawienia ogólne',
    'LBL_STATUS' => 'Status',
    'LBL_STATUS_INTRO' => 'Włącz lub wyłącz uwierzytelnianie dwuskładnikowe dla całego CRM. Włączenie dodaje drugi krok logowania; wyłączenie przywraca standardowe logowanie.',
    'LBL_ACTIVE' => 'Aktywne',
    'LBL_INACTIVE' => 'Nieaktywne',
    'LBL_ACTIVATE_CONFIRM' => 'Włączenie będzie wymagać drugiego kroku logowania od wszystkich użytkowników (poza wyłączonymi). Przed kontynuowaniem upewnij się, że wysyłanie e-maili działa i że masz zapisane kody zapasowe - w przeciwnym razie możesz zostać zablokowany. Kontynuować?',
    'LBL_DEACTIVATE_CONFIRM' => 'Wyłączenie usunie drugi krok logowania dla wszystkich i przywróci pierwotne logowanie. Kontynuować?',
    'LBL_ACTIVATE_FAILED' => 'Nie można zastosować zmiany logowania. Sprawdź, czy plik logowania jest zapisywalny.',
    'LBL_NOT_ACTIVE_NOTE' => 'Uwierzytelnianie dwuskładnikowe jest zainstalowane, ale jeszcze nieaktywne. Drugi krok logowania nie jest wymagany, dopóki nie włączysz go poniżej.',
    'LBL_ENFORCE_ALL_NOTE' => 'Uwierzytelnianie dwuskładnikowe jest aktywne i wymagane od wszystkich użytkowników. Domyślnie każdy użytkownik otrzymuje przy logowaniu jednorazowy kod e-mailem. Dodaj użytkownika do listy wyjątków, aby go wykluczyć.',
    'LBL_ENFORCE_OFF_NOTE' => 'Uwierzytelnianie dwuskładnikowe jest obecnie wyłączone dla wszystkich użytkowników.',
    'LBL_AVAILABLE_METHODS' => 'Dostępne metody',
    'LBL_AVAILABLE_METHODS_INTRO' => 'Wybierz, których metod drugiego składnika mogą używać użytkownicy. Pozostaw obie włączone, aby każdy użytkownik wybierał przy logowaniu; włącz tylko jedną, aby wymagać jej od wszystkich. Co najmniej jedna metoda musi pozostać włączona.',
    'LBL_METHOD_EMAIL' => 'Jednorazowy kod e-mailem',
    'LBL_METHOD_TOTP' => 'Aplikacja uwierzytelniająca (TOTP)',
    'LBL_EMAIL_NEEDS_SERVER' => 'Skonfiguruj serwer poczty wychodzącej (Ustawienia > Serwer wychodzący), aby włączyć kody e-mail.',
    'LBL_TAB_USERS' => 'Użytkownicy',
    'LBL_USERS_INTRO' => 'Każdy aktywny użytkownik ma domyślnie włączone uwierzytelnianie dwuskładnikowe. Wyłącz je dla użytkownika, aby go wykluczyć (tylko hasło), zmień jego metodę lub zresetuj jego aplikację, jeśli zgubił urządzenie.',
    'LBL_USER' => 'Użytkownik',
    'LBL_USER_NOT_FOUND' => 'Nie znaleziono użytkownika.',
    'LBL_2FA' => '2FA',
    'LBL_ACTIONS' => 'Akcje',
    'LBL_RESET' => 'Resetuj',
    'LBL_RESET_CONFIRM' => 'Zresetować uwierzytelnianie dwuskładnikowe dla tego użytkownika? Jego aplikacja uwierzytelniająca i kody zapasowe przestaną działać, a przy następnym logowaniu skonfiguruje je ponownie.',
    'LBL_METHOD' => 'Metoda',
    'LBL_UNUSED_CODES' => 'Niewykorzystane kody zapasowe',
    'LBL_EMAILS_INTRO' => 'Dostosuj temat i treść tego e-maila. Użyj zmiennych poniżej - podczas wysyłania zostaną zastąpione rzeczywistymi wartościami. Użyj Przywróć domyślne, aby przywrócić oryginalny tekst.',
    'LBL_TPL_TITLE_LOGIN' => 'Szablon kodu weryfikacyjnego logowania',
    'LBL_TPL_TITLE_BACKUP' => 'Szablon kodów zapasowych',
    'LBL_TPL_SUBJECT' => 'Temat',
    'LBL_TPL_BODY' => 'Treść',
    'LBL_TPL_VARS' => 'Dostępne zmienne',
    'LBL_RESET_DEFAULT' => 'Przywróć domyślne',
];

$jsLanguageStrings = [
    'JS_SAVED' => 'Zapisano.',
    'JS_METHOD_SAVED' => 'Metoda zapisana.',
    'JS_METHODS_SAVED' => 'Zaktualizowano dostępne metody.',
    'JS_AT_LEAST_ONE_METHOD' => 'Co najmniej jedna metoda musi pozostać włączona.',
    'JS_RESET_TPL_CONFIRM' => 'Przywrócić ten szablon do domyślnego tekstu?',
];
