<?php
/*********************************************************************************
 * The content of this file is subject to the ITS4YouEmails license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

class ITS4YouEmails extends CRMEntity
{
    public $id;
    public $column_fields;
    public $log;
    public $db;
    public $moduleName = 'ITS4YouEmails';
    public $parentName = 'Tools';
    public $moduleLabel = 'Emails 4 You';
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
        'Assigned To' => array('vtiger_crmentity' => 'smownerid'),
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
    /**
     * @var array
     * [Module, RelatedModule, RelatedLabel, RelatedActions, RelatedFunction]
     */
    public $registerRelatedLists = array(
        ['ITS4YouEmails', 'Documents', 'Documents', 'ADD,SELECT', 'get_attachments'],
    );
    public $registerCustomLinks = array(
        ['ITS4YouEmails', 'HEADERSCRIPT', 'ITS4YouEmails_HS_Js', 'layouts/v7/modules/ITS4YouEmails/resources/ITS4YouEmails_HS.js'],
        ['ITS4YouEmails', 'HEADERSCRIPT', 'ITS4YouEmails_MassEdit_Js', 'layouts/v7/modules/ITS4YouEmails/resources/MassEdit.js'],
    );

    public function __construct()
    {
        global $log;
        $this->column_fields = getColumnFields(get_class($this));
        $this->db = PearDatabase::getInstance();
        $this->log = $log;
    }

    public function save_module()
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
        require_once 'include/utils/utils.php';
        require_once 'vtlib/Vtiger/Module.php';
        include_once 'modules/ModComments/ModComments.php';
        include_once 'modules/ModTracker/ModTracker.php';

        switch ($eventType) {
            case 'module.postinstall':
            case 'module.enabled':
            case 'module.postupdate':
                $this->addCustomLinks();
                break;
            case 'module.disabled':
            case 'module.preuninstall':
            case 'module.preupdate':
                $this->deleteCustomLinks();
                break;
        }
    }

    public function addCustomLinks()
    {
        $this->updateNumbering();
        $this->updateCustomLinks();
        $this->updateExtensions();
        $this->updateRelatedToModules();

        $this->retrieveRelatedList();
        $this->updateRelatedList();
        $this->updateRelatedListSequence();

		$this->updateTables();
        $this->updateFields();

        Settings_MenuEditor_Module_Model::addModuleToApp($this->moduleName, $this->parentName);
        ModComments::addWidgetTo([$this->moduleName]);
        ModTracker::enableTrackingForModule(getTabid($this->moduleName));
    }

    public function updateRelatedListSequence()
    {
        $currentEmailsId = getTabid($this->moduleName);
        $emailsId = getTabid('Emails');
        $tabResult = $this->db->pquery(
            'SELECT tabid FROM vtiger_relatedlists WHERE related_tabid=?',
            [$emailsId]
        );

        while ($tabRow = $this->db->fetchByAssoc($tabResult)) {
            $relatedInfoList = array();
            $tabId = $tabRow['tabid'];

            $relationResult = $this->db->pquery(
                'SELECT * FROM vtiger_relatedlists WHERE tabid=? ORDER BY sequence',
                [$tabId]
            );

            $currentRelationId = null;
            $emailsRelationId = null;
            $sequence = 0;

            while ($relationRow = $this->db->fetchByAssoc($relationResult)) {
                $relationId = $relationRow['relation_id'];
                $sequence++;
                $relationRow['sequence'] = $sequence;

                if ($emailsId == $relationRow['related_tabid']) {
                    $emailsRelationId = $relationId;
                    $sequence++;
                } elseif ($currentEmailsId == $relationRow['related_tabid']) {
                    $currentRelationId = $relationId;
                }

                $relatedInfoList[$relationId] = $relationRow;
            }

            if ($emailsRelationId && $currentRelationId) {
                $relatedInfoList[$currentRelationId]['sequence'] = $relatedInfoList[$emailsRelationId]['sequence'] + 1;

                Vtiger_Relation_Model::updateRelationSequenceAndPresence($relatedInfoList, $tabId);
            }
        }
    }

    public function updateFields()
    {
        $module = Vtiger_Module_Model::getInstance($this->moduleName);
        $field = Vtiger_Field_Model::getInstance('related_to', $module);
        $block = Vtiger_Block_Model::getInstance('LBL_RELATED_TO', $module);

        if ($field && $block && 'Related To' === $field->get('label')) {
            $this->db->pquery('UPDATE vtiger_field SET fieldlabel=?, block=? WHERE fieldid=?', ['Related Record', $block->get('id'), $field->get('id')]);
        }

        $this->db->pquery('UPDATE vtiger_field SET headerfield=? WHERE tabid=? AND fieldname IN (?,?)', [1, getTabid($this->moduleName), 'email_flag', 'createdtime']);
        $this->db->pquery('UPDATE vtiger_field SET typeofdata=? WHERE tabid=? AND fieldname IN (?,?)', ['I~O', getTabid($this->moduleName), 'access_count', 'click_count']);
    }

    public function updateTables()
	{
		$this->db->query('ALTER TABLE `its4you_emails` CHANGE `subject` `subject` VARCHAR(255)');
		$this->db->query('ALTER TABLE `its4you_emails` CHANGE `body` `body` LONGTEXT');
	}

    public function updateNumbering()
    {
        $this->setModuleSeqNumber('configure', $this->moduleName, 'MAIL', '0001');
        $this->updateMissingSeqNumber($this->moduleName);
    }

    /**
     * @param bool $register
     */
    public function updateCustomLinks($register = true)
    {
        foreach ($this->registerCustomLinks as $customLink) {
            list($moduleName, $type, $label, $link, $icon, $sequence, $handler) = array_pad($customLink, 7, null);
            $module = Vtiger_Module::getInstance($moduleName);
            $url = str_replace('$LAYOUT$', Vtiger_Viewer::getDefaultLayoutName(), $link);

            if ($module) {
                $module->deleteLink($type, $label);

                if ($register) {
                    $module->addLink($type, $label, $url, $icon, $sequence, $handler);
                }
            }
        }
    }

    /**
     * @param bool $register
     */
    public function updateRelatedList($register = true)
    {
        foreach ($this->registerRelatedLists as $relatedList) {
            $module = Vtiger_Module::getInstance($relatedList[0]);
            $relatedModule = Vtiger_Module::getInstance($relatedList[1]);

            if ($module && $relatedModule) {
                $relatedLabel = isset($relatedList[2]) ? $relatedList[2] : $relatedModule->name;
                $relatedActions = isset($relatedList[3]) ? $relatedList[3] : '';
                $relatedFunction = isset($relatedList[4]) ? $relatedList[4] : 'get_related_list';
                $field = isset($relatedList[5]) ? Vtiger_Field_Model::getInstance($relatedList[5], $relatedModule) : '';
                $fieldId = $field ? $field->getId() : '';

                $module->unsetRelatedList($relatedModule, $relatedLabel);
                $module->unsetRelatedList($relatedModule, $relatedLabel, $relatedFunction);

                if ($register) {
                    $module->setRelatedList($relatedModule, $relatedLabel, $relatedActions, $relatedFunction, $fieldId);
                }
            }
        }
    }

    public function updateExtensions()
    {
        $this->updateFiles('PHPMailer');
    }

    /**
     * @param $fileName
     */
    public function updateFiles($fileName)
    {
        $srcZip = 'https://www.its4you.sk/images/extensions/' . $this->moduleName . '/src/' . $fileName . '.zip';
        $trgZip = 'modules/ITS4YouLibrary/' . $fileName . '.zip';
        
        mkdir(getcwd() . '/modules/ITS4YouLibrary');

        if (copy($srcZip, $trgZip)) {
            if (is_file($trgZip)) {
                require_once('vtlib/thirdparty/dUnzip2.inc.php');

                $unzip = new dUnzip2($trgZip);
                $unzip->unzipAll(getcwd() . '/modules/ITS4YouLibrary/');
                $unzip->close();

                unlink($trgZip);
            }
        }
    }

    public function updateRelatedToModules()
    {
        $supportedModules = ITS4YouEmails_Integration_Model::getSupportedModules();
        $moduleInstance = Vtiger_Module_Model::getInstance($this->moduleName);
        $fieldInstance = Vtiger_Field_Model::getInstance('related_to', $moduleInstance);

        if ($fieldInstance) {
            foreach ($supportedModules as $supportedModule) {
                $fieldInstance->setRelatedModules([$supportedModule->getName()]);
            }
        }
    }

    public function deleteCustomLinks()
    {
        $this->updateCustomLinks(false);

        $this->retrieveRelatedList();
        $this->updateRelatedList(false);

        ModComments::removeWidgetFrom([$this->moduleName]);
        ModTracker::disableTrackingForModule(getTabid($this->moduleName));
    }

    public function retrieveRelatedList()
    {
        $supportedModules = ITS4YouEmails_Integration_Model::getSupportedModules();

        foreach ($supportedModules as $supportedModule) {
            $supportedModuleName = $supportedModule->getName();

            $this->registerRelatedLists[] = [$supportedModuleName, 'ITS4YouEmails', 'ITS4YouEmails', 'SELECT', 'get_related_list'];
        }
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

                if ($relationModel) {
                    $relationModel->addRelation($recordId, $this->id);
                }
            }
        }
    }
}