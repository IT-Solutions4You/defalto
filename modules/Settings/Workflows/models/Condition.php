<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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
                || !in_array($condition['groupjoin'], ['and', 'or'], true)
                || $condition['groupid'] != $normalized[$index]['groupid']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return editor-compatible conditions without changing their boolean meaning.
     * Preserve row order and outgoing AND/OR connectors, as in saved lists.
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

            if (!ctype_digit($group)
                || !isset($condition['fieldname'], $condition['operation']) || !array_key_exists('value', $condition)
                || in_array($condition['fieldname'], ['', 'none'], true)
                || in_array($condition['operation'], ['', 'none'], true)
                || ($condition['value'] !== null && !is_scalar($condition['value']))
                || !in_array($condition['groupjoin'] ?? '', ['', 'and', 'or'], true)) {
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
            $groupJoin = null;

            foreach ($rows as $row) {
                $rowGroupJoin = ($row['groupjoin'] ?? '') ?: 'and';

                if ($groupJoin !== null && $groupJoin !== $rowGroupJoin) {
                    return null;
                }

                $groupJoin = $rowGroupJoin;
                $join = ($row['joincondition'] ?? '') ?: 'and';

                if (!in_array($join, ['and', 'or'], true)) {
                    return null;
                }
            }

            $targetGroup = (int)$group;

            foreach ($rows as $index => $row) {
                $row['value'] = $row['value'] ?? '';
                $row['valuetype'] = ($row['valuetype'] ?? '') ?: 'rawtext';
                $row['groupid'] = (string)$targetGroup;
                $row['groupjoin'] = $groupJoin;
                $row['joincondition'] = $index === count($rows) - 1 ? '' : (($row['joincondition'] ?? '') ?: 'and');
                $result[$targetGroup][] = $row;
            }
        }

        return $result ? array_merge(...array_values($result)) : [];
    }
}
