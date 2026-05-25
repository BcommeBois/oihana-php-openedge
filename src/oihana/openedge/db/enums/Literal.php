<?php

namespace oihana\openedge\db\enums;

use oihana\reflect\traits\ConstantsTrait;

/**
 * The literal type enumeration.
 *
 * A literal, also called a constant, is a type of expression that specifies a constant value.
 * Generally, you can specify a literal wherever SQL syntax allows an expression.
 * Some SQL constructs allow literals but disallow other forms of expressions.
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Literals.html
 */
class Literal
{
    use ConstantsTrait ;

    /**
     * A date literal specifies a day, month, and year using any of the following formats,
     * enclosed in single quotation marks ( ' ' ).
     *
     * **Syntax:**
     * ```sql
     * { d 'yyyy-mm-dd'}
     * ```
     */
    const string DATE = 'date' ;

    /**
     * A numeric literal is a string of digits that SQL interprets as a decimal number.
     * SQL allows the string to be in a variety of formats, including scientific notation.
 */
    const string NUMERIC = 'numeric' ;

    /**
     * A character‑string literal is a string of characters enclosed in single quotation marks ( '').
     * To include a single quotation mark in a character‑string literal, precede it with an additional single quotation mark.
     */
    const string STRING = 'string' ;

    /**
     * Time literals specify an hour, minute, second, and millisecond, using the following format,
     * enclosed in single quotation marks (' ' ).
     *
     * **Syntax*
     * ```sql
     * { t 'hh:mi:ss'[:mls] }
     * ```
     */
    const string TIME = 'time' ;

    /**
     * A date literal specifies a day, month, and year using any of the following formats, enclosed in single quotation marks ( ' ' ).
     */
    const string TIMESTAMP = 'timestamp' ;
}