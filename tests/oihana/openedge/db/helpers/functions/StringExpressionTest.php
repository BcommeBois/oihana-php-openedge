<?php

declare(strict_types=1);

namespace tests\oihana\openedge\db\helpers\functions;

use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\stringExpression;

final class StringExpressionTest extends TestCase
{
    public function testNullInputReturnsNull()
    {
        $this->assertNull(stringExpression(null));
    }

    public function testUnquotedLiteral()
    {
        $this->assertEquals("'unquoted literal'", stringExpression('unquoted literal'));
    }

    public function testSingleQuoteLiteral()
    {
        $this->assertEquals("'''single-quoted literal'''", stringExpression("'single-quoted literal'"));
    }

    public function testSingleQuoteEscaping()
    {
        $this->assertEquals("'O''Hare'", stringExpression("O'Hare"));
    }

    public function testDoubleQuotesAreNotEscaped()
    {
        $this->assertEquals('\'"double-quoted"\'', stringExpression('"double-quoted"'));
    }

    public function testEmptyString()
    {
        $this->assertEquals("''", stringExpression(''));
    }
}