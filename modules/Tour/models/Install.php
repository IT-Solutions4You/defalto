<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Tour_Install_Model extends Core_Install_Model
{
    /**
     * @var array|array[]
     */
    public array $registerCustomLinks = [
        ['Tour', 'HEADERSCRIPT', 'TourHeaderScriptJs', 'layouts/$LAYOUT$/modules/Tour/resources/Guide.js'],
    ];

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        $this->updateCustomLinks();
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        $this->updateCustomLinks(false);
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getTables(): array
    {
        return [];
    }

    /**
     * @return void
     */
    public function installTables(): void
    {
    }
}