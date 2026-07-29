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
    'LBL_2FA_TITLE' => 'Verifica in due passaggi',
    'LBL_EMAIL_PROMPT' => 'Abbiamo inviato un codice di verifica al tuo indirizzo e-mail. Inseriscilo qui sotto per continuare.',
    'LBL_TOTP_PROMPT' => 'Inserisci il codice della tua app di autenticazione per continuare.',
    'LBL_CODE_PLACEHOLDER' => 'Codice di verifica',
    'LBL_VERIFY' => 'Verifica',
    'LBL_RESEND_CODE' => 'Invia di nuovo il codice',
    'LBL_BACK_TO_LOGIN' => 'Torna al login',
    'LBL_BACKUP_HINT' => 'Accesso perso? Puoi inserire uno dei tuoi codici di backup.',
    'LBL_USE_OTHER_METHOD' => 'Usa un altro metodo',
    'LBL_CHOOSE_METHOD' => 'Scegli come vuoi verificare la tua identità.',
    'LBL_CHOICE_EMAIL' => 'Invia un codice alla mia e-mail',
    'LBL_CHOICE_EMAIL_SUB' => 'Un codice monouso viene inviato al tuo indirizzo e-mail',
    'LBL_CHOICE_TOTP' => 'Usa la mia app di autenticazione',
    'LBL_CHOICE_TOTP_SUB' => 'Google Authenticator, Microsoft Authenticator, Authy...',
    'LBL_ENROLL_PROMPT' => 'Scansiona questo codice QR con la tua app di autenticazione, quindi inserisci il codice a 6 cifre mostrato per completare la configurazione.',
    'LBL_MANUAL_KEY' => 'Non riesci a scansionare? Inserisci questa chiave manualmente:',
    'LBL_ENROLL_VERIFY' => 'Conferma e continua',
    'LBL_BACKUP_CODES_WARNING' => 'Conserva questi codici di backup in un luogo sicuro. Ognuno funziona una volta e ti permette di accedere se perdi l\'accesso alla tua app. Vengono mostrati solo ora.',
    'LBL_BACKUP_CODES' => 'Codici di backup',
    'LBL_MANAGE_TITLE' => 'Autenticazione a due fattori',
    'LBL_AUTH_METHOD' => 'Metodo di autenticazione',
    'LBL_OPT_EMAIL' => 'Codice monouso via e-mail',
    'LBL_OPT_APP' => 'App di autenticazione',
    'LBL_METHOD_NOT_SET' => '— (sceglie al login)',
    'LBL_ADMIN' => 'Admin',
    'LBL_METHOD_SAVED' => 'Il tuo metodo di autenticazione è stato salvato.',
    'LBL_APP_STATUS_ON' => 'La tua app di autenticazione è configurata.',
    'LBL_APP_STATUS_OFF' => 'Non hai ancora configurato un\'app di autenticazione.',
    'LBL_SETUP_APP' => 'Configura app di autenticazione',
    'LBL_RECONFIGURE_APP' => 'Riconfigura app di autenticazione',
    'LBL_REGEN_BACKUP' => 'Rigenera codici di backup',
    'LBL_UNUSED_CODES' => 'Codici di backup non utilizzati',
    'LBL_BACKUP_CODES_INTRO_USER' => 'I codici di backup ti permettono di accedere se perdi l\'accesso al tuo secondo fattore. Ogni codice funziona una volta.',
    'LBL_INVALID_CODE' => 'Il codice inserito non è corretto o è scaduto. Riprova.',
    'LBL_TOO_MANY_ATTEMPTS' => 'Troppi tentativi falliti. Attendi %d minuto/i prima di riprovare.',
    'LBL_CODE_RESENT' => 'Un nuovo codice di verifica è stato inviato al tuo indirizzo e-mail.',
    'LBL_TPL_LOGIN_SUBJECT' => 'Il tuo codice di verifica',
    'LBL_TPL_LOGIN_BODY' => '<p>Ciao $username$,</p><p>Il tuo codice di verifica monouso è: <strong>$code$</strong></p><p>È valido per $minutes$ minuto/i. Se non hai tentato di accedere, contatta il tuo amministratore.</p><p>$company$</p>',
    'LBL_TPL_BACKUP_SUBJECT' => 'I tuoi codici di backup a due fattori',
    'LBL_TPL_BACKUP_BODY' => '<p>Ciao $username$,</p><p>Ecco i tuoi codici di backup a due fattori. Ogni codice funziona una volta:</p><p>$codes$</p><p>Conservali in un luogo sicuro.</p><p>$company$</p>',
    'LBL_DONE' => 'Fatto',
];
