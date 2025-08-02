<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_CustomLabels_Model extends Vtiger_Base_Model
{
    public $labelKey;
    public $labelId;

    /**
     * @var PearDatabase
     */
    public $db;

    /**
     * @throws Exception
     */
    public static function getInstance($value)
    {
        $self = self::getCleanInstance();
        $self->setLabelKey($value);
        $self->retrieveInfo();

        return $self;
    }

    public static function getCleanInstance()
    {
        $self = new self();
        $self->db = PearDatabase::getInstance();

        return $self;
    }

    /**
     * @throws Exception
     */
    public function retrieveInfo()
    {
        if (!empty($this->labelId)) {
            $sql = 'SELECT * FROM vtiger_emakertemplates_label_keys WHERE label_id=?';
            $params = [$this->labelId];
        } else {
            $sql = 'SELECT * FROM vtiger_emakertemplates_label_keys WHERE label_key=?';
            $params = [$this->labelKey];
        }

        $result = $this->db->pquery($sql, $params);
        $values = $this->db->query_result_rowdata($result);

        if (!empty($values)) {
            $this->setData($values);
        }
    }

    /**
     * @throws Exception
     */
    public static function getInstanceById($value)
    {
        $self = self::getCleanInstance();
        $self->setLabelId($value);
        $self->retrieveInfo();

        return $self;
    }

    public function getLabelKey()
    {
        return $this->get('label_key');
    }

    public function setLabelKey($value)
    {
        $this->set('label_key', $value);
        $this->labelKey = $value;
    }

    /**
     * @throws Exception
     */
    public function saveLabelKey()
    {
        $this->db->pquery(
            'INSERT IGNORE INTO vtiger_emakertemplates_label_keys (label_key) VALUES (?)',
            [$this->getLabelKey()]
        );
        $this->retrieveInfo();
    }

    public function deleteLabelKeys()
    {
        $this->db->pquery(
            'DELETE FROM vtiger_emakertemplates_label_keys WHERE label_id=?',
            [$this->getLabelId()]
        );
    }

    /**
     * @return int
     */
    public function getLabelId()
    {
        return intval($this->get('label_id'));
    }

    /**
     * @param int $value
     */
    public function setLabelId($value)
    {
        $this->set('label_id', $value);
        $this->labelId = $value;
    }

    public function deleteLabelValues()
    {
        $this->db->pquery('DELETE FROM vtiger_emakertemplates_label_vals WHERE label_id=?', [$this->getLabelId()]);
    }

    public function saveLabelValues(Vtiger_Request $request)
    {
        $languageValues = $this->getLanguageValues();

        foreach ($languageValues as $languageId => $languageValue) {
            $control = $request->get('LblVal' . $languageId);

            if ('yes' === $control) {
                $labelValue = $request->get('LblVal' . $languageId . 'Value');
                $this->saveLabelValue($languageId, $labelValue);
            }
        }
    }

    public function getLanguageValues()
    {
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        [$objectLabels, $languages] = $EMAILMaker->GetCustomLabels();

        $objectLabel = $objectLabels[$this->getLabelId()];

        return $objectLabel->GetLangValsArr();
    }

    public function saveLabelValue($languageId, $labelValue)
    {
        $labelId = $this->getLabelId();
        $result2 = $this->db->pquery(
            'SELECT * FROM vtiger_emakertemplates_label_vals WHERE label_id=? AND lang_id=?',
            [$labelId, $languageId]
        );
        $params = [$labelValue, $labelId, $languageId];

        if ($this->db->num_rows($result2)) {
            $this->db->pquery(
                'UPDATE vtiger_emakertemplates_label_vals SET label_value=? WHERE label_id=? AND lang_id=?',
                $params
            );
        } elseif (!empty($labelValue)) {
            $this->db->pquery(
                'INSERT INTO vtiger_emakertemplates_label_vals (label_value, label_id,lang_id) VALUES (?,?,?)',
                $params
            );
        }
    }
}