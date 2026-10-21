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
 * Which CRM records may feed an AI prompt of the current user (I16): the records the
 * user's own list view of the module would show — the list access query of the
 * QueryGenerator applies the owner, the sharing rules and the private sharing, which a
 * per-record isPermitted() alone does not (QA PT09359).
 */
class MailManager_ProAiAccess_Model
{
    /**
     * @param string $module
     * @param int[]  $recordIds
     *
     * @return int[] the ids of $recordIds the current user may see, in their order
     */
    public static function getVisibleIds(string $module, array $recordIds): array
    {
        $recordIds = array_values(array_unique(array_filter(array_map('intval', $recordIds))));

        if (!$recordIds || !vtlib_isModuleActive($module) || !Users_Privileges_Model::isPermitted($module, 'DetailView')) {
            return [];
        }

        $user = Users_Record_Model::getCurrentUserModel();
        $queryGenerator = new QueryGenerator($module, $user);
        $queryGenerator->setFields(['id']);
        // the list query as it is (its access joins, its WHERE), narrowed from outside
        $idColumn = substr((string)strrchr('.' . $queryGenerator->getSQLColumn('id'), '.'), 1);
        $query = 'SELECT mm_visible.' . $idColumn . ' FROM (' . $queryGenerator->getQuery() . ') mm_visible
            WHERE mm_visible.' . $idColumn . ' IN (' . generateQuestionMarks($recordIds) . ')';

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery($query, $recordIds);
        $visible = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            $visible[(int)reset($row)] = true;
        }

        return array_values(array_filter($recordIds, function ($recordId) use ($visible) {
            return isset($visible[$recordId]);
        }));
    }

    /**
     * @param string $module
     * @param int    $recordId
     *
     * @return bool the current user may see the record in the module's list
     */
    public static function isVisible(string $module, int $recordId): bool
    {
        return (bool)self::getVisibleIds($module, [$recordId]);
    }
}
