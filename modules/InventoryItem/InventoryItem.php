<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem extends CRMEntity
{
    public $table_name = 'df_inventoryitem';
    public $table_index = 'inventoryitemid';

    /**
     * Mandatory table for supporting custom fields.
     */
    public $customFieldTable = ['df_inventoryitemcf', 'inventoryitemid'];

    /**
     * Mandatory for Saving, Include tables related to this module.
     */
    public $tab_name = ['vtiger_crmentity', 'df_inventoryitem', 'df_inventoryitemcf'];

    /**
     * Mandatory for Saving, Include tablename and tablekey columnname here.
     */
    public $tab_name_index = [
        'vtiger_crmentity'   => 'crmid',
        'df_inventoryitem'   => 'inventoryitemid',
        'df_inventoryitemcf' => 'inventoryitemid'
    ];

    /**
     * Mandatory for Listing (Related listview)
     */
    public $list_fields = [
        'Item text'   => ['inventoryitem', 'item_text'],
        'Assigned To' => ['crmentity', 'smownerid']
    ];
    public $list_fields_name = [
        'Item text'   => 'item_text',
        'Assigned To' => 'assigned_user_id',
    ];

    // Make the field link to detail view
    public $list_link_field = 'item_text';

    // For Popup listview and UI type support
    public $search_fields = [
        'Item text'   => ['df_inventoryitem', 'item_text'],
        'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
    ];
    public $search_fields_name = [
        'Item text'   => 'item_text',
        'Assigned To' => 'assigned_user_id',
    ];

    // For Popup window record selection
    public $popup_fields = ['item_text'];

    // For Alphabetical search
    public $def_basicsearch_col = 'item_text';

    // Column value to use on detail view record text display
    public $def_detailview_recname = 'item_text';

    // Used when enabling/disabling the mandatory fields for the module.
    // Refers to vtiger_field.fieldname values.
    public $mandatory_fields = ['item_text', 'assigned_user_id'];

    public $default_order_by = 'inventoryitemid';
    public $default_sort_order = 'DESC';

    function __construct()
    {
        $this->log = Logger::getLogger('inventoryItem');
        $this->db = PearDatabase::getInstance();
        $this->column_fields = getColumnFields('InventoryItem');
    }

    /**
     * Invoked when special actions are performed on the module.
     *
     * @param string $moduleName Module name
     * @param string $eventType  Event Type
     *
     * @return void
     */
    public function vtlib_handler(string $moduleName, string $eventType)
    {
    }

    /**
     * Move the related records of the specified list of id's to the given record.
     *
     * @param string This module name
     * @param array List of Entity Id's from which related records need to be transfered
     * @param int Id of the the Record to which the related records are to be moved
     */
    public function transferRelatedRecords($module, $transferEntityIds, $entityId)
    {
        global $adb, $log;
        $log->debug("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

        $rel_table_arr = ['Documents' => 'vtiger_senotesrel', 'Attachments' => 'vtiger_seattachmentsrel'];

        $tbl_field_arr = ['vtiger_senotesrel' => 'notesid', 'vtiger_seattachmentsrel' => 'attachmentsid'];

        $entity_tbl_field_arr = ['vtiger_senotesrel' => 'crmid', 'vtiger_seattachmentsrel' => 'crmid'];

        foreach ($transferEntityIds as $transferId) {
            foreach ($rel_table_arr as $rel_module => $rel_table) {
                $id_field = $tbl_field_arr[$rel_table];
                $entity_id_field = $entity_tbl_field_arr[$rel_table];
                // IN clause to avoid duplicate entries
                $sel_result = $adb->pquery(
                    "select $id_field from $rel_table where $entity_id_field=? " .
                    " and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)",
                    [$transferId, $entityId]
                );
                $res_cnt = $adb->num_rows($sel_result);

                if ($res_cnt > 0) {
                    for ($i = 0; $i < $res_cnt; $i++) {
                        $id_field_value = $adb->query_result($sel_result, $i, $id_field);
                        $adb->pquery(
                            "update $rel_table set $entity_id_field=? where $entity_id_field=? and $id_field=?",
                            [$entityId, $transferId, $id_field_value]
                        );
                    }
                }
            }
        }

        parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
        $log->debug('Exiting transferRelatedRecords...');
    }

    /*
	 * Function to get the relation tables for related modules
	 * @param string $secmodule secondary module name
     *
	 * @return array  with table names and fieldnames storing relations between module and this module
	 */
    public function setRelationTables($secmodule)
    {
        return [];
    }

    /**
     * Function to unlink an entity with given Id from another entity
     *
     * @param int    $id
     * @param string $return_module
     * @param int    $return_id
     *
     * @return void
     */
    public function unlinkRelationship($id, $return_module, $return_id)
    {
        if (empty($return_module) || empty($return_id)) {
            return;
        }

        if ($return_module == 'Documents') {
            $sql = 'DELETE FROM vtiger_senotesrel WHERE crmid=? AND notesid=?';
            $this->db->pquery($sql, [$id, $return_id]);
        } else {
            parent::unlinkRelationship($id, $return_module, $return_id);
        }
    }

    /**
     * @param string $module
     *
     * @return void
     */
    public function save_module($module)
    {
    }

    /**
     * @param string    $module
     * @param int       $crmid
     * @param string    $withModule
     * @param int|array $withCrmids
     * @param array     $otherParams
     *
     * @return void
     */
    public function save_related_module($module, $crmid, $withModule, $withCrmids, $otherParams = [])
    {
        if (!is_array($withCrmids)) {
            $withCrmids = [$withCrmids];
        }

        foreach ($withCrmids as $withCrmid) {
            parent::save_related_module($module, $crmid, $withModule, $withCrmid);
        }
    }
}