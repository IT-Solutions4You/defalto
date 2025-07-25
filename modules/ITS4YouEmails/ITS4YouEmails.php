<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouEmails extends CRMEntity
{
    public string $moduleVersion = '1.0';
    public $id;
    public $column_fields;
    public $log;
    public $db;
    public string $moduleName = 'ITS4YouEmails';
    public string $parentName = 'Tools';
    public string $moduleLabel = 'Emails';
    public $table_name = 'its4you_emails';
    public $table_index = 'its4you_emails_id';
    public $entity_table = 'vtiger_crmentity';

    /**
     * @var array
     */
    public $customFieldTable = array(
        'its4you_emailscf',
        'its4you_emails_id',
    );

    /**
     * @var array
     */
    public $tab_name = array(
        'vtiger_crmentity',
        'its4you_emails',
        'its4you_emailscf',
    );

    /**
     * @var array
     */
    public $tab_name_index = array(
        'vtiger_crmentity' => 'crmid',
        'its4you_emails' => 'its4you_emails_id',
        'its4you_emailscf' => 'its4you_emails_id',
    );

    /**
     * @var array
     */
    public $list_fields = array(
        'Subject' => array('its4you_emails' => 'subject'),
        'Assigned To' => array('vtiger_crmentity' => 'assigned_user_id'),
        'Description' => array('vtiger_crmentity' => 'description'),
    );

    /**
     * @var array
     */
    public $list_fields_name = array(
        'Subject' => 'subject',
        'Assigned To' => 'assigned_user_id',
        'Description' => 'description',
    );
    public $isLineItemUpdate = true;


    public function __construct()
    {
        global $log;
        $this->column_fields = getColumnFields(get_class($this));
        $this->db = PearDatabase::getInstance();
        $this->log = $log;
    }

    public function save_module()
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
     * @throws Exception
     */
    public function vtlib_handler($moduleName, $eventType)
    {
        ITS4YouEmails_Install_Model::getInstance($eventType, $moduleName)->install();
    }

    /**
     * @param string $name
     * @throws AppException
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
     * @return bool
     */
    public function isRelationExists($recordId, $relationId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT crmid FROM vtiger_crmentityrel WHERE crmid = ? AND module = ? AND relcrmid = ? AND relmodule = ?',
            array($recordId, getSalesEntityType($recordId), $relationId, getSalesEntityType($relationId))
        );

        return $result && $adb->num_rows($result);
    }
}