<?php

namespace oihana\openedge\db\enums;

use oihana\enums\Char;
use oihana\reflect\traits\ConstantsTrait;

class ConcatOperator
{
    use ConstantsTrait ;

    /**
     * Use the concatenation operator (||) to join two text strings together.
     */
    const string CONCAT = Char::DOUBLE_PIPE ;

    /**
     * A special concatenation operator with space before and after.
     */
    const string CONCAT_WITH_SPACE = Char::SPACE . Char::DOUBLE_PIPE . Char::SPACE ;

    /**
     * A special concatenation operator with COMMA between expressions -> " || ',' || "
     */
    const string CONCAT_WITH_COMMA_SEPARATOR = self::CONCAT_WITH_SPACE . Char::SIMPLE_QUOTE . Char::COMMA . Char::SIMPLE_QUOTE . self::CONCAT_WITH_SPACE ;

    /**
     * Returns a concat expression with a specific separator character.
     * @param ?string $separator
     * @return string
     * @example
     * ```
     * echo ConcatOperator::concatSeparator() ; // " || ',' || "
     * echo ConcatOperator::concatSeparator(';') ; // " || ';' || "
     * ```
     */
    public static function concatSeparator( ?string $separator = null ):string
    {
        if( is_null( $separator ) || $separator == Char::COMMA )
        {
            return self::CONCAT_WITH_COMMA_SEPARATOR ;
        }
        return self::CONCAT_WITH_SPACE . Char::SIMPLE_QUOTE . $separator . Char::SIMPLE_QUOTE . self::CONCAT_WITH_SPACE ;
    }
}
