<?php

namespace oihana\openedge\db\helpers\functions\numerics;

use oihana\openedge\db\enums\functions\NumericFunction;
use function oihana\core\strings\func;

/**
 * Generates a Progress OpenEdge SQL `RAND()` expression.
 *
 * The `RAND()` function returns a random floating-point value between 0 and 1.
 * An optional integer seed can be provided to generate a repeatable sequence of numbers.
 *
 * **SQL Syntax:**
 * ```
 * RAND([seed])
 * ```
 *
 * @param null|float|int|string $expression An optional integer seed value.
 *
 * @return string The generated SQL `RAND()` expression.
 * Example: `RAND(123)`
 *
 * @example
 * ```php
 * echo rand();      // outputs: RAND()
 * echo rand(12345); // outputs: RAND(12345)
 * ```
 *
 * @see NumericFunction::RAND
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/RAND.html
 */
function rand( null|float|int|string $expression = null ):string
{
    return func(NumericFunction::RAND , $expression ) ;
}

