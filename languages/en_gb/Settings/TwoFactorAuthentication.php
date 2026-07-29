<?php
/*
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 */

$languageStrings = [
    'TwoFactorAuthentication' => '2FA',
    'LBL_TAB_GENERAL' => 'General settings',
    'LBL_STATUS' => 'Status',
    'LBL_STATUS_INTRO' => 'Turn two-factor authentication on or off for the whole CRM. Activating applies the second login step; deactivating restores the standard login.',
    'LBL_ACTIVE' => 'Active',
    'LBL_INACTIVE' => 'Inactive',
    'LBL_ACTIVATE_CONFIRM' => 'Activating will require a second login step for all users (except exempt ones). Before continuing, make sure outgoing mail works and that you have saved backup codes - otherwise you may be locked out. Continue?',
    'LBL_DEACTIVATE_CONFIRM' => 'Deactivating will remove the second login step for everyone and restore the original login. Continue?',
    'LBL_ACTIVATE_FAILED' => 'Could not apply the login change. Check that the login file is writable.',
    'LBL_NOT_ACTIVE_NOTE' => 'Two-factor authentication is installed but not active yet. No second login step is required until you activate it below.',
    'LBL_ENFORCE_ALL_NOTE' => 'Two-factor authentication is active and required for all users. By default each user receives a one-time passcode by email at login. Add a user to the exemption list to opt them out.',
    'LBL_ENFORCE_OFF_NOTE' => 'Two-factor authentication is currently turned off for all users.',
    'LBL_AVAILABLE_METHODS' => 'Available methods',
    'LBL_AVAILABLE_METHODS_INTRO' => 'Choose which second-factor methods users can use. Leave both enabled to let each user pick at login; enable only one to require it for everyone. At least one method must stay enabled.',
    'LBL_METHOD_EMAIL' => 'Email one-time code',
    'LBL_METHOD_TOTP' => 'Authenticator app (TOTP)',
    'LBL_EMAIL_NEEDS_SERVER' => 'Configure an outgoing mail server (Settings > Outgoing Server) to enable email codes.',
    'LBL_TAB_USERS' => 'Users',
    'LBL_USERS_INTRO' => 'Every active user has two-factor authentication on by default. Turn it off for a user to exempt them (password only), change their method, or reset their authenticator if they lost their device.',
    'LBL_USER' => 'User',
    'LBL_USER_NOT_FOUND' => 'User not found.',
    'LBL_2FA' => '2FA',
    'LBL_ACTIONS' => 'Actions',
    'LBL_RESET' => 'Reset',
    'LBL_RESET_CONFIRM' => 'Reset two-factor authentication for this user? Their authenticator app and backup codes will stop working and they will set it up again at next login.',
    'LBL_METHOD' => 'Method',
    'LBL_UNUSED_CODES' => 'Unused backup codes',
    'LBL_EMAILS_INTRO' => 'Customise the subject and body of this email. Use the placeholders below - they are replaced with real values when the email is sent. Use Reset to default to restore the built-in text.',
    'LBL_TPL_TITLE_LOGIN' => 'Login verification code template',
    'LBL_TPL_TITLE_BACKUP' => 'Backup codes template',
    'LBL_TPL_SUBJECT' => 'Subject',
    'LBL_TPL_BODY' => 'Body',
    'LBL_TPL_VARS' => 'Available placeholders',
    'LBL_RESET_DEFAULT' => 'Reset to default',
];

$jsLanguageStrings = [
    'JS_SAVED' => 'Saved.',
    'JS_METHOD_SAVED' => 'Method saved.',
    'JS_METHODS_SAVED' => 'Available methods updated.',
    'JS_AT_LEAST_ONE_METHOD' => 'At least one method must stay enabled.',
    'JS_RESET_TPL_CONFIRM' => 'Reset this template to its default text?',
];
