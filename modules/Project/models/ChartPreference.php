<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
class Project_ChartPreference_Model extends Core_DatabaseData_Model
{
    protected string $table = 'df_project_chart_preference';
    protected string $tableId = 'id';
    protected static array $numericSettingLimits = [
        'scale' => [0, 2],
        'scroll_x' => [0, 100000000],
        'scroll_y' => [0, 100000000],
    ];
    protected static array $textSettings = ['search', 'status', 'selected'];

    protected function getChartPreferenceTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    public function isAvailable(): bool
    {
        return Vtiger_Utils::CheckTable($this->table);
    }

    public function getPreferences(int $projectId, int $userId): array
    {
        if (!$this->isAvailable()) {
            return [];
        }

        $row = $this->getChartPreferenceTable()->selectData(['settings'], ['projectid' => $projectId, 'userid' => $userId]);
        $settings = $row ? json_decode($row['settings'], true) : [];

        return is_array($settings) ? $this->getValidSettings($settings) : [];
    }

    public function getValidSettings(array $settings): array
    {
        $result = [];

        foreach (self::$numericSettingLimits as $key => [$min, $max]) {
            if (isset($settings[$key]) && is_numeric($settings[$key])) {
                $result[$key] = max($min, min($max, (int)$settings[$key]));
            }
        }

        foreach (self::$textSettings as $key) {
            if (isset($settings[$key]) && is_string($settings[$key])) {
                $result[$key] = preg_match('/^.{0,500}/us', $settings[$key], $match) ? $match[0] : '';
            }
        }

        if (isset($settings['owner']) && is_string($settings['owner'])) {
            $owner = $settings['owner'];
            $result['owner'] = preg_match('/^\d+(?:,\d+)*$/D', $owner)
                ? implode(',', array_unique(explode(',', $owner))) : '';
        }

        if (isset($settings['show_grid'])) {
            $result['show_grid'] = filter_var($settings['show_grid'], FILTER_VALIDATE_BOOLEAN);
        }

        return $result;
    }

    public function savePreferences(int $projectId, int $userId, array $settings): void
    {
        $this->retrieveDB();
        $this->getDB()->pquery('INSERT INTO df_project_chart_preference (projectid,userid,settings) VALUES (?,?,?)'
            . ' ON DUPLICATE KEY UPDATE settings=VALUES(settings)',
            [$projectId, $userId, json_encode($this->getValidSettings($settings), JSON_UNESCAPED_UNICODE)]);
    }

    public function clearPreferences(int $projectId, int $userId): void
    {
        $this->getChartPreferenceTable()->deleteData(['projectid' => $projectId, 'userid' => $userId]);
    }
}
