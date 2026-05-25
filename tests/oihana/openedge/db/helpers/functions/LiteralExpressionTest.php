<?php

declare(strict_types=1);

namespace tests\oihana\openedge\db\helpers\functions;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use oihana\openedge\db\enums\Literal;
use oihana\openedge\enums\OpenEdge;
use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\literalExpression;

final class LiteralExpressionTest extends TestCase
{
    /**
     * @throws DateMalformedStringException
     * @throws DateInvalidTimeZoneException
     * @throws ConstantException
     */
    public function testReturnsNullForNullExpression(): void
    {
        $this->assertNull(literalExpression(null));
    }

    /**
     * @throws DateMalformedStringException
     * @throws DateInvalidTimeZoneException
     * @throws ConstantException
     */
    public function testReturnsExpressionAsIsIfNoDefinition(): void
    {
        $value = '123';
        $this->assertSame($value, literalExpression($value, null));
    }

    /**
     * @throws DateMalformedStringException
     * @throws DateInvalidTimeZoneException
     */
    public function testThrowsExceptionForInvalidLiteral(): void
    {
        $this->expectException(ConstantException::class);
        literalExpression('123', [OpenEdge::LITERAL => 'invalid']);
    }

    /**
     * @throws DateMalformedStringException
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     */
    public function testNumericLiteral(): void
    {
        $value = '123.45';
        $expected = '123.45';
        $this->assertSame($expected, literalExpression($value, [OpenEdge::LITERAL => Literal::NUMERIC]));
    }

    /**
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testStringLiteralEscapesSingleQuotes(): void
    {
        $value = "O'Hare";
        $expected = "'O''Hare'";
        $this->assertSame($expected, literalExpression($value, [OpenEdge::LITERAL => Literal::STRING]));
    }

    /**
     * @throws DateMalformedStringException
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     */
    public function testDateLiteral(): void
    {
        $value = '2025-10-17';
        $expected = "{ d '2025-10-17' }";
        $this->assertSame($expected, literalExpression($value, [OpenEdge::LITERAL => Literal::DATE]));
    }

    /**
     * @throws ConstantException
     * @throws DateMalformedStringException
     * @throws DateInvalidTimeZoneException
     */
    public function testTimestampLiteral(): void
    {
        $value = '2025-10-17 14:30:45';
        $expected = "{ ts '2025-10-17 14:30:45' }";
        $this->assertSame(
            $expected,
            literalExpression($value, [OpenEdge::LITERAL => Literal::TIMESTAMP])
        );
    }

    /**
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testTimeLiteralWithoutMilliseconds(): void
    {
        $value = '14:30:45';
        $expected = "{ t '14:30:45' }";
        $this->assertSame(
            $expected,
            literalExpression($value, [OpenEdge::LITERAL => Literal::TIME])
        );
    }

    /**
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testTimeLiteralWithMilliseconds(): void
    {
        $value = '14:30:45.678';
        $expected = "{ t '14:30:45:678' }";
        $this->assertSame(
            $expected,
            literalExpression($value, [
                OpenEdge::LITERAL => Literal::TIME,
                OpenEdge::MILLISECONDS => true
            ])
        );
    }

    /**
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     */
    public function testDateLiteralThrowsForInvalidDate(): void
    {
        $this->expectException(DateMalformedStringException::class);
        literalExpression('invalid-date', [OpenEdge::LITERAL => Literal::DATE]);
    }

    /**
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     */
    public function testTimeLiteralThrowsForInvalidTime(): void
    {
        $this->expectException(DateMalformedStringException::class);
        literalExpression('invalid-time', [OpenEdge::LITERAL => Literal::TIME]);
    }

    /**
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     */
    public function testTimestampLiteralThrowsForInvalidTimestamp(): void
    {
        $this->expectException(DateMalformedStringException::class);
        literalExpression('invalid-timestamp', [OpenEdge::LITERAL => Literal::TIMESTAMP]);
    }

    public function testDateLiteralThrowsForInvalidTimezone(): void
    {
        $this->expectException(DateInvalidTimeZoneException::class);
        literalExpression('2025-10-17', [
            OpenEdge::LITERAL => Literal::DATE,
            OpenEdge::TIMEZONE => 'Invalid/Zone'
        ]);
    }
}