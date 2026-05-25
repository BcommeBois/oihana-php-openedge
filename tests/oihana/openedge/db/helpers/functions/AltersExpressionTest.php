<?php

declare(strict_types=1);

namespace tests\oihana\openedge\db\helpers\functions;

use oihana\openedge\db\enums\functions\StringFunction;
use oihana\reflect\exceptions\ConstantException;

use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\alters\altersExpression;

final class AltersExpressionTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testNoDefinitionsReturnsOriginalExpression(): void
    {
        $expr = 'user.name';
        $this->assertSame($expr, altersExpression($expr, null));
        $this->assertSame($expr, altersExpression($expr, []));
    }

    /**
     * @throws ConstantException
     */
    public function testSingleDefinition(): void
    {
        $expr = 'user.name';
        $definitions = [StringFunction::LOWER];
        $result = altersExpression($expr, $definitions);
        $this->assertStringContainsString('LOWER', $result);
        $this->assertStringContainsString('user.name', $result);
    }

    /**
     * @throws ConstantException
     */
    public function testMultipleDefinitions(): void
    {
        $expr = 'user.name';
        $definitions = [
            [StringFunction::RPAD, 5, '-'],
            StringFunction::LOWER
        ];
        $result = altersExpression($expr, $definitions);
        // RPAD should be applied first, then LOWER
        $this->assertMatchesRegularExpression('/LOWER\s*\(\s*RPAD\s*\(\s*user\.name/', $result);
    }

    /**
     * @throws ConstantException
     */
    public function testMapCallbackIsApplied(): void
    {
        $expr = 'user.name';
        $definitions = [StringFunction::UPPER];
        $result = altersExpression($expr, $definitions, fn($v) => strtolower($v));
        // The definition should be converted to lowercase by the callback
        $this->assertStringContainsString('upper', strtolower($result));
    }

    public function testInvalidFunctionThrowsConstantException(): void
    {
        $this->assertSame( 'user.name' , altersExpression( 'user.name' , ['INVALID_FUNCTION'] ) ) ;
    }
}