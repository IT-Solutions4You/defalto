<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_AiAvailability_Model
{
    public function getStatus(): array
    {
        $module = Vtiger_Module_Model::getInstance('AI');
        $licensed = Installer_License_Model::isMembershipActive();
        $enabled = $module && $module->isActive();
        $canCheckSetup = $enabled && is_callable([AI_Record_Model::class, 'hasApiKey']);
        $configured = $canCheckSetup && AI_Record_Model::hasApiKey();
        $ready = $licensed && $configured;
        $isAdmin = Users_Record_Model::getCurrentUserModel()->isAdminUser();
        $next = 'LBL_AI_TRY_CHAT';
        $action = 'chat';

        if (!$licensed) {
            $next = 'LBL_AI_ACTIVATE_MEMBERSHIP';
            $action = 'license';
        } elseif (!$module) {
            $next = 'LBL_AI_INSTALL_EXTENSION';
            $action = 'install';
        } elseif (!$enabled) {
            $next = 'LBL_AI_ENABLE_EXTENSION';
            $action = 'enable';
        } elseif (!$canCheckSetup) {
            $next = 'LBL_AI_SETUP_UNVERIFIED';
            $action = null;
        } elseif (!$configured) {
            $next = 'LBL_AI_CONFIGURE_KEY';
            $action = 'settings';
        } elseif (!Users_Privileges_Model::isPermitted('AI', 'DetailView')) {
            $next = 'LBL_AI_NO_ACCESS';
            $action = null;
        }

        return [
            'steps' => [
                'LBL_AI_ACTIVE_LICENSE' => $licensed,
                'LBL_AI_INSTALLED_EXTENSION' => (bool)$module,
                'LBL_AI_READY' => $ready,
            ],
            'next' => $next,
            'action' => $isAdmin || 'chat' === $action ? $action : null,
            'contactAdmin' => !$isAdmin && 'chat' !== $action,
        ];
    }
}
