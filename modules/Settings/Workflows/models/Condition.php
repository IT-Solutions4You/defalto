<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 * (c) IT-Solutions4You s.r.o.
 * Licensed under the GNU AGPL v3 License. See LICENSE-AGPLv3.txt.
 */

class Settings_Workflows_Condition_Model
{
    public static function isModernFormat($conditions): bool
    {
        if (!is_array($conditions) || !array_is_list($conditions)) {
            return false;
        }

        $normalized = self::getEditableConditions($conditions);

        if ($normalized === null) {
            return false;
        }

        foreach ($conditions as $index => $condition) {
            if (!is_array($condition)
                || count(array_intersect(['fieldname', 'operation', 'value', 'valuetype', 'groupid', 'groupjoin', 'joincondition'], array_keys($condition))) !== 7
                || $condition['groupjoin'] !== 'and'
                || $condition['groupid'] != $normalized[$index]['groupid']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return editor-compatible conditions without changing their boolean meaning.
     * Unsupported expressions remain legacy; never infer groups from groupjoin.
     */
    public static function getEditableConditions($conditions): ?array
    {
        if (!is_array($conditions)) {
            return null;
        }

        $groups = [];
        $lastGroup = null;

        foreach ($conditions as $condition) {
            $condition = (array)$condition;
            $group = (string)($condition['groupid'] ?? '0');

            if (!in_array($group, ['0', '1'], true)
                || !isset($condition['fieldname'], $condition['operation']) || !array_key_exists('value', $condition)
                || in_array($condition['fieldname'], ['', 'none'], true)
                || in_array($condition['operation'], ['', 'none'], true)
                || ($condition['value'] !== null && !is_scalar($condition['value']))
                || !in_array($condition['groupjoin'] ?? '', ['', 'and'], true)) {
                return null;
            }

            // Interleaved groups are interpreted differently by scheduled execution.
            if ($lastGroup !== $group && isset($groups[$group])) {
                return null;
            }

            $lastGroup = $group;
            $groups[$group][] = $condition;
        }

        $result = [];

        foreach ($groups as $group => $rows) {
            $operator = null;

            foreach ($rows as $index => $row) {
                if ($index === count($rows) - 1) {
                    continue;
                }

                $join = ($row['joincondition'] ?? '') ?: 'and';

                if (!in_array($join, ['and', 'or'], true) || ($operator !== null && $operator !== $join)) {
                    return null;
                }

                $operator = $join;
            }

            $targetGroup = $operator === null ? (int)$group : ($operator === 'and' ? 0 : 1);
            $lastJoin = $rows[count($rows) - 1]['joincondition'] ?? '';

            if ($operator !== null && $lastJoin !== '' && $lastJoin !== $operator) {
                return null;
            }

            if (isset($result[$targetGroup])) {
                // Merging OR groups joined by AND would change their meaning.
                return null;
            }

            foreach ($rows as $index => $row) {
                $row['value'] = $row['value'] ?? '';
                $row['valuetype'] = ($row['valuetype'] ?? '') ?: 'rawtext';
                $row['groupid'] = (string)$targetGroup;
                $row['groupjoin'] = 'and';
                $row['joincondition'] = $index === count($rows) - 1 ? '' : ($targetGroup === 0 ? 'and' : 'or');
                $result[$targetGroup][] = $row;
            }
        }

        ksort($result);

        return $result ? array_merge(...array_values($result)) : [];
    }
}
