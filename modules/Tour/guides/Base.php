<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

abstract class Tour_Base_Guide extends Core_DatabaseData_Model
{
    /**
     * @var string
     */
    public string $name = '';

    /**
     * @return mixed
     */
    abstract public function deleteDemoData();

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $value
     *
     * @return void
     */
    public function setName(string $value): void
    {
        $this->name = $value;
    }

    /**
     * @return string
     */
    abstract public function getLabel(): string;

    /**
     * @return string
     */
    abstract public function getDescription(): string;

    /**
     * @throws Exception
     */
    public static function getCurrentInstance(): self|false
    {
        if (!empty($_SESSION['Tour']['CurrentGuide'])) {
            return self::getInstance($_SESSION['Tour']['CurrentGuide']);
        }

        return false;
    }

    /**
     * @return string
     */
    public function getDeleteDemoDataUrl(): string
    {
        return 'index.php?module=Tour&action=Guide&mode=deleteDemoData&name=' . $this->getName();
    }

    /**
     * @return string
     */
    public function getImportDemoDataUrl(): string
    {
        return 'index.php?module=Tour&action=Guide&mode=importDemoData&name=' . $this->getName();
    }

    /**
     * @return string
     */
    public function getGuidesUrl(): string
    {
        return 'index.php?module=Tour&view=Index';
    }

    /**
     * @throws Exception
     */
    public static function getInstance(string $name): self
    {
        $className = 'Tour_' . $name . '_Guide';

        if (class_exists($className)) {
            $instance = new $className();
            $instance->setName($name);

            return $instance;
        }

        throw new Exception("Guide class '$className' not found.");
    }

    /**
     * @return string
     */
    public function getNextStepUrl(): string
    {
        return 'index.php?module=Tour&action=Guide&mode=nextStep&name=' . $this->getName();
    }

    /**
     * @return string
     */
    public function getPrevStepUrl(): string
    {
        return 'index.php?module=Tour&action=Guide&mode=prevStep&name=' . $this->getName();
    }

    /**
     * @return int
     */
    public function getStep(): int
    {
        return (int)$_SESSION['Tour']['CurrentStep'];
    }

    /**
     * @return string
     */
    public function getStepTemplate(): string
    {
        return 'guides/' . $this->getName() . '.tpl';
    }

    /**
     * @return string
     */
    abstract public function getStepUrl(): string;

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return 'index.php?module=Tour&view=Guide&name=' . $this->getName();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function hasDemoData(): bool
    {
        $data = $this->getTable('vtiger_crmentity', null)->selectData(['source'], ['source' => $this->getDemoDataSource()]);

        return !empty($data);
    }

    /**
     * @return mixed
     */
    abstract public function importDemoData();

    /**
     * @return mixed
     */
    abstract public function isLastStep();

    /**
     * @return void
     */
    public function setNextStep(): void
    {
        $_SESSION['Tour']['CurrentStep'] += 1;
    }

    /**
     * @return void
     */
    public function setPrevStep(): void
    {
        $_SESSION['Tour']['CurrentStep'] -= 1;

        if ($_SESSION['Tour']['CurrentStep'] < 0) {
            $_SESSION['Tour']['CurrentStep'] = 0;
        }
    }

    /**
     * @param int $step
     *
     * @return void
     */
    public function setStep(int $step): void
    {
        $_SESSION['Tour']['CurrentGuide'] = $this->getName();
        $_SESSION['Tour']['CurrentStep'] = $step;
    }

    /**
     * @throws Exception
     */
    public static function getAll(): array
    {
        $names = array_diff(scandir('modules/Tour/guides'), ['.', '..', 'Base.php']);
        $guides = [];

        foreach ($names as $name) {
            $name = str_replace('.php', '', $name);
            $guides[$name] = self::getInstance($name);
        }

        return $guides;
    }
}