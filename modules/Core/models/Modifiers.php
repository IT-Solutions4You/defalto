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
     * @throws Exception
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

        $return = static::$modifiers;
        $table = (new self())->getModifiersTable();
        $result = $table->selectResult(['modifiable', 'class_name'], ['tab_id' => $moduleId]);

        while ($row = $table->getDB()->fetchByAssoc($result)) {
            $return[$row['modifiable']][] = $row['class_name'];
        }

        return $return;
    }

    /**
     * @param string $className
     * @param string $forModule
     *
     * @return array
     * @throws Exception
     */
    public static function getForClass(string $className, string $forModule = ''): array
    {
        $return = [];
        $modifiers = self::getInstance($forModule)::getAll($forModule);
        $classNameParts = array_pad(explode('_', $className), 3, '');
        [$handlerName, $handlerType] = array_slice($classNameParts, -2);

        if ($handlerName && $handlerType && isset($modifiers[$handlerName . $handlerType])) {
            foreach ($modifiers[$handlerName . $handlerType] as $modifier) {
                $return[] = new $modifier();
            }
        }

        return $return;
    }

    /**
     * @param string $forModule
     * @return self
     * @throws Exception
     */
    public static function getInstance(string $forModule = 'Core'): self
    {
        $className = Vtiger_Loader::getComponentClassName('Model', 'Modifiers', $forModule);

        return new $className();
    }

    /**
     * Modifies a class by applying all relevant modifiers to a specific method.
     *
     * @param string $className The name of the class to modify.
     * @param string $methodName The method name to be modified.
     * @param string $forModule Optional parameter specifying the module context.
     *
     * @return void
     * @throws Exception
     */
    public static function modifyForClass(string $className, string $methodName, string $forModule = ''): void
    {
        $fullArgs = func_get_args();
        array_splice($fullArgs, 0, 3);

        $modifiers = self::getInstance($forModule)::getForClass($className, $forModule);
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
     * @param string $className The name of the class to modify.
     * @param string $methodName The method name to be modified.
     * @param string $forModule Optional parameter specifying the module context.
     * @param mixed $modifiable
     *
     * @return void
     * @throws Exception
     */
    public static function modifyVariableForClass(string $className, string $methodName, string $forModule = '', mixed &$modifiable = []): void
    {
        $fullArgs = func_get_args();
        array_splice($fullArgs, 0, 4);

        $modifiers = self::getInstance($forModule)::getForClass($className, $forModule);
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
        $forModuleId = getTabId($forModule);
        $fromModuleId = getTabId($fromModule);

        if (!$forModuleId) {
            throw new Exception('Unknown module: ' . $forModule);
        }

        if (!$fromModuleId) {
            throw new Exception('Unknown module: ' . $fromModule);
        }

        $table = (new self())->getModifiersTable();
        $data = ['tab_id' => $forModuleId, 'from_tab_id' => $fromModuleId, 'modifiable' => $modifiable, 'class_name' => $className];
        $table->retrieveIdByParams($data);

        if (!$table->getId()) {
            $table->insertData($data);
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
     * @throws Exception
     */
    public static function deregisterModifier(string $forModule = '', string $fromModule = '', string $modifiable = '', string $className = ''): void
    {
        $forModuleId = getTabId($forModule);
        $fromModuleId = getTabId($fromModule);
        $data = [];

        if (empty($forModuleId)) {
            if (empty($fromModuleId)) {
                return;
            }

            $data['from_tab_id'] = $fromModuleId;
        } else {
            $data['tab_id'] = $forModuleId;

            if ($fromModuleId) {
                $data['from_tab_id'] = $fromModuleId;
            }
        }

        if ($modifiable) {
            $data['modifiable'] = $modifiable;
        }

        if ($className) {
            $data['class_name'] = $className;
        }

        $table = (new self())->getModifiersTable();
        $table->deleteData($data);
    }
}