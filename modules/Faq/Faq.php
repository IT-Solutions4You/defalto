<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header$
 * Description:  Defines the Account SugarBean Account entity with the necessary
 * methods and variables.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

// Faq is used to store vtiger_faq information.
class Faq extends CRMEntity
{
    public string $moduleName = 'Faq';
    public string $parentName = 'SUPPORT';
    public $table_name = "vtiger_faq";
    public $table_index = 'id';
    //fix for Custom Field for FAQ
    public $tab_name = ['vtiger_crmentity', 'vtiger_faq', 'vtiger_faqcf'];
    public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_faq' => 'id', 'vtiger_faqcomments' => 'faqid', 'vtiger_faqcf' => 'faqid'];
    public $customFieldTable = ['vtiger_faqcf', 'faqid'];

    public $entity_table = "vtiger_crmentity";

    public $column_fields = [];

    public $sortby_fields = ['question', 'category', 'id'];

    // This is the list of vtiger_fields that are in the lists.
    public $list_fields = [
        'FAQ Id'        => ['faq' => 'id'],
        'Question'      => ['faq' => 'question'],
        'Category'      => ['faq' => 'faqcategories'],
        'Product Name'  => ['faq' => 'product_id'],
        'Created Time'  => ['crmentity' => 'createdtime'],
        'Modified Time' => ['crmentity' => 'modifiedtime'],
    ];

    public $list_fields_name = [
        'FAQ Id'        => '',
        'Question'      => 'question',
        'Category'      => 'faqcategories',
        'Product Name'  => 'product_id',
        'Created Time'  => 'createdtime',
        'Modified Time' => 'modifiedtime',
    ];
    public $list_link_field = 'question';

    public $search_fields = [
        'Account Name' => ['account' => 'accountname'],
        'City'         => ['accountbillads' => 'bill_city'],
    ];

    public $search_fields_name = [
        'Account Name' => 'accountname',
        'City'         => 'bill_city',
    ];

    //Added these variables which are used as default order by and sortorder in ListView
    public $default_order_by = 'id';
    public $default_sort_order = 'DESC';

    public $mandatory_fields = ['question', 'faq_answer', 'createdtime', 'modifiedtime'];

    // For Alphabetical search
    public $def_basicsearch_col = 'question';

    /**
     * @inheritDoc
     */
    public function save_module(string $module)
    {
        //Inserting into Faq comment table
        $this->insertIntoFAQCommentTable('vtiger_faqcomments', $module);
    }

    /** Function to insert values in vtiger_faqcomments table for the specified module,
     *
     * @param $table_name -- table name:: Type varchar
     * @param $module     -- module:: Type varchar
     */
    function insertIntoFAQCommentTable($table_name, $module)
    {
        global $log;
        $log->info("in insertIntoFAQCommentTable  " . $table_name . "    module is  " . $module);
        global $adb;

        $current_time = $adb->formatDate(date('Y-m-d H:i:s'), true);

        if ($this->column_fields['comments'] != '') {
            $comment = $this->column_fields['comments'];
        } else {
            $comment = $_REQUEST['comments'];
        }

        if ($comment != '') {
            $params = ['', $this->id, from_html($comment), $current_time];
            $sql = "insert into vtiger_faqcomments values(?, ?, ?, ?)";
            $adb->pquery($sql, $params);
        }
    }

    /*
     * Function to get the relation tables for related modules
     * @param - $secmodule secondary module name
     * returns the array with table names and fieldnames storing relations between module and this module
     */
    function setRelationTables($secmodule)
    {
        $rel_tables = [
            "Documents" => ["vtiger_senotesrel" => ["crmid", "notesid"], "vtiger_faq" => "id"],
        ];

        return $rel_tables[$secmodule];
    }

    function clearSingletonSaveFields()
    {
        $this->column_fields['comments'] = '';
    }
}