<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Modifiers_Model
{
    protected static array $modifiers = [];

    /**
     * @return array
     */
    public static function getAll(): array
    {
        return static::$modifiers;
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
        $modifiers = self::getAll();
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
}