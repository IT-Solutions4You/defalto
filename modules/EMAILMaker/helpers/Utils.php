<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_Utils_Helper
{
    public static function count($value)
    {
        return !empty($value) && is_array($value) ? count($value) : 0;
    }
}