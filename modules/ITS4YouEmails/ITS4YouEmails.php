<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ITS4YouEmails extends CRMEntity
{
    public string $moduleVersion = '1.0';
    public string $moduleName = 'ITS4YouEmails';
    public string $parentName = 'Tools';
    public string $moduleLabel = 'Emails';
    public $table_name = 'its4you_emails';
    public $table_index = 'its4you_emails_id';
    public $entity_table = 'vtiger_crmentity';
    public $def_basicsearch_col = 'subject';

    /**
     * @var array
     */
    public $customFieldTable = [
        'its4you_emailscf',
        'its4you_emails_id',
    ];

    /**
     * @var array
     */
    public $tab_name = [
        'vtiger_crmentity',
        'its4you_emails',
        'its4you_emailscf',
    ];

    /**
     * @var array
     */
    public $tab_name_index = [
        'vtiger_crmentity' => 'crmid',
        'its4you_emails'   => 'its4you_emails_id',
        'its4you_emailscf' => 'its4you_emails_id',
    ];

    /**
     * @var array
     */
    public $list_fields = [
        'Subject'     => ['its4you_emails' => 'subject'],
        'Assigned To' => ['vtiger_crmentity' => 'assigned_user_id'],
        'Description' => ['vtiger_crmentity' => 'description'],
    ];

    /**
     * @var array
     */
    public $list_fields_name = [
        'Subject'     => 'subject',
        'Assigned To' => 'assigned_user_id',
        'Description' => 'description',
    ];

    /**
     * @inheritDoc
     */
    public function save_module(string $module)
    {
        $this->createRelationsFormBlock();
    }

    public function createRelationsFormBlock()
    {
        $module = Vtiger_Module_Model::getInstance('ITS4YouEmails');
        $block = Vtiger_Block_Model::getInstance('LBL_RELATED_TO', $module);

        if ($block) {
            foreach ($block->getFields() as $field) {
                if (10 === intval($field->uitype)) {
                    $this->createRelationFromField($field->name);
                }
            }
        }
    }

    /**
     * @param string $moduleName
     * @param string $eventType
     *
     * @throws Exception
     */
    public function vtlib_handler($moduleName, $eventType)
    {
        ITS4YouEmails_Install_Model::getInstance($eventType, $moduleName)->install();
    }

    /**
     * @param string $name
     *
     * @throws Exception
     */
    public function createRelationFromField($name)
    {
        $module = Vtiger_Module_Model::getInstance($this->moduleName);
        $recordId = intval($this->column_fields[$name]);

        if (!empty($recordId)) {
            $parentModuleName = getSalesEntityType($recordId);
            $parentModule = Vtiger_Module_Model::getInstance($parentModuleName);

            if ($parentModule) {
                $relationModel = Vtiger_Relation_Model::getInstance($parentModule, $module);

                if ($relationModel && !$this->isRelationExists($recordId, $this->id)) {
                    $relationModel->addRelation($recordId, $this->id);
                }
            }
        }
    }

    /**
     * @param int $recordId
     * @param int $relationId
     *
     * @return bool
     */
    public function isRelationExists($recordId, $relationId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT crmid FROM vtiger_crmentityrel WHERE crmid = ? AND module = ? AND relcrmid = ? AND relmodule = ?',
            [$recordId, getSalesEntityType($recordId), $relationId, getSalesEntityType($relationId)]
        );

        return $result && $adb->num_rows($result);
    }
}