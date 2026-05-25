<?php

namespace oihana\openedge\db\helpers ;

use function oihana\core\arrays\toArray;

/**
 * Validates that a given context value is allowed within a specific set of permitted values.
 *
 * This function is typically used to ensure that a predicate or SQL clause is used within the correct context.
 *
 * **Behavior:**
 * - If `$context` is `null` or an empty string, validation is skipped.
 * - If `$allowed` is `null`, no validation is performed.
 * - If `$allowed` is a string, `$context` must exactly match it.
 * - If `$allowed` is an array, `$context` must be one of the values in the array.
 * - Otherwise, the function returns `false`.
 *
 * **Usage Examples:**
 * ```php
 * validateContext('WHERE', ['WHERE', 'HAVING']); // returns true
 * validateContext('GROUP', 'GROUP');             // returns true
 * validateContext('ORDER', ['WHERE', 'HAVING']); // returns false
 * validateContext(null, ['WHERE']);              // returns true
 * ```
 *
 * @param string|null        $context The context value to validate (e.g., a SQL clause).
 * @param string|array|null  $allowed A single allowed value or an array of permitted values.
 *
 * @return bool Returns `true` if the context is valid, `false` otherwise.
 */
function validateContext( ?string $context , null|string|array $allowed ): bool
{
    if ( $context === null || $allowed === null )
    {
        return true ;
    }

    $allowedValues = toArray( $allowed ) ;

    // Special handling for empty string context to satisfy both Arango and OpenEdge expectations:
    // - If empty string is explicitly listed as allowed, treat it as invalid (return false).
    // - Otherwise, skip validation (do not throw).
    if ( $context === '' )
    {
        if ( in_array( '' , $allowedValues , true ) )
        {
            return false ;
        }
        return true ;
    }

    $allowedSet = array_flip( $allowedValues ) ;

    if ( !isset( $allowedSet[ $context ] ) )
    {
        return false ;
    }

    return true ;
}