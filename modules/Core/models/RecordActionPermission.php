<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Action permissions of single records (df_record_action_permission): record + user + action → permitted or denied.
 *
 * A rule allows or denies one action on one record for everyone, a user, a group or a role. Rules only narrow what
 * the profile and sharing already allow: Users_Privileges_Model::getPermission() checks them before the profile, and
 * an allow rule can only cancel a deny rule, never grant a profile right. The most specific matching level decides
 * (user > group > role > all); on the same level a deny wins. A record without rules keeps the standard permissions.
 *
 * Rules are written by sources (workflow tasks now, approvals and edit locks later) and removed by the same source.
 * Rules of a record are loaded once per request; lists call preload() with the ids of the page.
 */
class Core_RecordActionPermission_Model extends Core_DatabaseData_Model
{
    public const ACTION_EDIT = 'EditView';
    public const ACTION_DELETE = 'Delete';
    public const ACTION_DUPLICATE = 'Duplicate';
    public const ACTION_CONVERT = 'Convert';
    public const ACTION_FIELD_EDIT = 'FieldEdit';
    public const ACTION_STATUS_CHANGE = 'StatusChange';
    public const ACTION_PDF_EXPORT = 'PdfExport';
    public const ACTION_EMAIL_SEND = 'EmailSend';

    /** Separator of the field and the value in the action value of StatusChange rules ("quotestage=Accepted") */
    public const STATUS_VALUE_SEPARATOR = '=';

    public const EFFECT_ALLOW = 'allow';
    public const EFFECT_DENY = 'deny';

    public const SCOPE_ALL = 'all';
    public const SCOPE_USER = 'user';
    public const SCOPE_GROUP = 'group';
    public const SCOPE_ROLE = 'role';

    public const SOURCE_WORKFLOW = 'workflow';
    public const SOURCE_MANUAL = 'manual';

    /** Permission actions of Users_Privileges_Model checks => record action (MassSave checks "Save") */
    public const PERMISSION_ACTIONS = [
        'EditView' => self::ACTION_EDIT,
        'Save'     => self::ACTION_EDIT,
        'Delete'   => self::ACTION_DELETE,
    ];

    /** Modules whose record ids are not crmids */
    public const NON_ENTITY_MODULES = ['Users'];

    /** Scope levels from the most specific one; the first level with a matching rule decides */
    public const SCOPE_PRIORITY = [self::SCOPE_USER, self::SCOPE_GROUP, self::SCOPE_ROLE, self::SCOPE_ALL];

    protected string $table = 'df_record_action_permission';
    protected string $tableId = 'id';

    /** Rules of records loaded in this request [crmid => rows] */
    protected static array $recordRules = [];

    /** Scopes of users [userid => ['user' => [...], 'group' => [...], 'role' => [...]]] */
    protected static array $userScopes = [];

    protected static ?bool $isInstalled = null;

    /** Sources shown in the detail banner [source => bool] */
    protected static array $bannerSources = [];

    /** Records that have rules [crmid => true]; loaded once per request so records without rules cost no query */
    protected static ?array $ruleRecordIds = null;

    protected static ?self $instance = null;

    /**
     * Returns the shared instance used by permission checks.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Returns the table accessor of df_record_action_permission.
     *
     * @return self configured clone for CRUD of the rules
     */
    public function getRecordActionPermissionTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    /**
     * Creates the table and registers the workflow tasks; idempotent. Listed in Core_CreateTables_Config, so the
     * core install and the system update run it.
     *
     * @return void
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->getRecordActionPermissionTable()
            ->createTable()
            ->createColumn('crmid', 'INT(19) NOT NULL')
            ->createColumn('module', 'VARCHAR(100) NOT NULL')
            ->createColumn('action', 'VARCHAR(50) NOT NULL')
            ->createColumn('action_value', "VARCHAR(255) NOT NULL DEFAULT ''")
            ->createColumn('effect', "VARCHAR(5) NOT NULL DEFAULT 'deny'")
            ->createColumn('scope_type', "VARCHAR(10) NOT NULL DEFAULT 'all'")
            ->createColumn('scope_id', "VARCHAR(255) NOT NULL DEFAULT ''")
            ->createColumn('source', 'VARCHAR(50) NOT NULL')
            ->createColumn('source_ref', "VARCHAR(100) NOT NULL DEFAULT ''")
            ->createColumn('reason', "VARCHAR(255) NOT NULL DEFAULT ''")
            ->createColumn('created_by', 'INT(19) NOT NULL DEFAULT 0')
            ->createColumn('created_time', 'DATETIME NULL')
            ->createColumn('expires_time', 'DATETIME NULL')
            ->createKey('KEY IF NOT EXISTS `crmid_idx` (`crmid`)')
            ->createKey('KEY IF NOT EXISTS `crmid_action_idx` (`crmid`, `action`)')
            ->createKey('KEY IF NOT EXISTS `source_idx` (`source`, `source_ref`)');

        self::$isInstalled = true;

        Core_RecordActionPermissionTask_Helper::registerTaskTypes();
    }

    /**
     * Checks whether the rules table exists; until the system update creates it, every action is permitted.
     *
     * @return bool
     */
    public function isInstalled(): bool
    {
        if (null === self::$isInstalled) {
            require_once 'vtlib/Vtiger/Utils.php';

            self::$isInstalled = (bool)Vtiger_Utils::CheckTable($this->table);
        }

        return self::$isInstalled;
    }

    /**
     * Returns the records that have rules, loaded once per request; permission checks of other records (related
     * lists, popups, widgets) then cost no query. Rules are few (records waiting for approval and similar).
     *
     * @return array crmid => true
     * @throws Exception
     */
    public function getRuleRecordIds(): array
    {
        if (null === self::$ruleRecordIds) {
            self::$ruleRecordIds = [];

            if ($this->isInstalled()) {
                $this->retrieveDB();
                $db = $this->getDB();
                $result = $db->pquery('SELECT DISTINCT crmid FROM ' . $this->table, []);

                while ($row = $db->fetchByAssoc($result)) {
                    self::$ruleRecordIds[(int)$row['crmid']] = true;
                }
            }
        }

        return self::$ruleRecordIds;
    }

    /**
     * Returns the actions that can be restricted, with their language keys (module Core).
     *
     * @return array action => label key
     */
    public static function getActions(): array
    {
        return [
            self::ACTION_EDIT          => 'LBL_RECORD_ACTION_EDIT',
            self::ACTION_DELETE        => 'LBL_RECORD_ACTION_DELETE',
            self::ACTION_DUPLICATE     => 'LBL_RECORD_ACTION_DUPLICATE',
            self::ACTION_CONVERT       => 'LBL_RECORD_ACTION_CONVERT',
            self::ACTION_FIELD_EDIT    => 'LBL_RECORD_ACTION_FIELD_EDIT',
            self::ACTION_STATUS_CHANGE => 'LBL_RECORD_ACTION_STATUS_CHANGE',
            self::ACTION_PDF_EXPORT    => 'LBL_RECORD_ACTION_PDF_EXPORT',
            self::ACTION_EMAIL_SEND    => 'LBL_RECORD_ACTION_EMAIL_SEND',
        ];
    }

    /**
     * Loads the rules of several records with one query, so lists do not query the table per row.
     *
     * @param array $recordIds
     *
     * @return void
     * @throws Exception
     */
    public function preload(array $recordIds): void
    {
        $recordIds = array_values(array_diff(self::getIds($recordIds), array_keys(self::$recordRules)));

        if (empty($recordIds)) {
            return;
        }

        foreach ($recordIds as $recordId) {
            self::$recordRules[$recordId] = [];
        }

        $recordIds = array_values(array_intersect($recordIds, array_keys($this->getRuleRecordIds())));

        if (empty($recordIds)) {
            return;
        }

        $this->retrieveDB();
        $db = $this->getDB();
        $result = $db->pquery(
            'SELECT * FROM ' . $this->table . ' WHERE crmid IN (' . generateQuestionMarks($recordIds) . ')',
            $recordIds
        );

        while ($row = $db->fetchByAssoc($result, -1, false)) {
            self::$recordRules[(int)$row['crmid']][] = $row;
        }
    }

    /**
     * Returns all stored rules of a record, including expired ones.
     *
     * @param int $recordId
     *
     * @return array rows of df_record_action_permission
     * @throws Exception
     */
    public function getRecordRules(int $recordId): array
    {
        if (!$recordId) {
            return [];
        }

        $this->preload([$recordId]);

        return self::$recordRules[$recordId];
    }

    /**
     * Checks a permission action of Users_Privileges_Model::getPermission(); actions without record rules pass.
     *
     * @param string     $module     module of the permission check
     * @param string     $actionName permission action (EditView, Delete, ...)
     * @param mixed      $recordId   record of the check; empty (or false/null) for module-level checks
     *
     * @return bool false when a rule of the record denies the action to the current user
     * @throws Exception
     */
    public static function isPermissionActionPermitted(string $module, string $actionName, mixed $recordId): bool
    {
        $recordId = is_numeric($recordId) ? (int)$recordId : 0;
        $action = self::PERMISSION_ACTIONS[$actionName] ?? '';

        if (!$recordId || '' === $action || in_array($module, self::NON_ENTITY_MODULES, true)) {
            return true;
        }

        // Rules belong to the crmid, not to the module parameter of the check (Save may pass source_module)
        return self::getInstance()->isActionPermitted($recordId, $action);
    }

    /**
     * Returns the reason of a record rule that denies a permission action, so a denied request shows why.
     *
     * @param string $module     module of the permission check
     * @param string $actionName permission action (EditView, Delete, ...)
     * @param mixed  $recordId   record of the check
     *
     * @return string|null translated reason, null when no record rule denies the action
     * @throws Exception
     */
    public static function getPermissionDenyReason(string $module, string $actionName, mixed $recordId): ?string
    {
        $recordId = is_numeric($recordId) ? (int)$recordId : 0;
        $action = self::PERMISSION_ACTIONS[$actionName] ?? '';

        if (!$recordId || '' === $action || in_array($module, self::NON_ENTITY_MODULES, true)) {
            return null;
        }

        return self::getInstance()->getDenyReason($recordId, $action);
    }

    /**
     * Splits records into the ones a user may perform an action on and the skipped ones, for mass actions,
     * PDF export and sending emails. Loads the rules of all records with one query.
     *
     * @param array    $recordIds
     * @param string   $action
     * @param int|null $userId null for the current user
     *
     * @return array ['permitted' => [ids], 'skipped' => [id => translated reason]]
     * @throws Exception
     */
    public function getPermittedRecords(array $recordIds, string $action, ?int $userId = null): array
    {
        $recordIds = self::getIds($recordIds);
        $records = ['permitted' => [], 'skipped' => []];

        $this->preload($recordIds);

        foreach ($recordIds as $recordId) {
            $reason = $this->getDenyReason($recordId, $action, '', $userId);

            if (null === $reason) {
                $records['permitted'][] = $recordId;
            } else {
                $records['skipped'][$recordId] = $reason;
            }
        }

        return $records;
    }

    /**
     * Returns the message about records skipped by a mass action, with their distinct reasons.
     *
     * @param array $skipped record id => translated reason
     *
     * @return string empty when nothing was skipped
     */
    public function getSkippedMessage(array $skipped): string
    {
        if (empty($skipped)) {
            return '';
        }

        return sprintf(
            vtranslate('LBL_RECORD_ACTION_SKIPPED', 'Core'),
            count($skipped),
            implode('; ', array_unique(array_map('strval', $skipped)))
        );
    }

    /**
     * Returns why the changed values of a record model may not be saved: a changed field locked by a FieldEdit
     * rule or a new value denied by a StatusChange rule. Compares the model with the record loaded again from the
     * database (the model shares its value map with its entity), so unchanged fields always pass.
     *
     * @param Vtiger_Record_Model $recordModel existing record with the new values set
     * @param array               $fieldNames  submitted fields to check; empty for every field of the module
     * @param int|null            $userId      null for the current user
     *
     * @return string|null translated reason, null when the changes are permitted
     * @throws Exception
     */
    public function getChangeDenyReason(Vtiger_Record_Model $recordModel, array $fieldNames = [], ?int $userId = null): ?string
    {
        $recordId = (int)$recordModel->getId();

        if (!$recordId || !$this->hasValueRules($recordId)) {
            return null;
        }

        $currentRecordModel = Vtiger_Record_Model::getInstanceById($recordId, $recordModel->getModuleName());
        $values = [];
        $currentValues = [];
        $fieldNames = $fieldNames ?: array_keys($recordModel->getModule()->getFields());

        // has() cannot be used: the value map of a loaded record is a TrackableObject, not an array
        foreach ($fieldNames as $fieldName) {
            if (null !== $recordModel->get($fieldName)) {
                $values[$fieldName] = $recordModel->get($fieldName);
                $currentValues[$fieldName] = $currentRecordModel->get($fieldName);
            }
        }

        return $this->getValuesDenyReason($recordId, $values, $currentValues, $userId);
    }

    /**
     * Returns why the values of a webservice update or revise may not be saved (FieldEdit and StatusChange rules).
     * Reference values ("12x345") are compared by their record id.
     *
     * @param int    $recordId
     * @param string $moduleName webservice entity of the element; ids of other entities (Users) are not crmids
     * @param array  $element    webservice element: field name => value
     *
     * @return string|null translated reason, null when the changes are permitted
     * @throws Exception
     */
    public function getWebserviceDenyReason(int $recordId, string $moduleName, array $element): ?string
    {
        if (!$recordId || getSalesEntityType($recordId) !== $moduleName || !$this->hasValueRules($recordId)) {
            return null;
        }

        $currentRecordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $newValues = [];
        $currentValues = [];

        foreach ($element as $fieldName => $value) {
            // has() cannot be used: the value map of a loaded record is a TrackableObject, not an array
            if ('id' === $fieldName || !$currentRecordModel->getModule()->getField($fieldName)) {
                continue;
            }

            $currentValues[$fieldName] = $currentRecordModel->get($fieldName);

            if (is_string($value) && preg_match('/^\d+x(\d+)$/', $value, $matches)) {
                $value = $matches[1];
            }

            $newValues[$fieldName] = $value;
        }

        return $this->getValuesDenyReason($recordId, $newValues, $currentValues);
    }

    /**
     * Returns why new field values of a record may not be saved (FieldEdit and StatusChange rules); fields whose
     * value does not change always pass.
     *
     * @param int      $recordId
     * @param array    $newValues     field name => new value in database format
     * @param array    $currentValues field name => current value in database format
     * @param int|null $userId        null for the current user
     *
     * @return string|null translated reason, null when the changes are permitted
     * @throws Exception
     */
    public function getValuesDenyReason(int $recordId, array $newValues, array $currentValues, ?int $userId = null): ?string
    {
        if (!$this->hasValueRules($recordId)) {
            return null;
        }

        foreach ($newValues as $fieldName => $newValue) {
            $fieldName = (string)$fieldName;

            if (!array_key_exists($fieldName, $currentValues)
                || $this->isSameValue($newValue, $currentValues[$fieldName])) {
                continue;
            }

            $reason = $this->getDenyReason($recordId, self::ACTION_FIELD_EDIT, $fieldName, $userId)
                ?? $this->getDenyReason($recordId, self::ACTION_STATUS_CHANGE, self::getStatusValue($fieldName, $newValue), $userId);

            if (null !== $reason) {
                return $reason;
            }
        }

        return null;
    }

    /**
     * Marks the fields of a record structure locked by FieldEdit rules: they are not inline editable and the edit
     * view shows them read-only with the reason. The field models of a structure are not always the instances of
     * Vtiger_Module_Model::getFields() (field cache), so the structure handed to the template is marked.
     *
     * @param array $structure block label => [field name => Vtiger_Field_Model]
     * @param int   $recordId
     *
     * @return array the same structure
     * @throws Exception
     */
    public function applyLockedFields(array $structure, int $recordId): array
    {
        if (!$recordId || !$this->hasActionRules($recordId, self::ACTION_FIELD_EDIT)) {
            return $structure;
        }

        foreach ($structure as $fieldModels) {
            foreach ((array)$fieldModels as $fieldName => $fieldModel) {
                $reason = $this->getDenyReason($recordId, self::ACTION_FIELD_EDIT, (string)$fieldName);

                if (null !== $reason && $fieldModel instanceof Vtiger_Field_Model) {
                    $fieldModel->set('record_action_locked', $reason);
                    $fieldModel->ajaxeditable = 0;
                }
            }
        }

        return $structure;
    }

    /**
     * Builds the action value of a StatusChange rule from a field and its value.
     *
     * @param string $fieldName
     * @param mixed  $value
     *
     * @return string
     */
    public static function getStatusValue(string $fieldName, mixed $value): string
    {
        $value = is_array($value) ? implode(' |##| ', $value) : (string)$value;

        // Picklist values may be stored HTML-encoded; rules and checks use the decoded value
        return $fieldName . self::STATUS_VALUE_SEPARATOR . trim(decode_html($value));
    }

    /**
     * Checks whether a user may perform an action on a record according to the record rules.
     *
     * @param int         $recordId
     * @param string      $action  one of getActions() or an action registered by a source
     * @param string      $value   action value (field or status value); rules with an empty value match every value
     * @param int|null    $userId  null for the current user
     *
     * @return bool
     * @throws Exception
     */
    public function isActionPermitted(int $recordId, string $action, string $value = '', ?int $userId = null): bool
    {
        $rule = $this->getDecisiveRule($recordId, $action, $value, $userId);

        return !$rule || self::EFFECT_DENY !== $rule['effect'];
    }

    /**
     * Returns why a record may not be created as a copy: duplicating a record or converting an inventory record
     * (quote to sales order, invoice, ...) denied by the rules of the source record. Used by the edit view and
     * by saving a new record, so a copy cannot be created by posting the form directly.
     *
     * @param string $moduleName      module of the new record
     * @param int    $duplicateId     record being duplicated; 0 when not duplicating
     * @param array  $sourceRecordIds records the new record is created from (sourceRecord, quote_id, salesorder_id)
     *
     * @return string|null translated reason, null when the copy is permitted
     * @throws Exception
     */
    public function getCopyDenyReason(string $moduleName, int $duplicateId, array $sourceRecordIds): ?string
    {
        if (in_array($moduleName, self::NON_ENTITY_MODULES, true)) {
            return null;
        }

        $reason = $duplicateId ? $this->getDenyReason($duplicateId, self::ACTION_DUPLICATE) : null;

        if (null !== $reason) {
            return $reason;
        }

        foreach (self::getIds($sourceRecordIds) as $sourceRecordId) {
            $reason = $this->isInventoryConversion($moduleName, $sourceRecordId)
                ? $this->getDenyReason($sourceRecordId, self::ACTION_CONVERT)
                : null;

            if (null !== $reason) {
                return $reason;
            }
        }

        return null;
    }

    /**
     * Checks whether a new record of a module created from a source record is a conversion between inventory records.
     *
     * @param string $moduleName     module of the new record
     * @param int    $sourceRecordId
     *
     * @return bool
     */
    public function isInventoryConversion(string $moduleName, int $sourceRecordId): bool
    {
        $sourceModule = $sourceRecordId ? (string)getSalesEntityType($sourceRecordId) : '';

        return '' !== $sourceModule
            && InventoryItem_Utils_Helper::isInventoryModule($moduleName)
            && InventoryItem_Utils_Helper::isInventoryModule($sourceModule);
    }

    /**
     * Returns the translated reason of the deny rule that blocks an action, for banners and skipped mass actions.
     *
     * @param int         $recordId
     * @param string      $action
     * @param string      $value
     * @param int|null    $userId null for the current user
     *
     * @return string|null null when the action is permitted
     * @throws Exception
     */
    public function getDenyReason(int $recordId, string $action, string $value = '', ?int $userId = null): ?string
    {
        $rule = $this->getDecisiveRule($recordId, $action, $value, $userId);

        if (!$rule || self::EFFECT_DENY !== $rule['effect']) {
            return null;
        }

        return $this->getRuleReason($rule);
    }

    /**
     * Returns the actions denied to a user on a record, with their reasons, for the detail banner; field and status
     * rules are listed per field or value, sources with their own panel (isSourceInBanner()) are left out.
     *
     * @param int      $recordId
     * @param int|null $userId null for the current user
     *
     * @return array "action/value" => ['label' => translated action, 'reason' => translated reason]
     * @throws Exception
     */
    public function getDeniedActions(int $recordId, ?int $userId = null): array
    {
        $denied = [];

        foreach ($this->getRecordRules($recordId) as $rule) {
            $key = $rule['action'] . '/' . $rule['action_value'];

            if (isset($denied[$key]) || self::EFFECT_DENY !== $rule['effect'] || $this->isRuleExpired($rule) || !$this->isSourceInBanner((string)$rule['source'])) {
                continue;
            }

            $reason = $this->getDenyReason($recordId, $rule['action'], (string)$rule['action_value'], $userId);

            if (null !== $reason) {
                $denied[$key] = [
                    'label'  => $this->getActionLabel($rule),
                    'reason' => $reason,
                ];
            }
        }

        return $denied;
    }

    /**
     * Returns the active rules of a record (not expired) with translated values, for the detail banner; sources with
     * their own panel (isSourceInBanner()) are left out.
     *
     * @param int $recordId
     *
     * @return array rows extended with action_label, scope_label and reason_label
     * @throws Exception
     */
    public function getActiveRules(int $recordId): array
    {
        $rules = [];

        foreach ($this->getRecordRules($recordId) as $rule) {
            if ($this->isRuleExpired($rule) || !$this->isSourceInBanner((string)$rule['source'])) {
                continue;
            }

            $rule['action_label'] = $this->getActionLabel($rule);
            $rule['scope_label'] = $this->getScopeLabel($rule);
            $rule['reason_label'] = $this->getRuleReason($rule);
            $rule['source_label'] = $this->getSourceLabel($rule);
            $rules[] = $rule;
        }

        return $rules;
    }

    /**
     * Returns whether the detail banner lists the rules of a source. A module that explains its restrictions in its own
     * panel of the detail view (its module model returns true from hasOwnRestrictionPanel(), e.g. Approvals) is left out.
     *
     * @param string $source
     *
     * @return bool
     */
    public function isSourceInBanner(string $source): bool
    {
        if (!isset(self::$bannerSources[$source])) {
            $moduleModel = '' !== $source && vtlib_isModuleActive($source) ? Vtiger_Module_Model::getInstance($source) : null;

            self::$bannerSources[$source] = !($moduleModel && method_exists($moduleModel, 'hasOwnRestrictionPanel') && $moduleModel->hasOwnRestrictionPanel());
        }

        return self::$bannerSources[$source];
    }

    /**
     * Saves a new rule for a record.
     *
     * @param int         $recordId
     * @param string      $action
     * @param string      $effect      EFFECT_ALLOW or EFFECT_DENY
     * @param string      $scopeType   SCOPE_* constant
     * @param string      $scopeId     user, group or role id; empty for SCOPE_ALL
     * @param string      $source      source that owns the rule (SOURCE_* or a module name)
     * @param string      $sourceRef   reference within the source (task id, request id, ...)
     * @param string      $reason      language key or text shown to users
     * @param string      $actionValue field or status value; empty for every value
     * @param string|null $expiresTime database datetime (server time zone) or null for no expiry
     *
     * @return int id of the rule
     * @throws Exception
     */
    public function saveRule(
        int $recordId,
        string $action,
        string $effect,
        string $scopeType,
        string $scopeId,
        string $source,
        string $sourceRef = '',
        string $reason = '',
        string $actionValue = '',
        ?string $expiresTime = null
    ): int {
        $module = $recordId ? (string)getSalesEntityType($recordId) : '';

        if (!$module || !$this->isInstalled()) {
            return 0;
        }

        $this->validateRule($action, $effect, $scopeType, $scopeId);

        $ruleId = (int)$this->getRecordActionPermissionTable()->insertData([
            'crmid'        => $recordId,
            'module'       => $module,
            'action'       => $action,
            'action_value' => $actionValue,
            'effect'       => $effect,
            'scope_type'   => $scopeType,
            'scope_id'     => self::SCOPE_ALL === $scopeType ? '' : $scopeId,
            'source'       => $source,
            'source_ref'   => $sourceRef,
            'reason'       => $reason,
            'created_by'   => (int)Users_Record_Model::getCurrentUserModel()->getId(),
            'created_time' => date('Y-m-d H:i:s'),
            'expires_time' => $expiresTime,
        ]);
        if (null !== self::$ruleRecordIds) {
            self::$ruleRecordIds[$recordId] = true;
        }

        $this->clearCache($recordId);

        return $ruleId;
    }

    /**
     * Deletes the rules a source wrote for a record.
     *
     * @param int    $recordId
     * @param string $source
     * @param string $sourceRef empty for every rule of the source
     * @param array  $actions   empty for every action
     *
     * @return void
     * @throws Exception
     */
    public function deleteBySource(int $recordId, string $source, string $sourceRef = '', array $actions = []): void
    {
        if (!$recordId || !$this->isInstalled()) {
            return;
        }

        $search = ['crmid' => $recordId, 'source' => $source];

        if ('' !== $sourceRef) {
            $search['source_ref'] = $sourceRef;
        }

        $table = $this->getRecordActionPermissionTable();

        if (empty($actions)) {
            $table->deleteData($search);
        }

        foreach ($actions as $action) {
            $table->deleteData(array_merge($search, ['action' => (string)$action]));
        }

        $this->clearCache($recordId);
    }

    /**
     * Returns the records of a module that have rules of a source (for example to rewrite them after a settings change).
     *
     * @param string $moduleName
     * @param string $source
     *
     * @return int[]
     * @throws Exception
     */
    public function getSourceRecordIds(string $moduleName, string $source): array
    {
        if (!$moduleName || !$source || !$this->isInstalled()) {
            return [];
        }

        $this->retrieveDB();
        $db = $this->getDB();
        $result = $db->pquery(
            'SELECT DISTINCT crmid FROM ' . $this->table . ' WHERE module = ? AND source = ?',
            [$moduleName, $source]
        );
        $recordIds = [];

        while ($row = $db->fetchByAssoc($result)) {
            $recordIds[] = (int)$row['crmid'];
        }

        return $recordIds;
    }

    /**
     * Deletes the rules a source wrote for every record of a module (for example when the source is switched off).
     *
     * @param string $moduleName
     * @param string $source
     *
     * @return void
     * @throws Exception
     */
    public function deleteModuleSourceRules(string $moduleName, string $source): void
    {
        if (!$moduleName || !$source || !$this->isInstalled()) {
            return;
        }

        $this->getRecordActionPermissionTable()->deleteData(['module' => $moduleName, 'source' => $source]);
        self::$recordRules = [];
        self::$ruleRecordIds = null;
    }

    /**
     * Deletes one rule of a record (admin removal from the detail banner).
     *
     * @param int $recordId
     * @param int $ruleId
     *
     * @return void
     * @throws Exception
     */
    public function deleteRule(int $recordId, int $ruleId): void
    {
        if (!$recordId || !$ruleId || !$this->isInstalled()) {
            return;
        }

        $this->getRecordActionPermissionTable()->deleteData(['crmid' => $recordId, $this->tableId => $ruleId]);
        $this->clearCache($recordId);
    }

    /**
     * Deletes every rule of a record, whatever its source (admin removal of all restrictions from the detail banner).
     *
     * @param int $recordId
     *
     * @return void
     * @throws Exception
     */
    public function deleteRecordRules(int $recordId): void
    {
        if (!$recordId || !$this->isInstalled()) {
            return;
        }

        $this->getRecordActionPermissionTable()->deleteData(['crmid' => $recordId]);
        $this->clearCache($recordId);
    }

    /**
     * Forgets the loaded rules of a record so the next check reads the table again.
     *
     * @param int $recordId
     *
     * @return void
     */
    public function clearCache(int $recordId): void
    {
        unset(self::$recordRules[$recordId]);
    }

    /**
     * Returns the translated action of a rule; field and status rules name the field and the value.
     *
     * @param array $rule
     *
     * @return string
     */
    protected function getActionLabel(array $rule): string
    {
        $label = vtranslate(self::getActions()[$rule['action']] ?? $rule['action'], 'Core');
        $actionValue = (string)$rule['action_value'];

        if ('' === $actionValue) {
            return $label;
        }

        [$fieldName, $value] = array_pad(explode(self::STATUS_VALUE_SEPARATOR, $actionValue, 2), 2, null);
        $moduleModel = Vtiger_Module_Model::getInstance($rule['module']);
        $fieldModel = $moduleModel ? $moduleModel->getField($fieldName) : false;
        $fieldLabel = $fieldModel ? vtranslate($fieldModel->get('label'), $rule['module']) : $fieldName;

        if (self::ACTION_STATUS_CHANGE === $rule['action'] && null !== $value) {
            return sprintf('%s: %s → %s', $label, $fieldLabel, vtranslate($value, $rule['module']));
        }

        return sprintf('%s: %s', $label, $fieldLabel);
    }

    /**
     * Checks whether a record has a rule of an action (any effect, user or expiry).
     *
     * @param int    $recordId
     * @param string $action
     *
     * @return bool
     * @throws Exception
     */
    protected function hasActionRules(int $recordId, string $action): bool
    {
        foreach ($this->getRecordRules($recordId) as $rule) {
            if ($action === $rule['action']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether a record has rules on field values (FieldEdit or StatusChange).
     *
     * @param int $recordId
     *
     * @return bool
     * @throws Exception
     */
    protected function hasValueRules(int $recordId): bool
    {
        return $this->hasActionRules($recordId, self::ACTION_FIELD_EDIT)
            || $this->hasActionRules($recordId, self::ACTION_STATUS_CHANGE);
    }

    /**
     * Compares a new and a stored field value: arrays as multi-select values, numbers numerically, text after
     * decoding HTML entities and trimming.
     *
     * @param mixed $newValue
     * @param mixed $currentValue
     *
     * @return bool
     */
    protected function isSameValue(mixed $newValue, mixed $currentValue): bool
    {
        $newValue = trim(decode_html(is_array($newValue) ? implode(' |##| ', $newValue) : (string)$newValue));
        $currentValue = trim(decode_html(is_array($currentValue) ? implode(' |##| ', $currentValue) : (string)$currentValue));

        if (is_numeric($newValue) && is_numeric($currentValue)) {
            return (float)$newValue === (float)$currentValue;
        }

        // An empty reference or owner is null, '' or '0' depending on the path (webservice, request, database)
        if (in_array($newValue, ['', '0'], true) && in_array($currentValue, ['', '0'], true)) {
            return true;
        }

        return $newValue === $currentValue;
    }

    /**
     * Returns the rule that decides an action for a user: the most specific scope level with a matching rule,
     * a deny rule winning on the same level.
     *
     * @param int         $recordId
     * @param string      $action
     * @param string      $value
     * @param int|null    $userId null for the current user
     *
     * @return array|null rule row, null when no rule matches
     * @throws Exception
     */
    protected function getDecisiveRule(int $recordId, string $action, string $value, ?int $userId): ?array
    {
        $rules = $this->getRecordRules($recordId);

        if (empty($rules)) {
            return null;
        }

        $scopes = $this->getUserScopes($userId ?? (int)Users_Record_Model::getCurrentUserModel()->getId());
        $levels = [];

        foreach ($rules as $rule) {
            if (!$this->isRuleMatching($rule, $action, $value, $scopes)) {
                continue;
            }

            $levelRule = $levels[$rule['scope_type']] ?? null;

            if (!$levelRule || self::EFFECT_DENY === $rule['effect']) {
                $levels[$rule['scope_type']] = $rule;
            }
        }

        foreach (self::SCOPE_PRIORITY as $scopeType) {
            if (isset($levels[$scopeType])) {
                return $levels[$scopeType];
            }
        }

        return null;
    }

    /**
     * Checks whether a rule applies to an action, its value and a user and has not expired.
     *
     * @param array  $rule
     * @param string $action
     * @param string $value
     * @param array  $scopes result of getUserScopes()
     *
     * @return bool
     */
    protected function isRuleMatching(array $rule, string $action, string $value, array $scopes): bool
    {
        if ($rule['action'] !== $action || $this->isRuleExpired($rule)) {
            return false;
        }

        if ('' !== $rule['action_value'] && $rule['action_value'] !== $value) {
            return false;
        }

        if (self::SCOPE_ALL === $rule['scope_type']) {
            return true;
        }

        return in_array((string)$rule['scope_id'], $scopes[$rule['scope_type']] ?? [], true);
    }

    /**
     * Checks whether a rule has passed its expiry time (server time zone, as stored by saveRule()).
     *
     * @param array $rule
     *
     * @return bool
     */
    protected function isRuleExpired(array $rule): bool
    {
        return !empty($rule['expires_time']) && $rule['expires_time'] <= date('Y-m-d H:i:s');
    }

    /**
     * Returns the scope ids of a user: the user, the groups and the role.
     *
     * @param int $userId
     *
     * @return array ['user' => [id], 'group' => [ids], 'role' => [roleid]] as strings
     */
    protected function getUserScopes(int $userId): array
    {
        if (!isset(self::$userScopes[$userId])) {
            $privileges = $userId ? Users_Privileges_Model::getInstanceById($userId) : null;

            self::$userScopes[$userId] = [
                self::SCOPE_USER  => [(string)$userId],
                self::SCOPE_GROUP => $privileges ? array_map('strval', (array)$privileges->get('groups')) : [],
                self::SCOPE_ROLE  => $privileges && $privileges->get('roleid') ? [(string)$privileges->get('roleid')] : [],
            ];
        }

        return self::$userScopes[$userId];
    }

    /**
     * Returns the translated reason of a rule; a stored language key is looked up in the record module and Core.
     *
     * @param array $rule
     *
     * @return string
     */
    protected function getRuleReason(array $rule): string
    {
        $reason = decode_html((string)$rule['reason']);

        if ('' === $reason) {
            return vtranslate('LBL_RECORD_ACTION_DENIED', 'Core');
        }

        $translated = vtranslate($reason, $rule['module']);

        if ($translated === $reason && vtlib_isModuleActive((string)$rule['source'])) {
            // A module that writes rules (for example Approvals) keeps its reasons in its own language file
            $translated = vtranslate($reason, $rule['source']);
        }

        return $translated !== $reason ? $translated : vtranslate($reason, 'Core');
    }

    /**
     * Returns the translated source of a rule; a module source is shown by its module name.
     *
     * @param array $rule
     *
     * @return string
     */
    protected function getSourceLabel(array $rule): string
    {
        $key = 'LBL_RECORD_ACTION_SOURCE_' . $rule['source'];
        $label = vtranslate($key, 'Core');

        return $label === $key ? vtranslate((string)$rule['source'], (string)$rule['source']) : $label;
    }

    /**
     * Returns the translated description of the users a rule applies to.
     *
     * @param array $rule
     *
     * @return string
     */
    protected function getScopeLabel(array $rule): string
    {
        $scopeId = (string)$rule['scope_id'];

        // Names are stored HTML-encoded; the template escapes them once
        return match ($rule['scope_type']) {
            self::SCOPE_USER => decode_html((string)getUserFullName((int)$scopeId)),
            self::SCOPE_GROUP => (string)(getGroupName((int)$scopeId)[0] ?? ''),
            self::SCOPE_ROLE => decode_html((string)getRoleName($scopeId)),
            default => vtranslate('LBL_RECORD_ACTION_SCOPE_ALL', 'Core'),
        };
    }

    /**
     * Validates the values of a new rule.
     *
     * @param string $action
     * @param string $effect
     * @param string $scopeType
     * @param string $scopeId
     *
     * @return void
     * @throws Exception for an empty action, unknown effect or scope, or a scope without id
     */
    protected function validateRule(string $action, string $effect, string $scopeType, string $scopeId): void
    {
        $isValid = '' !== $action
            && in_array($effect, [self::EFFECT_ALLOW, self::EFFECT_DENY], true)
            && in_array($scopeType, self::SCOPE_PRIORITY, true)
            && (self::SCOPE_ALL === $scopeType || '' !== $scopeId);

        if (!$isValid) {
            throw new Exception('Invalid record action permission rule');
        }
    }

    /**
     * Normalizes a list of ids to distinct positive integers.
     *
     * @param array $ids
     *
     * @return array
     */
    protected static function getIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }
}
