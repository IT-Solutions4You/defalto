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
 * Module-wide settings of a payment module, one row per setting name (<payment table>_setting). The payment module
 * subclass (<Module>_Setting_Model) sets the table from its configuration.
 * Settings per document module live in the integration table (Core_PaymentIntegration_Model).
 */
abstract class Core_PaymentSetting_Model extends Core_DatabaseData_Model
{
    protected string $tableId = 'id';

    /**
     * Values read in this request per payment module class, so recalculating many documents reads each setting once.
     *
     * @var array [model class => [setting name => value]]
     */
    private static array $cache = [];

    /**
     * Creates an instance with an initialised database connection.
     *
     * @return static
     */
    public static function getInstance(): static
    {
        return (new static())->retrieveDB();
    }

    /**
     * Returns a configured table object for the settings table.
     *
     * @return self
     */
    private function getSettingTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    /**
     * Returns the underpayment that still counts as paid: a document whose balance is at most this amount gets
     * the paid status. The amount is compared in the document currency without conversion; 0 means fully paid only.
     *
     * @return float
     */
    public function getUnderpaymentThreshold(): float
    {
        return max(0.0, (float)$this->getValue(Core_PaymentConfig_Helper::SETTING_UNDERPAYMENT_THRESHOLD, '0'));
    }

    /**
     * Stores the underpayment threshold.
     *
     * @param float $threshold Amount, not negative
     * @return void
     */
    public function saveUnderpaymentThreshold(float $threshold): void
    {
        $this->saveValue(Core_PaymentConfig_Helper::SETTING_UNDERPAYMENT_THRESHOLD, (string)max(0.0, $threshold));
    }

    /**
     * Returns a stored setting.
     *
     * @param string $name    Setting name
     * @param string $default Value when the setting is not stored
     * @return string
     */
    private function getValue(string $name, string $default): string
    {
        if (!array_key_exists($name, self::$cache[static::class] ?? [])) {
            $row = $this->getSettingTable()->selectData(['value'], ['name' => $name]);
            self::$cache[static::class][$name] = $row ? (string)$row['value'] : null;
        }

        return self::$cache[static::class][$name] ?? $default;
    }

    /**
     * Inserts or updates a setting.
     *
     * @param string $name  Setting name
     * @param string $value Value
     * @return void
     */
    private function saveValue(string $name, string $value): void
    {
        $table = $this->getSettingTable();

        if ($table->selectData(['id'], ['name' => $name])) {
            $table->updateData(['value' => $value], ['name' => $name]);
        } else {
            $table->insertData(['name' => $name, 'value' => $value]);
        }

        self::$cache[static::class][$name] = $value;
    }
}
