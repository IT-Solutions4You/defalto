<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Tour_Install_Model extends Core_Install_Model {

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