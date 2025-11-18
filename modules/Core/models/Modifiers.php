<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Modifiers_Model extends Core_DatabaseData_Model
{
    protected static array $modifiers = [];

    protected string $table = 'df_modifiers';
    protected string $tableId = 'modifier_id';
    protected string $tableName = 'modifier';

    /**
     * @param string $forModule
     *
     * @return array
     */
    public static function getAll(string $forModule = ''): array
    {
        if ($forModule == '') {
            return static::$modifiers;
        }

        $moduleId = getTabId($forModule);

        if (!$moduleId) {
            return static::$modifiers;
        }

        $db = PearDatabase::getInstance();
        $return = static::$modifiers;
        $res = $db->pquery('SELECT modifiable, class_name FROM df_modifiers WHERE tab_id = ?', [$moduleId]);

        while ($row = $db->fetchByAssoc($res)) {
            $return[$row['modifiable']][] = $row['class_name'];
        }

        return $return;
    }

    /**
     * @param string $className
     * @param string $forModule
     *
     * @return array
     */
    public static function getForClass(string $className, string $forModule = ''): array
    {
        $return = [];
        $modifiers = self::getAll($forModule);
        $classNameParts = array_pad(explode('_', $className), 3, '');
        [$handlerName, $handlerType] = array_slice($classNameParts, -2);
        $modifierClassName = $forModule . '_Modifiers_Model';

        if ($forModule !== '' && method_exists($modifierClassName, 'getAll')) {
            $modifiers = $modifierClassName::getAll();
        }

        if ($handlerName && $handlerType && isset($modifiers[$handlerName . $handlerType])) {
            foreach ($modifiers[$handlerName . $handlerType] as $modifier) {
                $return[] = new $modifier();
            }
        }

        return $return;
    }

    /**
     * Modifies a class by applying all relevant modifiers to a specific method.
     *
     * @param string $className  The name of the class to modify.
     * @param string $methodName The method name to be modified.
     * @param string $forModule  Optional parameter specifying the module context.
     *
     * @return void
     */
    public static function modifyForClass(string $className, string $methodName, string $forModule = ''): void
    {
        $fullArgs = func_get_args();
        array_splice($fullArgs, 0, 3);

        $modifiers = self::getForClass($className, $forModule);
        $realMethodName = 'modify' . ucfirst($methodName);

        foreach ($modifiers as $modifier) {
            if (method_exists($modifier, $realMethodName)) {
                $modifier->$realMethodName(...$fullArgs);
            }
        }
    }

    /**
     * Returns a modified array after applying all relevant modifiers to a specific method.
     *
     * @param string $className  The name of the class to modify.
     * @param string $methodName The method name to be modified.
     * @param string $forModule  Optional parameter specifying the module context.
     * @param mixed  $modifiable
     *
     * @return void
     */
    public static function modifyVariableForClass(string $className, string $methodName, string $forModule = '', mixed &$modifiable = []): void
    {
        $fullArgs = func_get_args();
        array_splice($fullArgs, 0, 4);

        $modifiers = self::getForClass($className, $forModule);
        $realMethodName = 'modify' . ucfirst($methodName);

        foreach ($modifiers as $modifier) {
            if (method_exists($modifier, $realMethodName)) {
                $modifier->$realMethodName($modifiable, ...$fullArgs);
            }
        }
    }

    /**
     * @return self
     */
    public function getModifiersTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    /**
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->getModifiersTable()
            ->createTable()
            ->createColumn('tab_id', 'int(19)')
            ->createColumn('from_tab_id', 'int(19)')
            ->createColumn('modifiable', 'varchar(255)')
            ->createColumn('class_name', 'varchar(255)')
            ->createKey('PRIMARY KEY IF NOT EXISTS `tab_id` (`tab_id`)')
            ->createKey('CONSTRAINT `fk_1_df_modifiers` FOREIGN KEY IF NOT EXISTS (`tab_id`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE')
            ->createKey('CONSTRAINT `fk_1_df_modifiers` FOREIGN KEY IF NOT EXISTS (`from_tab_id`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE');
    }

    /**
     * Registers a modifier for a specific module.
     *
     * @param string $forModule  The module to which the modifier applies.
     * @param string $fromModule The module providing the modifier.
     * @param string $modifiable The modifiable target view/action for which the modifier applies.
     * @param string $className  The class name of the modifier.
     *
     * @return void
     *
     * @throws Exception If either the target or source module is unknown.
     */
    public static function registerModifier(string $forModule, string $fromModule, string $modifiable, string $className): void
    {
        $db = PearDatabase::getInstance();

        $forModuleId = getTabId($forModule);
        $fromModuleId = getTabId($fromModule);

        if (!$forModuleId) {
            throw new Exception('Unknown module: ' . $forModule);
        }

        if (!$fromModuleId) {
            throw new Exception('Unknown module: ' . $fromModule);
        }

        $controlRes = $db->pquery(
            'SELECT * FROM df_modifiers WHERE tab_id = ? AND from_tab_id = ? AND modifiable = ? AND class_name = ?',
            [$forModuleId, $fromModuleId, $modifiable, $className]
        );

        if (!$db->num_rows($controlRes)) {
            $db->pquery('INSERT INTO df_modifiers (tab_id, from_tab_id, modifiable, class_name) VALUES (?, ?, ?, ?)', [$forModuleId, $fromModuleId, $modifiable, $className]);
        }
    }

    /**
     * Deregisters a modifier based on the provided parameters.
     * At least one of [$forModule, $fromModule] has to be defined.
     *
     * @param string $forModule  The module for which the modifier is registered.
     * @param string $fromModule The module providing the modifier.
     * @param string $modifiable The modifiable target view/action for which the modifier applies.
     * @param string $className  The class name of the modifier to be deregistered.
     *
     * @return void
     */
    public static function deregisterModifier(string $forModule = '', string $fromModule = '', string $modifiable = '', string $className = ''): void
    {
        $db = PearDatabase::getInstance();
        $forModuleId = getTabId($forModule);
        $fromModuleId = getTabId($fromModule);

        $sql = 'DELETE FROM df_modifiers WHERE ';
        $params = [];

        if (empty($forModuleId)) {
            if (empty($fromModuleId)) {
                return;
            }

            $sql .= 'from_tab_id = ?';
            $params[] = $fromModuleId;
        } else {
            $sql .= 'tab_id = ?';
            $params[] = $forModuleId;

            if ($fromModuleId) {
                $sql .= ' AND from_tab_id = ?';
                $params[] = $fromModuleId;
            }
        }

        if ($modifiable) {
            $sql .= ' AND modifiable = ?';
            $params[] = $modifiable;
        }

        if ($className) {
            $sql .= ' AND class_name = ?';
            $params[] = $className;
        }

        $db->pquery($sql, $params);
    }
}