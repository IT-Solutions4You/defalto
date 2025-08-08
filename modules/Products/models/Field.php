<?php

class Products_Field_Model extends Vtiger_Field_Model
{
    public function isPicklistColorSupported(): bool
    {
        return 'usageunit' !== $this->getName();
    }
}