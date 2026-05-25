<?php

declare(strict_types=1);

namespace tests\oihana\openedge\db\helpers;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use PHPUnit\Framework\TestCase;

use oihana\enums\Char;
use oihana\openedge\db\enums\functions\DateFunction;
use oihana\openedge\db\enums\functions\NumericFunction;
use oihana\openedge\enums\OpenEdge;
use oihana\reflect\exceptions\ConstantException;
use function oihana\openedge\db\helpers\literal;
use function oihana\openedge\db\helpers\valueExpression;

final class ValueExpressionTest extends TestCase
{
    /**
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testEmptyDefinitionReturnsEmptyString(): void
    {
        $this->assertSame(Char::EMPTY, valueExpression([]));
    }

    /**
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testLiteralValueReturnsQuotedString(): void
    {
        $definition = [OpenEdge::VALUE => 'hello'];
        $result = valueExpression($definition);
        $this->assertSame(literal('hello'), $result);
    }

    /**
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testCurDateReturnsCurDateFunction(): void
    {
        $definition = [OpenEdge::VALUE => DateFunction::CURDATE];
        $result = valueExpression($definition);
        $this->assertStringContainsString('CURDATE', $result);
    }

    /**
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testNowReturnsNowFunction(): void
    {
        $definition = [OpenEdge::VALUE => DateFunction::NOW];
        $result = valueExpression($definition);
        $this->assertStringContainsString('NOW', $result);
    }

    /**
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testPiReturnsPiFunction(): void
    {
        $definition = [OpenEdge::VALUE => NumericFunction::PI];
        $result = valueExpression($definition);
        $this->assertStringContainsString('PI', $result);
    }

    /**
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testWithOverrideExpression(): void
    {
        $definition =
        [
            OpenEdge::VALUE  => 'user.age',
            OpenEdge::CAST   => 'INTEGER',
            OpenEdge::ALTERS => [ ['UPPER'] ]
        ];

        $result = valueExpression($definition);
        $this->assertStringContainsString('CAST', $result);
        $this->assertStringContainsString('UPPER', $result);
        $this->assertStringContainsString('user.age', $result);
    }
}