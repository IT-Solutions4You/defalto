<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_MultiReference_UIType extends Vtiger_Reference_UIType
{
    /**
     * @var string
     */
    public string $referenceModule = '';
    /**
     * @var array
     */
    public array $referenceRecords = [];

    /**
     * @param mixed       $value
     * @param int|bool    $record
     * @param object|bool $recordInstance
     *
     * @return string
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false): string
    {
        $this->retrieveReference($record);

        return $this->getReferenceNames();
    }

    /**
     * @return string
     */
    public function getReferenceData(): string
    {
        return json_encode(Vtiger_Functions::getCRMRecordLabels($this->referenceModule, $this->referenceRecords));
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
    public function getReferenceOptions()
    {
        $recordNames = [];

        foreach ($this->referenceRecords as $referenceRecord) {
            $relatedRecord = Vtiger_Record_Model::getInstanceById($referenceRecord);
            $recordNames[] = sprintf('<option value="%s" selected="selected">%s</option>', $relatedRecord->getId(), $relatedRecord->getName());
        }

        return implode('', $recordNames);
    }

    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return 'uitypes/MultiReference.tpl';
    }

    /**
     * @param int $recordId
     *
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

        $this->referenceRecords = array_unique(array_filter($this->referenceRecords));
    }

    /**
     * @param mixed $value
     * @param mixed $record
     *
     * @return void
     */
    public function retrieveReference($record)
    {
        $field = $this->get('field');
        $referenceModules = $field ? $field->getReferenceList() : [];

        foreach ($referenceModules as $referenceModule) {
            $this->referenceModule = $referenceModule;

            if (!empty($record) && !empty($referenceModule)) {
                $this->retrieveRecords($record);
            }
        }
    }
}