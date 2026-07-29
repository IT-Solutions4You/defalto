<?php
/*
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 */

$languageStrings = [
    'TwoFactorAuthentication' => '2FA',
    'LBL_TAB_GENERAL' => 'Paramètres généraux',
    'LBL_STATUS' => 'État',
    'LBL_STATUS_INTRO' => 'Activez ou désactivez l\'authentification à deux facteurs pour tout le CRM. L\'activation ajoute la seconde étape de connexion ; la désactivation restaure la connexion standard.',
    'LBL_ACTIVE' => 'Actif',
    'LBL_INACTIVE' => 'Inactif',
    'LBL_ACTIVATE_CONFIRM' => 'L\'activation exigera une seconde étape de connexion pour tous les utilisateurs (sauf ceux qui en sont exemptés). Avant de continuer, assurez-vous que l\'envoi d\'e-mails fonctionne et que vous avez enregistré des codes de secours - sinon vous risquez d\'être bloqué. Continuer ?',
    'LBL_DEACTIVATE_CONFIRM' => 'La désactivation supprimera la seconde étape de connexion pour tout le monde et restaurera la connexion d\'origine. Continuer ?',
    'LBL_ACTIVATE_FAILED' => 'Impossible d\'appliquer la modification de connexion. Vérifiez que le fichier de connexion est accessible en écriture.',
    'LBL_NOT_ACTIVE_NOTE' => 'L\'authentification à deux facteurs est installée mais pas encore active. Aucune seconde étape de connexion n\'est requise tant que vous ne l\'activez pas ci-dessous.',
    'LBL_ENFORCE_ALL_NOTE' => 'L\'authentification à deux facteurs est active et requise pour tous les utilisateurs. Par défaut, chaque utilisateur reçoit un code à usage unique par e-mail lors de la connexion. Ajoutez un utilisateur à la liste d\'exemption pour l\'en exclure.',
    'LBL_ENFORCE_OFF_NOTE' => 'L\'authentification à deux facteurs est actuellement désactivée pour tous les utilisateurs.',
    'LBL_AVAILABLE_METHODS' => 'Méthodes disponibles',
    'LBL_AVAILABLE_METHODS_INTRO' => 'Choisissez les méthodes de second facteur que les utilisateurs peuvent utiliser. Laissez les deux activées pour que chaque utilisateur choisisse à la connexion ; n\'en activez qu\'une pour l\'imposer à tous. Au moins une méthode doit rester activée.',
    'LBL_METHOD_EMAIL' => 'Code à usage unique par e-mail',
    'LBL_METHOD_TOTP' => 'Application d\'authentification (TOTP)',
    'LBL_EMAIL_NEEDS_SERVER' => 'Configurez un serveur de messagerie sortant (Paramètres > Serveur sortant) pour activer les codes par e-mail.',
    'LBL_TAB_USERS' => 'Utilisateurs',
    'LBL_USERS_INTRO' => 'Chaque utilisateur actif a l\'authentification à deux facteurs activée par défaut. Désactivez-la pour un utilisateur afin de l\'exempter (mot de passe seul), changez sa méthode ou réinitialisez son authentificateur s\'il a perdu son appareil.',
    'LBL_USER' => 'Utilisateur',
    'LBL_USER_NOT_FOUND' => 'Utilisateur introuvable.',
    'LBL_2FA' => '2FA',
    'LBL_ACTIONS' => 'Actions',
    'LBL_RESET' => 'Réinitialiser',
    'LBL_RESET_CONFIRM' => 'Réinitialiser l\'authentification à deux facteurs pour cet utilisateur ? Son application d\'authentification et ses codes de secours cesseront de fonctionner et il devra les reconfigurer à la prochaine connexion.',
    'LBL_METHOD' => 'Méthode',
    'LBL_UNUSED_CODES' => 'Codes de secours inutilisés',
    'LBL_EMAILS_INTRO' => 'Personnalisez l\'objet et le corps de cet e-mail. Utilisez les variables ci-dessous - elles sont remplacées par les valeurs réelles lors de l\'envoi. Utilisez Réinitialiser par défaut pour restaurer le texte d\'origine.',
    'LBL_TPL_TITLE_LOGIN' => 'Modèle de code de vérification de connexion',
    'LBL_TPL_TITLE_BACKUP' => 'Modèle de codes de secours',
    'LBL_TPL_SUBJECT' => 'Objet',
    'LBL_TPL_BODY' => 'Corps',
    'LBL_TPL_VARS' => 'Variables disponibles',
    'LBL_RESET_DEFAULT' => 'Réinitialiser par défaut',
];

$jsLanguageStrings = [
    'JS_SAVED' => 'Enregistré.',
    'JS_METHOD_SAVED' => 'Méthode enregistrée.',
    'JS_METHODS_SAVED' => 'Méthodes disponibles mises à jour.',
    'JS_AT_LEAST_ONE_METHOD' => 'Au moins une méthode doit rester activée.',
    'JS_RESET_TPL_CONFIRM' => 'Réinitialiser ce modèle à son texte par défaut ?',
];
