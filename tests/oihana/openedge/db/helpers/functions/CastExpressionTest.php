<?php

declare(strict_types=1);

namespace tests\oihana\openedge\db\helpers\functions;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\castExpression;

final class CastExpressionTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastExpressionWithSimpleType(): void
    {
        $result = castExpression('username', Type::VARCHAR);
        $this->assertSame('CAST(username AS VARCHAR(1))', $result);
    }

    /**
     * @throws ConstantException
     */
    public function testCastExpressionWithLength(): void
    {
        $result = castExpression('password', [Type::VARBINARY, 255]);
        $this->assertSame('CAST(password AS VARBINARY(255))', $result);
    }

    /**
     * @throws ConstantException
     */
    public function testCastExpressionWithLengthAndScale(): void
    {
        $result = castExpression('price', [Type::DECIMAL, 10, 2]);
        $this->assertSame('CAST(price AS DECIMAL(10,2))', $result);
    }

    /**
     * @throws ConstantException
     */
    public function testCastExpressionWithTimestamp(): void
    {
        $result = castExpression('created_at', [Type::TIMESTAMP]);
        $this->assertSame('CAST(created_at AS TIMESTAMP)', $result);

        $result = castExpression('created_at', [Type::TIMESTAMP_WITH_TIME_ZONE]);
        $this->assertSame('CAST(created_at AS TIMESTAMP WITH TIME ZONE)', $result);
    }

    /**
     * @throws ConstantException
     */
    public function testCastExpressionWithNullDefinition(): void
    {
        $result = castExpression('id', null);
        $this->assertSame('id', $result);
    }

    /**
     * @throws ConstantException
     */
    public function testCastExpressionWithInvalidType(): void
    {
        $result = castExpression('foo', 'INVALID_TYPE');
        $this->assertSame('foo', $result);
    }
}