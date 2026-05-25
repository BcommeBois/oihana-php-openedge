<?php

namespace tests\oihana\openedge\db\helpers;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use oihana\enums\Char;
use oihana\openedge\db\enums\functions\StringFunction;
use oihana\openedge\db\enums\Type;
use oihana\openedge\enums\OpenEdge;
use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\columnExpression;
use function oihana\openedge\db\helpers\literal;

final class ColumnExpressionTest extends TestCase
{
    /**
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function test_returns_empty_when_no_column_and_no_default(): void
    {
        $expr = columnExpression([]);
        $this->assertSame(Char::EMPTY, $expr);
    }

    /**
     * @throws ConstantException
     */
    public function test_uses_default_when_no_column(): void
    {
        $expr = columnExpression([], 'name');
        $this->assertSame('name', $expr);
    }

    /**
     * @throws ConstantException
     */
    public function test_prefixes_with_table(): void
    {
        $expr = columnExpression
        ([
            OpenEdge::TABLE  => 'users',
            OpenEdge::COLUMN => 'name',
        ]);
        $this->assertSame('users.name', $expr);
    }

    /**
     * @throws ConstantException
     */
    public function test_appends_nullable_marker_when_nullable_true(): void
    {
        $expr = columnExpression([
            OpenEdge::TABLE    => 'users',
            OpenEdge::COLUMN   => 'id',
            OpenEdge::NULLABLE => true,
        ]);
        $this->assertSame('users.id' . OpenEdge::NULLABLE_COLUMN, $expr);
    }

    /**
     * @throws ConstantException
     */
    public function test_does_not_append_nullable_marker_when_nullable_false(): void
    {
        $expr = columnExpression([
            OpenEdge::TABLE    => 'users',
            OpenEdge::COLUMN   => 'id',
            OpenEdge::NULLABLE => false,
        ]);
        $this->assertSame('users.id', $expr);
    }

    /**
     * @throws ConstantException
     */
    public function test_override_expression_with_cast_and_alter_and_alters(): void
    {
        // Use a simple chain available in overrideExpression helpers: CAST then ALTER(S)
        // We will CAST to CHAR with length via existing functions
        $definition =
        [
            OpenEdge::TABLE  => 'users',
            OpenEdge::COLUMN => 'name',
            OpenEdge::CAST   => [ Type::CHAR, 10],
            OpenEdge::ALTER  => StringFunction::LOWER,
            OpenEdge::ALTERS =>
            [
                [ StringFunction::RPAD, 12, literal( '-' ) ],
            ],
        ];

        $expr = columnExpression($definition);

        // Expected nesting: RPAD(LOWER(CAST(users.name AS CHAR(10))),12,'-')
        $this->assertSame("RPAD(LOWER(CAST(users.name AS CHAR(10))),12,'-')", $expr);
    }
}
