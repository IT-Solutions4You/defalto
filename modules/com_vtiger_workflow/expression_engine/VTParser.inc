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

class VTExpressionTreeNode
{
    function __construct($arr)
    {
        $this->arr = $arr;
    }

    function getParams()
    {
        $arr = $this->arr;

        return array_slice($arr, 1, php7_count($arr) - 1);
    }

    function getName()
    {
        return $this->arr[0];
    }
}

class VTExpressionSymbol
{
    function __construct($value)
    {
        $this->value = $value;
    }

    function __toString()
    {
        return "VTExpressionSymbol({$this->value})";
    }
}

class VTExpressionParser
{
    function __construct($tokens)
    {
        $this->tokens = $tokens;
        $this->tokenQueue = [];
    }

    function nextToken()
    {
        if (php7_count($this->tokenQueue) == 0) {
            return $this->tokens->nextToken();
        } else {
            return array_shift($this->tokenQueue);
        }
    }

    function la($n = 1)
    {
        for ($i = php7_count($this->tokenQueue); $i < $n; $i++) {
            $token = $this->tokens->nextToken();
            $this->tokenQueue[] = $token;
        }

        return $this->tokenQueue[$n - 1];
    }

    function consume($label, $value)
    {
        $token = $this->nextToken();
        if ($token->label != $label || $token->value != $value) {
            throw new Exception("Was expecting a $label of value $value got a {$token->label} of {$token->value} instead.");
        }
    }

    function consumeSymbol($sym)
    {
        $this->consume('SYMBOL', new VTExpressionSymbol($sym));
    }

    function check($token, $label, $value)
    {
        return $token->label == $label && $token->value == $value;
    }

    function checkSymbol($token, $sym)
    {
        return $this->check($token, 'SYMBOL', new VTExpressionSymbol($sym));
    }

    function atom()
    {
        $token = $this->nextToken();
        switch ($token->label) {
            case "STRING":
                return $token->value;
            case "INTEGER":
                return $token->value;
            case "FLOAT":
                return $token->value;
            case "SYMBOL":
                return $token->value;
            case "OPEN_BRACKET":
                $val = $this->expression();
                $close = $this->nextToken();
                if ($close->label != 'CLOSE_BRACKET') {
                    throw new Exception("Was expecting a close bracket");
                }

                return $val;
            default:
                throw new Exception();
        }
    }

    function ifCondition()
    {
        $this->consumeSymbol('if');
        $cond = $this->expression();
        $this->consumeSymbol('then');
        $ifTrue = $this->expression();
        $this->consumeSymbol('else');
        if ($this->checkSymbol($this->la(), 'if')) {
            $ifFalse = $this->ifCondition();
        } else {
            $ifFalse = $this->expression();
            $this->consumeSymbol('end');
        }

        return new VTExpressionTreeNode([new VTExpressionSymbol('if'), $cond, $ifTrue, $ifFalse]);
    }

    function expression()
    {
        $la1 = $this->la(1);
        $la2 = $this->la(2);
        if ($this->checkSymbol($la1, 'if')) {
            return $this->ifCondition();
        } elseif ($la1->label == 'SYMBOL' && $la2->label == 'OPEN_BRACKET') {
            $arr = [$this->nextToken()->value];
            $this->nextToken();
            if ($this->la()->label != 'CLOSE_BRACKET') {
                $arr[] = $this->expression();
                $comma = $this->nextToken();
                while ($comma->label == 'COMMA') {
                    $arr[] = $this->expression();
                    $comma = $this->nextToken();
                }
                if ($comma->label != 'CLOSE_BRACKET') {
                    throw new Exception("Was expecting a closing bracket");
                }
            } else {
                $this->consume('CLOSE_BRACKET', new Symbol(')'));
            }

            return new VTExpressionTreeNode($arr);
        } else {
            return $this->binOp();
        }
    }

    var $precedence = [
        ['*', '/'],
        ['+', '-'],
        ['and', 'or'],
        ['==', '>=', '<=', '>', '<']
    ];

    function binOp()
    {
        return $this->binOpPrec(php7_count($this->precedence) - 1);
    }

    private function binOpPrec($prec)
    {
        if ($prec >= 0) {
            $lhs = $this->binOpPrec($prec - 1);
            $la = $this->la();
            if ($la->label == 'OPERATOR' && in_array($la->value->value, $this->precedence[$prec])) {
                $operator = $this->nextToken()->value;
                $rhs = $this->binOpPrec($prec);

                return new VTExpressionTreeNode([$operator, $lhs, $rhs]);
            } else {
                return $lhs;
            }
        } else {
            return $this->unaryOp();
        }
    }

    function unaryOp()
    {
        $la = $this->la();
        if ($la->label == "OPERATOR" && in_array($la->value->value, ['+', '-'])) {
            $token = $this->nextToken();
            $operator = $la->value;
            $operand = $this->unaryOp();

            return new VTExpressionTreeNode([$operator, $operand]);
        } else {
            return $this->atom();
        }
    }
}