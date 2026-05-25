<?php

declare(strict_types=1);

namespace tests\oihana\openedge\db\helpers;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;

use oihana\openedge\enums\OpenEdge;

use function oihana\openedge\db\helpers\bindExpression;

final class BindExpressionTest extends TestCase
{
    /**
     * Test that a normal bind key returns the key with a colon.
     *
     * @throws ConstantException
     */
    public function testSimpleBind(): void
    {
        $definition = [OpenEdge::BIND => 'userId'];
        $this->assertSame(':userId', bindExpression($definition));
    }

    /**
     * Test that an empty bind key returns an empty string.
     *
     * @throws ConstantException
     */
    public function testEmptyBind(): void
    {
        $definition = [OpenEdge::BIND => ''];
        $this->assertSame('', bindExpression($definition));

        $definition = [];
        $this->assertSame('', bindExpression($definition));
    }

    /**
     * Test that the function correctly applies alterations via overrideExpression().
     *
     * @throws ConstantException
     */
    public function testBindWithAlter(): void
    {
        $definition = [
            OpenEdge::BIND  => 'price',
            OpenEdge::ALTER => 'ROUND'
        ];
        $result = bindExpression($definition);
        $this->assertStringContainsString('ROUND', $result);
        $this->assertStringContainsString(':price', $result);
    }

    /**
     * Test that multiple alterations are applied via ALTERS.
     *
     * @throws ConstantException
     */
    public function testBindWithAlters(): void
    {
        $definition = [
            OpenEdge::BIND   => 'amount',
            OpenEdge::ALTERS => [
                ['ROUND', 2],
                'ABS'
            ]
        ];
        $result = bindExpression($definition);

        // Should contain both ROUND and ABS wrapping :amount
        $this->assertStringContainsString('ROUND', $result);
        $this->assertStringContainsString('ABS', $result);
        $this->assertStringContainsString(':amount', $result);
    }

    /**
     * Test that invalid alteration does not break and returns original bind with colon.
     *
     * @throws ConstantException
     */
    public function testInvalidAlteration(): void
    {
        $definition = [
            OpenEdge::BIND  => 'value',
            OpenEdge::ALTER => 'INVALID_FUNCTION'
        ];

        // By design, overrideExpression returns the original expression for unsupported functions
        $this->assertSame(':value', bindExpression($definition));
    }
}