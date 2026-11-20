<?php
/*
 * Copyright (C) IT-Solutions4You s.r.o. All Rights Reserved.
 * Subject to the original ITS4YouMention license.
 */

class Mention_Install_Model extends Core_Install_Model
{
    public bool $registerDefaultSummaryWidgets = false;
    public array $registerCustomLinks = [
        ['Mention', 'HEADERSCRIPT', 'Mention_Js', 'layouts/$LAYOUT$/modules/Mention/resources/Mention.js'],
    ];
    public array $registerEventHandler = [
        ['vtiger.entity.aftersave', 'modules/Mention/handlers/Comment.php', 'Mention_Comment_Handler', '', 'ModComments'],
    ];

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
        $legacy = $this->db->pquery('SELECT tabid FROM vtiger_tab WHERE name = ?', ['ITS4YouMention']);

        if (!$this->db->num_rows($legacy)) {
            return;
        }

        $current = $this->db->pquery('SELECT tabid FROM vtiger_tab WHERE name = ?', ['Mention']);

        if ($this->db->num_rows($current)) {
            throw new Exception('Both Mention and ITS4YouMention are registered. Resolve the duplicate module before migrating.');
        }

        $this->db->pquery('UPDATE vtiger_tab SET name = ?, tablabel = ? WHERE name = ?', ['Mention', 'Mention', 'ITS4YouMention']);
        $this->db->pquery('UPDATE vtiger_ws_entity SET name = ? WHERE name = ?', ['Mention', 'ITS4YouMention']);
        $this->db->pquery('DELETE FROM vtiger_links WHERE tabid = ? AND linktype = ?', [$this->db->query_result($legacy, 0, 'tabid'), 'HEADERSCRIPT']);
        $events = new VTEventsManager($this->db);
        $events->unregisterHandler('ITS4YouMentionHandler');
    }

    public function addCustomLinks(): void
    {
        $this->updateCustomLinks();
        $this->updateEventHandler();
    }

    public function deleteCustomLinks(): void
    {
        $this->updateCustomLinks(false);
        $this->updateEventHandler(false);
    }
}
