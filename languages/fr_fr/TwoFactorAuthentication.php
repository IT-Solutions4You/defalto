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
    'LBL_2FA_TITLE' => 'Vérification en deux étapes',
    'LBL_EMAIL_PROMPT' => 'Nous avons envoyé un code de vérification à votre adresse e-mail. Saisissez-le ci-dessous pour continuer.',
    'LBL_TOTP_PROMPT' => 'Saisissez le code de votre application d\'authentification pour continuer.',
    'LBL_CODE_PLACEHOLDER' => 'Code de vérification',
    'LBL_VERIFY' => 'Vérifier',
    'LBL_RESEND_CODE' => 'Renvoyer le code',
    'LBL_BACK_TO_LOGIN' => 'Retour à la connexion',
    'LBL_BACKUP_HINT' => 'Accès perdu ? Vous pouvez saisir l\'un de vos codes de secours à la place.',
    'LBL_USE_OTHER_METHOD' => 'Utiliser une autre méthode',
    'LBL_CHOOSE_METHOD' => 'Choisissez comment vérifier votre identité.',
    'LBL_CHOICE_EMAIL' => 'Envoyer un code à mon e-mail',
    'LBL_CHOICE_EMAIL_SUB' => 'Un code à usage unique est envoyé à votre adresse e-mail',
    'LBL_CHOICE_TOTP' => 'Utiliser mon application d\'authentification',
    'LBL_CHOICE_TOTP_SUB' => 'Google Authenticator, Microsoft Authenticator, Authy...',
    'LBL_ENROLL_PROMPT' => 'Scannez ce QR code avec votre application d\'authentification, puis saisissez le code à 6 chiffres affiché pour terminer la configuration.',
    'LBL_MANUAL_KEY' => 'Impossible de scanner ? Saisissez cette clé manuellement :',
    'LBL_ENROLL_VERIFY' => 'Confirmer et continuer',
    'LBL_BACKUP_CODES_WARNING' => 'Conservez ces codes de secours en lieu sûr. Chacun fonctionne une fois et vous permet de vous connecter si vous perdez l\'accès à votre application. Ils ne sont affichés que maintenant.',
    'LBL_BACKUP_CODES' => 'Codes de secours',
    'LBL_MANAGE_TITLE' => 'Authentification à deux facteurs',
    'LBL_AUTH_METHOD' => 'Méthode d\'authentification',
    'LBL_OPT_EMAIL' => 'Code à usage unique par e-mail',
    'LBL_OPT_APP' => 'Application d\'authentification',
    'LBL_METHOD_NOT_SET' => '— (choix à la connexion)',
    'LBL_ADMIN' => 'Admin',
    'LBL_METHOD_SAVED' => 'Votre méthode d\'authentification a été enregistrée.',
    'LBL_APP_STATUS_ON' => 'Votre application d\'authentification est configurée.',
    'LBL_APP_STATUS_OFF' => 'Vous n\'avez pas encore configuré d\'application d\'authentification.',
    'LBL_SETUP_APP' => 'Configurer l\'application d\'authentification',
    'LBL_RECONFIGURE_APP' => 'Reconfigurer l\'application d\'authentification',
    'LBL_REGEN_BACKUP' => 'Régénérer les codes de secours',
    'LBL_UNUSED_CODES' => 'Codes de secours inutilisés',
    'LBL_BACKUP_CODES_INTRO_USER' => 'Les codes de secours vous permettent de vous connecter si vous perdez l\'accès à votre second facteur. Chaque code fonctionne une fois.',
    'LBL_INVALID_CODE' => 'Le code saisi est incorrect ou a expiré. Veuillez réessayer.',
    'LBL_TOO_MANY_ATTEMPTS' => 'Trop de tentatives échouées. Veuillez attendre %d minute(s) avant de réessayer.',
    'LBL_CODE_RESENT' => 'Un nouveau code de vérification a été envoyé à votre adresse e-mail.',
    'LBL_TPL_LOGIN_SUBJECT' => 'Votre code de vérification',
    'LBL_TPL_LOGIN_BODY' => '<p>Bonjour $username$,</p><p>Votre code de vérification à usage unique est : <strong>$code$</strong></p><p>Il est valable $minutes$ minute(s). Si vous n\'avez pas tenté de vous connecter, veuillez contacter votre administrateur.</p><p>$company$</p>',
    'LBL_TPL_BACKUP_SUBJECT' => 'Vos codes de secours à deux facteurs',
    'LBL_TPL_BACKUP_BODY' => '<p>Bonjour $username$,</p><p>Voici vos codes de secours à deux facteurs. Chaque code fonctionne une fois :</p><p>$codes$</p><p>Conservez-les en lieu sûr.</p><p>$company$</p>',
    'LBL_DONE' => 'Terminé',
];
