<?php

namespace oihana\openedge\db\helpers\functions\strings;

use oihana\openedge\db\enums\functions\StringFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `PRO_ELEMENT()` expression.
 *
 * The `PRO_ELEMENT()` function extracts one or more elements from an array-like
 * string column.
 *
 * **SQL Syntax:**
 * ```
 * PRO_ELEMENT('array_style_expression', start_position, end_position)
 * ```
 *
 * @param string   $arrayExpression A semicolon-separated string.
 * @param int      $startPosition The starting element position.
 * @param int|null $endPosition The ending element position.
 *
 * @return string The generated SQL `PRO_ELEMENT()` expression.
 *
 * @example
 * ```php
 * echo proElement('a;b;c', 1, 2); // outputs: PRO_ELEMENT('a;b;c',1,2)
 * ```
 *
 * @see StringFunction::PRO_ELEMENT
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/PRO_ELEMENT-function.html
 */
function proElement( string $arrayExpression, int $startPosition, ?int $endPosition ): string
{
    if (!isset($endPosition)) {
        $endPosition = $startPosition;
    }
    return func(StringFunction::PRO_ELEMENT, [$arrayExpression, $startPosition, $endPosition]);
}
