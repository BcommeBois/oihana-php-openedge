<?php

declare(strict_types=1);

namespace tests\oihana\openedge\db\helpers;

use oihana\enums\Char;
use PHPUnit\Framework\TestCase;

use oihana\openedge\enums\OpenEdge;

use function oihana\openedge\db\helpers\concatExpression;

final class ConcatExpressionTest extends TestCase
{
    /**
     * Test ARRAY concatenation with semicolon separator
     */
    public function testArrayConcatenation(): void
    {
        $definition = [
            OpenEdge::ARRAY => [
                'firstName',
                'lastName',
                'email'
            ]
        ];

        $result = concatExpression($definition);

        $this->assertEquals
        (
            "firstName || ';' || lastName || ';' || email",
            $result
        );
    }

    /**
     * Test LIST concatenation with default comma separator
     */
    public function testListConcatenationWithDefaultSeparator(): void
    {
        $definition =
        [
            OpenEdge::LIST =>
            [
                'firstName',
                'lastName'
            ]
        ];

        $result = concatExpression($definition);

        $this->assertEquals(
            "firstName || ',' || lastName",
            $result
        );
    }

    /**
     * Test LIST concatenation with custom separator
     */
    public function testListConcatenationWithCustomSeparator(): void
    {
        $definition =
        [
            OpenEdge::LIST =>
            [
                'firstName',
                'lastName'
            ],
            OpenEdge::SEPARATOR => ' - '
        ];

        $result = concatExpression($definition);

        $this->assertEquals(
            "firstName || ' - ' || lastName",
            $result
        );
    }

    /**
     * Test CONCAT concatenation with space separator
     */
    public function testConcatWithSpace(): void
    {
        $definition = [
            OpenEdge::CONCAT => [
                'firstName',
                'lastName'
            ]
        ];

        $result = concatExpression($definition);

        // CONCAT_WITH_SPACE is ' || ' (not " || ' ' || ")
        $this->assertEquals(
            "firstName || lastName",
            $result
        );
    }

    /**
     * Test empty array returns empty string
     */
    public function testEmptyArrayReturnsEmpty(): void
    {
        $definition = [
            OpenEdge::ARRAY => []
        ];

        $result = concatExpression($definition);

        $this->assertEquals(Char::EMPTY, $result);
    }

    /**
     * Test null expressions returns empty string
     */
    public function testNullExpressionsReturnsEmpty(): void
    {
        $definition = [
            OpenEdge::ARRAY => null
        ];

        $result = concatExpression($definition);

        $this->assertEquals(Char::EMPTY, $result);
    }

    /**
     * Test missing key returns empty string
     */
    public function testMissingKeyReturnsEmpty(): void
    {
        $definition = [];

        $result = concatExpression($definition);

        $this->assertEquals(Char::EMPTY, $result);
    }

    /**
     * Test with callback transformation
     */
    public function testWithCallbackTransformation(): void
    {
        $definition = [
            OpenEdge::CONCAT => [
                'name',
                'email'
            ]
        ];

        $callable = fn($expr) => strtoupper($expr);
        $result = concatExpression($definition, $callable);

        $this->assertEquals(
            "NAME || EMAIL",
            $result
        );
    }

    /**
     * Test ARRAY has priority over LIST
     */
    public function testArrayPriorityOverList(): void
    {
        $definition = [
            OpenEdge::ARRAY => ['a', 'b'],
            OpenEdge::LIST => ['c', 'd']
        ];

        $result = concatExpression($definition);

        // Should use ARRAY (semicolon), not LIST (comma)
        $this->assertEquals( "a || ';' || b", $result );
    }

    /**
     * Test LIST has priority over CONCAT
     */
    public function testListPriorityOverConcat(): void
    {
        $definition = [
            OpenEdge::LIST => ['a', 'b'],
            OpenEdge::CONCAT => ['c', 'd']
        ];

        $result = concatExpression($definition);

        // Should use LIST (comma), not CONCAT (space)
        $this->assertEquals( "a || ',' || b", $result );
    }

    /**
     * Test with nested arrays (complex expressions)
     */
    public function testWithNestedArrays(): void
    {
        $callable = fn($expr) => is_array($expr) ? 'COLUMN(' . $expr['column'] . ')' : $expr;

        $definition =
        [
            OpenEdge::CONCAT => [ ['column' => 'firstName'], ' - ', ['column' => 'lastName'] ]
        ];

        $result = concatExpression($definition, $callable);

        $this->assertEquals( "COLUMN(firstName) ||  -  || COLUMN(lastName)", $result );
    }

    /**
     * Test ARRAY with single element
     */
    public function testArrayWithSingleElement(): void
    {
        $definition = [
            OpenEdge::ARRAY => ['onlyOne']
        ];

        $result = concatExpression($definition);

        $this->assertEquals('onlyOne', $result);
    }

    /**
     * Test LIST with pipe separator
     */
    public function testListWithPipeSeparator(): void
    {
        $definition = [
            OpenEdge::LIST => ['a', 'b', 'c'],
            OpenEdge::SEPARATOR => '|'
        ];

        $result = concatExpression($definition);

        $this->assertEquals(
            "a || '|' || b || '|' || c",
            $result
        );
    }

    /**
     * Test with numeric values
     */
    public function testWithNumericValues(): void
    {
        $definition = [
            OpenEdge::CONCAT => [1, 2, 3]
        ];

        $result = concatExpression($definition);

        $this->assertEquals(
            "1 || 2 || 3",
            $result
        );
    }

    /**
     * Test with mixed types
     */
    public function testWithMixedTypes(): void
    {
        $definition =
        [
            OpenEdge::LIST => ['text', 123, true, null]
        ];

        $result = concatExpression($definition);

        // null values are cleaned by compile function
        $this->assertEquals( "text || ',' || 123 || ',' || true", $result );
    }
}