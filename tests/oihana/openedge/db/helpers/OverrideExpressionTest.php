<?php

declare(strict_types=1);

namespace tests\oihana\openedge\db\helpers;

use PHPUnit\Framework\TestCase;

use oihana\openedge\db\enums\functions\StringFunction;
use oihana\openedge\db\enums\Type;
use oihana\openedge\enums\OpenEdge;
use oihana\reflect\exceptions\ConstantException;

use function oihana\openedge\db\helpers\overrideExpression;
use function oihana\openedge\db\helpers\literal;

final class OverrideExpressionTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testNullExpressionReturnsNull(): void
    {
        $this->assertNull(overrideExpression(null, ['any' => 'value']));
    }

    /**
     * @throws ConstantException
     */
    public function testNullDefinitionReturnsOriginalExpression(): void
    {
        $expr = 'user.age';
        $this->assertSame($expr, overrideExpression($expr, null));
        $this->assertSame($expr, overrideExpression($expr, []));
    }

    /**
     * @throws ConstantException
     */
    public function testCastOnly(): void
    {
        $expr = 'user.age';
        $definition = [OpenEdge::CAST => Type::INTEGER];
        $result = overrideExpression($expr, $definition);
        $this->assertStringContainsString('CAST', $result);
        $this->assertStringContainsString('user.age', $result);
    }
    /**
     * @throws ConstantException
     */

    public function testAlterOnly(): void
    {
        $expr = 'user.name';
        $definition = [OpenEdge::ALTER => StringFunction::UPPER];
        $result = overrideExpression($expr, $definition);
        $this->assertStringContainsString('UPPER', $result);
        $this->assertStringContainsString('user.name', $result);
    }

    /**
     * @throws ConstantException
     */
    public function testAltersOnly(): void
    {
        $expr = 'user.name';
        $definition = [
            OpenEdge::ALTERS => [
                [StringFunction::RPAD, 5, '-'],
                StringFunction::LOWER
            ]
        ];
        $result = overrideExpression($expr, $definition);
        $this->assertMatchesRegularExpression('/LOWER\s*\(\s*RPAD\s*\(\s*user\.name/', $result);
    }

    /**
     * @throws ConstantException
     */
    public function testCastAndAlter(): void
    {
        $expr = 'user.age';
        $definition = [
            OpenEdge::CAST  => Type::INTEGER,
            OpenEdge::ALTER => StringFunction::UPPER
        ];
        $result = overrideExpression($expr, $definition);
        $this->assertStringContainsString('CAST', $result);
        $this->assertStringContainsString('UPPER', $result);
    }

    /**
     * @throws ConstantException
     */
    public function testAllTransformations(): void
    {
        $expr       = 'user.name';
        $definition =
        [
            OpenEdge::CAST   => Type::VARCHAR,
            OpenEdge::ALTER  => StringFunction::RTRIM,
            OpenEdge::ALTERS => [
                [ StringFunction::RPAD , 5 , literal('-') ],
                StringFunction::LOWER
            ]
        ];
        $result = overrideExpression($expr, $definition);

        $this->assertSame( "LOWER(RPAD(RTRIM(CAST(user.name AS VARCHAR(1))),5,'-'))" , $result ) ;
    }

    /**
     * @throws ConstantException
     */
    public function testInvalidFunctionThrowsConstantException(): void
    {
        $this->assertSame( 'user.name' , overrideExpression( 'user.name' , ['INVALID_FUNCTION'] ) ) ;
    }
}