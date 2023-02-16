<?php

/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouCalendar_MultiReference_UIType extends Vtiger_Reference_UIType
{
    /**
     * @var array
     */
    public array $referenceRecords = [];
    /**
     * @var string
     */
    public string $referenceModule = '';

    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return 'uitypes/MultiReference.tpl';
    }

    /**
     * @param mixed $value
     * @param int|bool $record
     * @param object|bool $recordInstance
     * @return string
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false): string
    {
        $this->retrieveReference($value, $record);

        return $this->getReferenceNames();
    }

    /**
     * @param mixed $value
     * @param mixed $record
     * @return void
     */
    public function retrieveReference($value, $record)
    {
        if (!empty($record) && !empty($value)) {
            $this->referenceModule = Vtiger_Functions::getCRMRecordType($value);
            $this->retrieveRecords($record);
        }
    }

    /**
     * @param int $recordId
     * @return void
     */
    public function retrieveRecords($recordId)
    {
        if (!empty($this->referenceRecords)) {
            return;
        }

        $recordModule = Vtiger_Functions::getCRMRecordType($recordId);
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid=? AND module=? AND relmodule=?', [$recordId, $recordModule, $this->referenceModule]);

        while ($row = $adb->fetchByAssoc($result)) {
            $this->referenceRecords[] = $row['relcrmid'];
        }
    }

    /**
     * @return string
     */
    public function getReferenceNames(): string
    {
        $recordNames = [];

        foreach ($this->referenceRecords as $referenceRecord) {
            $relatedRecord = Vtiger_Record_Model::getInstanceById($referenceRecord);
            $recordNames[] = sprintf('<a href="%s">%s</a><br>', $relatedRecord->getDetailViewUrl(), $relatedRecord->getName());
        }

        return implode('', $recordNames);
    }

    /**
     * @return string
     */
    public function getReferenceIds(): string
    {
        return implode(';', $this->referenceRecords);
    }

    /**
     * @return string
     */
    public function getReferenceData(): string
    {
        return json_encode(Vtiger_Functions::getCRMRecordLabels($this->referenceModule, $this->referenceRecords));
    }
}