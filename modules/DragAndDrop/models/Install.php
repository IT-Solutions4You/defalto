<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class DragAndDrop_Install_Model extends Core_Install_Model
{
    private const LEGACY_MODULE = 'ITS4YouDragAndDrop';

    public array $registerCustomLinks = [
        [
            'DragAndDrop',
            'HEADERSCRIPT',
            'DragAndDrop_Detail_Js',
            'layouts/$LAYOUT$/modules/DragAndDrop/resources/Detail.js',
        ],
        [
            'DragAndDrop',
            'HEADERCSS',
            'DragAndDrop_Detail_Style',
            'layouts/$LAYOUT$/modules/DragAndDrop/resources/Detail.css',
        ],
    ];

    public function addCustomLinks(): void
    {
        $this->deleteLegacyCustomLinks();
        $this->updateCustomLinks();
    }

    public function deleteCustomLinks(): void
    {
        $this->updateCustomLinks(false);
    }

    public function getBlocks(): array
    {
        return [];
    }

    public function getTables(): array
    {
        return [];
    }

    public function installTables(): void
    {
        $this->migrateLegacyModuleMetadata();
    }

    private function deleteLegacyCustomLinks(): void
    {
        $this->db->pquery(
            'DELETE FROM vtiger_links
             WHERE linktype IN (?, ?) AND (linklabel IN (?, ?, ?) OR linkurl LIKE ?)',
            [
                'HEADERSCRIPT',
                'HEADERCSS',
                'ITS4YouDragAndDrop_HS_Js',
                'ITS4YouDragAndDropJS',
                'ITS4YouDragAndDrop_Js',
                '%/modules/' . self::LEGACY_MODULE . '/%',
            ]
        );
    }

    private function migrateLegacyModuleMetadata(): void
    {
        $this->deleteLegacyCustomLinks();

        $legacyResult = $this->db->pquery('SELECT tabid FROM vtiger_tab WHERE name = ?', [self::LEGACY_MODULE]);
        $currentResult = $this->db->pquery('SELECT tabid FROM vtiger_tab WHERE name = ?', ['DragAndDrop']);

        if (!$this->db->num_rows($legacyResult) || $this->db->num_rows($currentResult)) {
            return;
        }

        $this->db->pquery(
            'UPDATE vtiger_tab SET name = ?, tablabel = ? WHERE name = ?',
            ['DragAndDrop', 'Drag And Drop', self::LEGACY_MODULE]
        );
        $this->db->pquery(
            'UPDATE vtiger_ws_entity SET name = ? WHERE name = ?',
            ['DragAndDrop', self::LEGACY_MODULE]
        );
    }
}
