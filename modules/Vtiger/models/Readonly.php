<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Vtiger_Readonly_Model extends Vtiger_Base_Model
{
    /**
     * @var PearDatabase
     */
    protected PearDatabase $db;

    /**
     * @param $module
     * @return self
     */
    public static function getInstance($module): self
    {
        $self = new self();
        $self->db = PearDatabase::getInstance();
        $self->set('module', $module);

        return $self;
    }

    /**
     * @return string
     */
    public function getReadonlyQuery(): string
    {
        return 'UPDATE vtiger_crmentity SET readonly=? WHERE crmid=? AND setype=?';
    }

    /**
     * @param string $module
     * @param int $record
     * @return bool
     * @throws Exception
     */
    public static function isButtonPermitted(string $module, int $record): bool
    {
        $isReadonly = self::isReadonly($module, $record);
        $currentUser = Users_Record_Model::getCurrentUserModel();

        return $isReadonly && $currentUser && $currentUser->isAdminUser();
    }

    /**
     * @param string $module
     * @param int $record
     * @param string $mode
     * @return bool
     * @throws Exception
     */
    public static function isPermitted(string $module, int $record, string $mode = 'EditView'): bool
    {
        if (in_array($mode, ['EditView', 'Delete'])) {
            return !self::isReadonly($module, $record);
        }

        return true;
    }

    /**
     * @param $module
     * @param $record
     * @return bool
     * @throws Exception
     */
    public static function isReadonly(string $module, int $record): bool
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT readonly FROM vtiger_crmentity WHERE setype=? AND crmid=? AND readonly=?',
            [$module, $record, 1]
        );

        return boolval($adb->num_rows($result));
    }

    /**
     * @return void
     */
    public function setReadonly(): void
    {
        $this->db->pquery($this->getReadonlyQuery(), [1, $this->get('record'), $this->get('module')]);
    }

    /**
     * @param $record
     * @return void
     */
    public function setRecord($record): void
    {
        $this->set('record', $record);
    }

    /**
     * @return void
     */
    public function unsetReadonly(): void
    {
        $this->db->pquery($this->getReadonlyQuery(), [0, $this->get('record'), $this->get('module')]);
    }

    /**
     * @return void
     */
    public static function updateTable(): void
    {
        $adb = PearDatabase::getInstance();

        if (!columnExists('readonly', 'vtiger_crmentity')) {
            $adb->pquery("ALTER TABLE vtiger_crmentity ADD readonly INT(11) NOT NULL DEFAULT '0' AFTER presence");
        }
    }

    /**
     * @param bool $register
     * @return void
     */
    public static function updateWorkflow(bool $register = true): void
    {
        require_once 'modules/com_vtiger_workflow/VTTaskManager.inc';

        $name = 'VTReadonly';
        $description = 'Make record readonly';
        $adb = PearDatabase::getInstance();
        $incDestination = sprintf('modules/com_vtiger_workflow/tasks/%s.inc', $name);
        $tplPath = sprintf('modules/Settings/Workflows/Tasks/%s.tpl', $name);

        if ($register) {
            $params = [
                'name' => $name,
                'label' => $description,
                'sourcemodule' => '',
                'classname' => $name,
                'classpath' => $incDestination,
                'templatepath' => $tplPath,
                'modules' => ['include' => [], 'exclude' => []],
            ];
            VTTaskType::registerTaskType($params);
        } else {
            $adb->pquery(
                'DELETE FROM com_vtiger_workflow_tasktypes WHERE tasktypename=?',
                array($name)
            );
        }
    }
}