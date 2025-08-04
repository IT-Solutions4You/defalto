<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

//Helper class that represents one label with all values for all languages
class PDFMakerLabel
{
    private $ID;
    private $key;
    private $langValsArr;

    public function __construct($_id, $_key)
    {
        $this->ID = $_id;
        $this->key = $_key;
        $this->langValsArr = [];
    }

    public function SetLangValue($_langId, $_val)
    {
        $this->langValsArr[$_langId] = $_val;
    }

    public function GetLangValue($_langId)
    {
        return $this->langValsArr[$_langId];
    }

    public function GetFirstNonEmptyValue()
    {
        $result = $this->key;
        ksort($this->langValsArr);
        foreach ($this->langValsArr as $val) {
            if ($val != "") {
                $result = $val;
                break;
            }
        }

        return $result;
    }

    public function IsLangValSet($_langId)
    {
        return isset($this->langValsArr[$_langId]);
    }

    public function GetID()
    {
        return $this->ID;
    }

    public function GetKey()
    {
        return $this->key;
    }

    public function GetLangValsArr()
    {
        ksort($this->langValsArr);

        return $this->langValsArr;
    }
}