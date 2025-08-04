<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Tour_QuickStart_Guide extends Tour_Base_Guide
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'QuickStart';
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return 'Quick Start Guide';
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa-solid fa-stopwatch';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'This guide will help you quickly get started with the application. It will walk you through the basic features and functionalities.';
    }

    /**
     * @return string
     */
    public function getDemoDataSource(): string
    {
        return 'DemoData' . $this->getName();
    }

    /**
     * @throws Exception
     */
    public function deleteDemoData(): void
    {
        $this->getTable('vtiger_crmentity', null)->deleteData(['source' => $this->getDemoDataSource()]);
    }

    /**
     * @return void
     */
    public function importDemoData(): void
    {
        $recordModel = Vtiger_Record_Model::getCleanInstance('Contacts');
        $recordModel->set('firstname', 'Demo');
        $recordModel->set('lastname', 'Demo');
        $recordModel->set('source', 'DemoData' . $this->getName());
        $recordModel->save();
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getDemoDataRecordId(): int
    {
        $data = $this->getTable('vtiger_crmentity', null)->selectData(['crmid'], ['source' => $this->getDemoDataSource()]);

        return (int)$data['crmid'];
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getStepUrl(): string
    {
        $step = $this->getStep();

        if (1 === $step) {
            return 'index.php?module=Contacts&view=List';
        }

        if (2 === $step) {
            return 'index.php?module=Contacts&view=Edit';
        }

        if (3 === $step) {
            return 'index.php?module=Contacts&view=Detail&record=' . $this->getDemoDataRecordId();
        }

        return $this->getUrl();
    }

    /**
     * @return bool
     */
    public function isLastStep(): bool
    {
        return $this->getStep() >= 3;
    }
}