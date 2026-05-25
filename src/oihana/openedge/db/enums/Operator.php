<?php

namespace oihana\openedge\db\enums;

use oihana\enums\Char;
use oihana\reflect\traits\ConstantsTrait;

class Operator
{
    use ConstantsTrait ;

    /**
     * Variable assignment
     */
    const string ASSIGN = Char::EQUAL ;

    /**
     * Use the concatenation operator (||) to join two text strings together.
     */
    const string CONCAT = Char::DOUBLE_PIPE ;

    /**
     * A special concatenation operator with COMMA between expressions (' || , || ').
     */
    const string CONCAT_WITH_COMMA_SEPARATOR = Char::SPACE . Char::DOUBLE_PIPE . Char::SPACE . Char::COMMA . Char::SPACE . Char::DOUBLE_PIPE . Char::SPACE ;
}
