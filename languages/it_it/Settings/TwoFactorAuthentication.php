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
    'LBL_TAB_GENERAL' => 'Impostazioni generali',
    'LBL_STATUS' => 'Stato',
    'LBL_STATUS_INTRO' => 'Attiva o disattiva l\'autenticazione a due fattori per l\'intero CRM. L\'attivazione applica il secondo passaggio di login; la disattivazione ripristina il login standard.',
    'LBL_ACTIVE' => 'Attivo',
    'LBL_INACTIVE' => 'Inattivo',
    'LBL_ACTIVATE_CONFIRM' => 'L\'attivazione richiederà un secondo passaggio di login per tutti gli utenti (tranne quelli esentati). Prima di continuare, assicurati che l\'invio di e-mail funzioni e di aver salvato i codici di backup, altrimenti potresti restare bloccato. Continuare?',
    'LBL_DEACTIVATE_CONFIRM' => 'La disattivazione rimuoverà il secondo passaggio di login per tutti e ripristinerà il login originale. Continuare?',
    'LBL_ACTIVATE_FAILED' => 'Impossibile applicare la modifica al login. Verifica che il file di login sia scrivibile.',
    'LBL_NOT_ACTIVE_NOTE' => 'L\'autenticazione a due fattori è installata ma non ancora attiva. Non è richiesto alcun secondo passaggio di login finché non la attivi qui sotto.',
    'LBL_ENFORCE_ALL_NOTE' => 'L\'autenticazione a due fattori è attiva e obbligatoria per tutti gli utenti. Per impostazione predefinita, ogni utente riceve un codice monouso via e-mail al login. Aggiungi un utente all\'elenco delle esenzioni per escluderlo.',
    'LBL_ENFORCE_OFF_NOTE' => 'L\'autenticazione a due fattori è attualmente disattivata per tutti gli utenti.',
    'LBL_AVAILABLE_METHODS' => 'Metodi disponibili',
    'LBL_AVAILABLE_METHODS_INTRO' => 'Scegli quali metodi di secondo fattore possono usare gli utenti. Lascia entrambi attivi per far scegliere a ciascun utente al login; attivane uno solo per imporlo a tutti. Almeno un metodo deve restare attivo.',
    'LBL_METHOD_EMAIL' => 'Codice monouso via e-mail',
    'LBL_METHOD_TOTP' => 'App di autenticazione (TOTP)',
    'LBL_EMAIL_NEEDS_SERVER' => 'Configura un server di posta in uscita (Impostazioni > Server in uscita) per abilitare i codici via e-mail.',
    'LBL_TAB_USERS' => 'Utenti',
    'LBL_USERS_INTRO' => 'Ogni utente attivo ha l\'autenticazione a due fattori attiva per impostazione predefinita. Disattivala per un utente per esentarlo (solo password), cambia il suo metodo o reimposta il suo autenticatore se ha perso il dispositivo.',
    'LBL_USER' => 'Utente',
    'LBL_USER_NOT_FOUND' => 'Utente non trovato.',
    'LBL_2FA' => '2FA',
    'LBL_ACTIONS' => 'Azioni',
    'LBL_RESET' => 'Reimposta',
    'LBL_RESET_CONFIRM' => 'Reimpostare l\'autenticazione a due fattori per questo utente? La sua app di autenticazione e i codici di backup smetteranno di funzionare e dovrà riconfigurarli al prossimo login.',
    'LBL_METHOD' => 'Metodo',
    'LBL_UNUSED_CODES' => 'Codici di backup non utilizzati',
    'LBL_EMAILS_INTRO' => 'Personalizza l\'oggetto e il corpo di questa e-mail. Usa le variabili qui sotto: vengono sostituite con i valori reali all\'invio dell\'e-mail. Usa Ripristina predefinito per ripristinare il testo originale.',
    'LBL_TPL_TITLE_LOGIN' => 'Modello del codice di verifica di login',
    'LBL_TPL_TITLE_BACKUP' => 'Modello dei codici di backup',
    'LBL_TPL_SUBJECT' => 'Oggetto',
    'LBL_TPL_BODY' => 'Corpo',
    'LBL_TPL_VARS' => 'Variabili disponibili',
    'LBL_RESET_DEFAULT' => 'Ripristina predefinito',
];

$jsLanguageStrings = [
    'JS_SAVED' => 'Salvato.',
    'JS_METHOD_SAVED' => 'Metodo salvato.',
    'JS_METHODS_SAVED' => 'Metodi disponibili aggiornati.',
    'JS_AT_LEAST_ONE_METHOD' => 'Almeno un metodo deve restare attivo.',
    'JS_RESET_TPL_CONFIRM' => 'Ripristinare questo modello al testo predefinito?',
];
