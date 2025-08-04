<?php
/*********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ******************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class VTExpressionToken
{
    function __construct($label)
    {
        $this->label = $label;
    }
}

function _vt_processtoken_id($token)
{
    return $token;
}

function _vt_processtoken_symbol($token)
{
    return new VTEXpressionSymbol($token);
}

class VTExpressionTokenizer
{
    function __construct($expr)
    {
        $tokenTypes = [
            "SPACE"          => ['\s+', '_vt_processtoken_id'],
            "SYMBOL"         => ['[a-zA-Z][\w]*|\(\w+ : \(\w+\) \w+\)', '_vt_processtoken_symbol'],
            "ESCAPED_SYMBOL" => ['?:`([^`]+)`', '_vt_processtoken_symbol'],
            //"STRING" => array('?:(?:"((?:\\\\"|[^"])+)"|'."'((?:\\\\'|[^'])+)')", 'stripcslashes'),
            //"STRING" => array('?:"((?:\\\\"|[^"])+)"', 'stripcslashes'),
            "STRING"         => ["?:'((?:\\\\'|[^'])+)'", 'stripcslashes'],
            "FLOAT"          => ['\d+[.]\d+', 'floatval'],
            "INTEGER"        => ['\d+', 'intval'],
            'OPERATOR'       => ['[+]|[-]|[*]|>=|<=|[<]|[>]|==|\/', '_vt_processtoken_symbol'],
            // NOTE: Any new Operator added should be updated in VTParser.inc::$precedence and operation at VTExpressionEvaluater
            'OPEN_BRACKET'   => ['[(]', '_vt_processtoken_symbol'],
            'CLOSE_BRACKET'  => ['[)]', '_vt_processtoken_symbol'],
            'COMMA'          => ['[,]', '_vt_processtoken_symbol']
        ];
        $tokenReArr = [];
        $tokenNames = [];
        $this->tokenTypes = $tokenTypes;

        foreach ($tokenTypes as $tokenName => $code) {
            [$re, $processtoken] = $code;
            $tokenReArr[] = '(' . $re . ')';
            $tokenNames[] = $tokenName;
        }
        $this->tokenNames = $tokenNames;
        $tokenRe = '/' . implode('|', $tokenReArr) . '/';
        $this->EOF = new VTExpressionToken("EOF");

        $matches = [];
        preg_match_all($tokenRe, $expr, $matches, PREG_SET_ORDER);
        $this->matches = $matches;
        $this->idx = 0;
    }

    function nextToken()
    {
        $matches = $this->matches;
        $idx = $this->idx;
        if ($idx == php7_count($matches)) {
            return $this->EOF;
        } else {
            $match = $matches[$idx];
            $this->idx = $idx + 1;
            $i = 1;
            while ($match[$i] == null) {
                $i += 1;
            }
            $tokenName = $this->tokenNames[$i - 1];
            $token = new VTExpressionToken($tokenName);
            $token->value = $this->tokenTypes[$tokenName][1]($match[$i]);

            return $token;
        }
    }
}

class VTExpressionSpaceFilter
{
    function __construct($tokens)
    {
        $this->tokens = $tokens;
    }

    function nextToken()
    {
        do {
            $token = $this->tokens->nextToken();
        } while ($token->label == "SPACE");

        return $token;
    }
}