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
    'LBL_2FA_TITLE' => 'Verificación en dos pasos',
    'LBL_EMAIL_PROMPT' => 'Enviamos un código de verificación a su dirección de correo electrónico. Introdúzcalo abajo para continuar.',
    'LBL_TOTP_PROMPT' => 'Introduzca el código de su aplicación de autenticación para continuar.',
    'LBL_CODE_PLACEHOLDER' => 'Código de verificación',
    'LBL_VERIFY' => 'Verificar',
    'LBL_RESEND_CODE' => 'Reenviar código',
    'LBL_BACK_TO_LOGIN' => 'Volver al inicio de sesión',
    'LBL_BACKUP_HINT' => '¿Perdió el acceso? Puede introducir uno de sus códigos de respaldo en su lugar.',
    'LBL_USE_OTHER_METHOD' => 'Usar otro método',
    'LBL_CHOOSE_METHOD' => 'Elija cómo desea verificar su identidad.',
    'LBL_CHOICE_EMAIL' => 'Enviar un código a mi correo',
    'LBL_CHOICE_EMAIL_SUB' => 'Se envía un código de un solo uso a su dirección de correo electrónico',
    'LBL_CHOICE_TOTP' => 'Usar mi aplicación de autenticación',
    'LBL_CHOICE_TOTP_SUB' => 'Google Authenticator, Microsoft Authenticator, Authy...',
    'LBL_ENROLL_PROMPT' => 'Escanee este código QR con su aplicación de autenticación y luego introduzca el código de 6 dígitos que muestra para finalizar la configuración.',
    'LBL_MANUAL_KEY' => '¿No puede escanear? Introduzca esta clave manualmente:',
    'LBL_ENROLL_VERIFY' => 'Confirmar y continuar',
    'LBL_BACKUP_CODES_WARNING' => 'Guarde estos códigos de respaldo en un lugar seguro. Cada uno funciona una vez y le permite iniciar sesión si pierde el acceso a su aplicación. Solo se muestran ahora.',
    'LBL_BACKUP_CODES' => 'Códigos de respaldo',
    'LBL_MANAGE_TITLE' => 'Autenticación de dos factores',
    'LBL_AUTH_METHOD' => 'Método de autenticación',
    'LBL_OPT_EMAIL' => 'Código de un solo uso por correo',
    'LBL_OPT_APP' => 'Aplicación de autenticación',
    'LBL_METHOD_NOT_SET' => '— (elige al iniciar sesión)',
    'LBL_ADMIN' => 'Admin',
    'LBL_METHOD_SAVED' => 'Su método de autenticación se ha guardado.',
    'LBL_APP_STATUS_ON' => 'Su aplicación de autenticación está configurada.',
    'LBL_APP_STATUS_OFF' => 'Aún no ha configurado una aplicación de autenticación.',
    'LBL_SETUP_APP' => 'Configurar aplicación de autenticación',
    'LBL_RECONFIGURE_APP' => 'Reconfigurar aplicación de autenticación',
    'LBL_REGEN_BACKUP' => 'Regenerar códigos de respaldo',
    'LBL_UNUSED_CODES' => 'Códigos de respaldo sin usar',
    'LBL_BACKUP_CODES_INTRO_USER' => 'Los códigos de respaldo le permiten iniciar sesión si pierde el acceso a su segundo factor. Cada código funciona una vez.',
    'LBL_INVALID_CODE' => 'El código introducido es incorrecto o ha caducado. Inténtelo de nuevo.',
    'LBL_TOO_MANY_ATTEMPTS' => 'Demasiados intentos fallidos. Espere %d minuto(s) antes de volver a intentarlo.',
    'LBL_CODE_RESENT' => 'Se ha enviado un nuevo código de verificación a su dirección de correo electrónico.',
    'LBL_TPL_LOGIN_SUBJECT' => 'Su código de verificación',
    'LBL_TPL_LOGIN_BODY' => '<p>Hola $username$,</p><p>Su código de verificación de un solo uso es: <strong>$code$</strong></p><p>Es válido durante $minutes$ minuto(s). Si no intentó iniciar sesión, póngase en contacto con su administrador.</p><p>$company$</p>',
    'LBL_TPL_BACKUP_SUBJECT' => 'Sus códigos de respaldo de dos factores',
    'LBL_TPL_BACKUP_BODY' => '<p>Hola $username$,</p><p>Estos son sus códigos de respaldo de dos factores. Cada código funciona una vez:</p><p>$codes$</p><p>Guárdelos en un lugar seguro.</p><p>$company$</p>',
    'LBL_DONE' => 'Listo',
];
