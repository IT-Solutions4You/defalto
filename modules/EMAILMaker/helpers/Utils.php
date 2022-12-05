<?php

class EMAILMaker_Utils_Helper
{
    public static function count($value)
    {
        return !empty($value) ? count((array)$value) : 0;
    }
}