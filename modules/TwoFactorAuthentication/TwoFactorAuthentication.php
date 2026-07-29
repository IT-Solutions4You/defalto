<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class TwoFactorAuthentication extends CRMExtension
{
    public string $moduleLabel = '2FA';
    public string $moduleName = 'TwoFactorAuthentication';
    public string $moduleVersion = '1.0';
    public string $parentName = 'Settings';

    /**
     * @throws Exception
     */
    public function vtlib_handler($moduleName, $eventType): void
    {
        Core_Install_Model::getInstance((string)$eventType, (string)$moduleName)->install();
    }
}
