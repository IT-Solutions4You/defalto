<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMakerLabel
{

    private $ID;
    private $key;
    private $langValsArr;

    public function __construct($_id, $_key)
    {
        $this->ID = $_id;
        $this->key = $_key;
        $this->langValsArr = array();
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
