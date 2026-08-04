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
    'LBL_TAB_GENERAL' => 'Configurações gerais',
    'LBL_STATUS' => 'Status',
    'LBL_STATUS_INTRO' => 'Ative ou desative a autenticação de dois fatores para todo o CRM. A ativação aplica a segunda etapa de login; a desativação restaura o login padrão.',
    'LBL_ACTIVE' => 'Ativo',
    'LBL_INACTIVE' => 'Inativo',
    'LBL_ACTIVATE_CONFIRM' => 'A ativação exigirá uma segunda etapa de login para todos os usuários (exceto os isentos). Antes de continuar, verifique se o envio de e-mails funciona e se você salvou códigos de backup - caso contrário, você poderá ficar bloqueado. Continuar?',
    'LBL_DEACTIVATE_CONFIRM' => 'A desativação removerá a segunda etapa de login para todos e restaurará o login original. Continuar?',
    'LBL_ACTIVATE_FAILED' => 'Não foi possível aplicar a alteração de login. Verifique se o arquivo de login tem permissão de gravação.',
    'LBL_NOT_ACTIVE_NOTE' => 'A autenticação de dois fatores está instalada, mas ainda não está ativa. Nenhuma segunda etapa de login é exigida até você ativá-la abaixo.',
    'LBL_ENFORCE_ALL_NOTE' => 'A autenticação de dois fatores está ativa e é obrigatória para todos os usuários. Por padrão, cada usuário recebe um código de uso único por e-mail ao entrar. Adicione um usuário à lista de isenções para excluí-lo.',
    'LBL_ENFORCE_OFF_NOTE' => 'A autenticação de dois fatores está atualmente desativada para todos os usuários.',
    'LBL_AVAILABLE_METHODS' => 'Métodos disponíveis',
    'LBL_AVAILABLE_METHODS_INTRO' => 'Escolha quais métodos de segundo fator os usuários podem usar. Deixe ambos ativados para que cada usuário escolha no login; ative apenas um para exigi-lo de todos. Pelo menos um método deve permanecer ativado.',
    'LBL_METHOD_EMAIL' => 'Código de uso único por e-mail',
    'LBL_METHOD_TOTP' => 'Aplicativo autenticador (TOTP)',
    'LBL_EMAIL_NEEDS_SERVER' => 'Configure um servidor de e-mail de saída (Configurações > Servidor de saída) para habilitar os códigos por e-mail.',
    'LBL_TAB_USERS' => 'Usuários',
    'LBL_USERS_INTRO' => 'Todo usuário ativo tem a autenticação de dois fatores ativada por padrão. Desative-a para um usuário para isentá-lo (apenas senha), altere o método dele ou redefina o autenticador dele se ele perdeu o dispositivo.',
    'LBL_USER' => 'Usuário',
    'LBL_USER_NOT_FOUND' => 'Usuário não encontrado.',
    'LBL_2FA' => '2FA',
    'LBL_ACTIONS' => 'Ações',
    'LBL_RESET' => 'Redefinir',
    'LBL_RESET_CONFIRM' => 'Redefinir a autenticação de dois fatores para este usuário? O aplicativo autenticador e os códigos de backup dele deixarão de funcionar e ele os configurará novamente no próximo login.',
    'LBL_METHOD' => 'Método',
    'LBL_UNUSED_CODES' => 'Códigos de backup não usados',
    'LBL_EMAILS_INTRO' => 'Personalize o assunto e o corpo deste e-mail. Use as variáveis abaixo - elas são substituídas pelos valores reais quando o e-mail é enviado. Use Redefinir para o padrão para restaurar o texto original.',
    'LBL_TPL_TITLE_LOGIN' => 'Modelo de código de verificação de login',
    'LBL_TPL_TITLE_BACKUP' => 'Modelo de códigos de backup',
    'LBL_TPL_SUBJECT' => 'Assunto',
    'LBL_TPL_BODY' => 'Corpo',
    'LBL_TPL_VARS' => 'Variáveis disponíveis',
    'LBL_RESET_DEFAULT' => 'Redefinir para o padrão',
];

$jsLanguageStrings = [
    'JS_SAVED' => 'Salvo.',
    'JS_METHOD_SAVED' => 'Método salvo.',
    'JS_METHODS_SAVED' => 'Métodos disponíveis atualizados.',
    'JS_AT_LEAST_ONE_METHOD' => 'Pelo menos um método deve permanecer ativado.',
    'JS_RESET_TPL_CONFIRM' => 'Redefinir este modelo para o texto padrão?',
];
