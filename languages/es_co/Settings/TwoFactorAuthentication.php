<?php
/*
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 */

$languageStrings = [
    'TwoFactorAuthentication' => '2FA',
    'LBL_TAB_GENERAL' => 'Configuración general',
    'LBL_STATUS' => 'Estado',
    'LBL_STATUS_INTRO' => 'Active o desactive la autenticación de dos factores para todo el CRM. La activación aplica el segundo paso de inicio de sesión; la desactivación restaura el inicio de sesión estándar.',
    'LBL_ACTIVE' => 'Activo',
    'LBL_INACTIVE' => 'Inactivo',
    'LBL_ACTIVATE_CONFIRM' => 'La activación requerirá un segundo paso de inicio de sesión para todos los usuarios (excepto los exentos). Antes de continuar, asegúrese de que el envío de correo funciona y de que ha guardado códigos de respaldo; de lo contrario, podría quedar bloqueado. ¿Continuar?',
    'LBL_DEACTIVATE_CONFIRM' => 'La desactivación eliminará el segundo paso de inicio de sesión para todos y restaurará el inicio de sesión original. ¿Continuar?',
    'LBL_ACTIVATE_FAILED' => 'No se pudo aplicar el cambio de inicio de sesión. Compruebe que el archivo de inicio de sesión tiene permisos de escritura.',
    'LBL_NOT_ACTIVE_NOTE' => 'La autenticación de dos factores está instalada pero aún no está activa. No se requiere un segundo paso de inicio de sesión hasta que la active a continuación.',
    'LBL_ENFORCE_ALL_NOTE' => 'La autenticación de dos factores está activa y es obligatoria para todos los usuarios. De forma predeterminada, cada usuario recibe un código de un solo uso por correo al iniciar sesión. Añada un usuario a la lista de exenciones para excluirlo.',
    'LBL_ENFORCE_OFF_NOTE' => 'La autenticación de dos factores está actualmente desactivada para todos los usuarios.',
    'LBL_AVAILABLE_METHODS' => 'Métodos disponibles',
    'LBL_AVAILABLE_METHODS_INTRO' => 'Elija qué métodos de segundo factor pueden usar los usuarios. Deje ambos activados para que cada usuario elija al iniciar sesión; active solo uno para exigirlo a todos. Al menos un método debe permanecer activado.',
    'LBL_METHOD_EMAIL' => 'Código de un solo uso por correo',
    'LBL_METHOD_TOTP' => 'Aplicación de autenticación (TOTP)',
    'LBL_EMAIL_NEEDS_SERVER' => 'Configure un servidor de correo saliente (Configuración > Servidor saliente) para habilitar los códigos por correo.',
    'LBL_TAB_USERS' => 'Usuarios',
    'LBL_USERS_INTRO' => 'Cada usuario activo tiene la autenticación de dos factores activada de forma predeterminada. Desactívela para un usuario para eximirlo (solo contraseña), cambie su método o restablezca su autenticador si perdió su dispositivo.',
    'LBL_USER' => 'Usuario',
    'LBL_USER_NOT_FOUND' => 'Usuario no encontrado.',
    'LBL_2FA' => '2FA',
    'LBL_ACTIONS' => 'Acciones',
    'LBL_RESET' => 'Restablecer',
    'LBL_RESET_CONFIRM' => '¿Restablecer la autenticación de dos factores para este usuario? Su aplicación de autenticación y sus códigos de respaldo dejarán de funcionar y deberá configurarlos de nuevo en el próximo inicio de sesión.',
    'LBL_METHOD' => 'Método',
    'LBL_UNUSED_CODES' => 'Códigos de respaldo sin usar',
    'LBL_EMAILS_INTRO' => 'Personalice el asunto y el cuerpo de este correo. Use las variables de abajo; se reemplazan por los valores reales al enviar el correo. Use Restablecer valores predeterminados para restaurar el texto original.',
    'LBL_TPL_TITLE_LOGIN' => 'Plantilla de código de verificación de inicio de sesión',
    'LBL_TPL_TITLE_BACKUP' => 'Plantilla de códigos de respaldo',
    'LBL_TPL_SUBJECT' => 'Asunto',
    'LBL_TPL_BODY' => 'Cuerpo',
    'LBL_TPL_VARS' => 'Variables disponibles',
    'LBL_RESET_DEFAULT' => 'Restablecer valores predeterminados',
];

$jsLanguageStrings = [
    'JS_SAVED' => 'Guardado.',
    'JS_METHOD_SAVED' => 'Método guardado.',
    'JS_METHODS_SAVED' => 'Métodos disponibles actualizados.',
    'JS_AT_LEAST_ONE_METHOD' => 'Al menos un método debe permanecer activado.',
    'JS_RESET_TPL_CONFIRM' => '¿Restablecer esta plantilla a su texto predeterminado?',
];
