<?php

namespace tests\oihana\openedge\db\helpers;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;

use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\openEdgeType;

final class OpenEdgeTypeTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testTypeWithoutArgs(): void
    {
        $this->assertSame('INTEGER', openEdgeType(Type::INTEGER));
    }

    /**
     * @throws ConstantException
     */
    public function testTypeWithSingleArg(): void
    {
        $this->assertSame('VARCHAR(5)', openEdgeType(Type::VARCHAR, 5));
    }

    /**
     * @throws ConstantException
     */
    public function testTypeWithMultipleArgs(): void
    {
        $this->assertSame('DECIMAL(10,2)', openEdgeType(Type::DECIMAL, [10, 2]));
    }

    public function testInvalidTypeThrowsException(): void
    {
        $this->expectException(ConstantException::class);
        $this->expectExceptionMessage('Invalid constant : "FOO"');

        openEdgeType('FOO'); // type invalide
    }
}