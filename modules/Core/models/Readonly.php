<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Readonly_Model extends Vtiger_Base_Model
{
    /**
     * @var PearDatabase
     */
    protected PearDatabase $db;

    /**
     * @param $module
     *
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
     * @param int    $record
     *
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
     * @param string             $module
     * @param int                $record
     * @param Users_Record_Model $currentUser
     *
     * @return bool
     * @throws Exception
     */
    public static function isMakeReadonlyPermitted(
        string $module,
        int $record,
        Users_Record_Model $currentUser
    ): bool
    {
        if ($currentUser->isAdminUser()) {
            return true;
        }

        if (!$record || getSalesEntityType($record) !== $module) {
            return false;
        }

        if (!Users_Privileges_Model::isPermitted($module, 'EditView', $record)) {
            return false;
        }

        $recordModel = Vtiger_Record_Model::getInstanceById($record, $module);

        return (int)$currentUser->getId() === (int)$recordModel->get('assigned_user_id');
    }

    /**
     * @param string $module
     * @param int    $record
     * @param string $mode
     *
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
     *
     * @return bool
     * @throws Exception
     */
    public static function isReadonly(string $module, int $record): bool
    {
        if (!columnExists('readonly', 'vtiger_crmentity')) {
            return false;
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT readonly FROM vtiger_crmentity WHERE setype=? AND crmid=? AND readonly=?',
            [$module, $record, 1]
        );

        return (bool)$adb->num_rows($result);
    }

    /**
     * @return void
     */
    public function setReadonly(): void
    {
        $this->db->pquery($this->getReadonlyQuery(), [1, $this->get('record'), $this->get('module')]);
    }

    /**
     * Revalidates a manual lock request at the write boundary and binds a non-administrator update to the current
     * direct owner.
     *
     * @param Users_Record_Model $currentUser
     *
     * @return bool
     * @throws Exception
     */
    public function updateReadonlyForUser(Users_Record_Model $currentUser): bool
    {
        $module = (string)$this->get('module');
        $record = (int)$this->get('record');

        if (!self::isMakeReadonlyPermitted($module, $record, $currentUser)) {
            return false;
        }

        if ($currentUser->isAdminUser()) {
            $this->setReadonly();

            return true;
        }

        $result = $this->db->pquery(
            $this->getReadonlyQuery() . ' AND smownerid=?',
            [1, $record, $module, $currentUser->getId()]
        );

        return $this->db->getAffectedRowCount($result) === 1;
    }

    /**
     * @param $record
     *
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
     *
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
                'name'         => $name,
                'label'        => $description,
                'sourcemodule' => '',
                'classname'    => $name,
                'classpath'    => $incDestination,
                'templatepath' => $tplPath,
                'modules'      => ['include' => [], 'exclude' => []],
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
