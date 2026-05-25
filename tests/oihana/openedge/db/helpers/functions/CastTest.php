<?php

declare(strict_types=1);

namespace tests\oihana\openedge\db\helpers\functions;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\cast;
use function oihana\openedge\db\helpers\literal;

final class CastTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCast(): void
    {
        $result = cast('hello', Type::VARCHAR , 5 );
        $this->assertEquals('CAST(hello AS VARCHAR(5))', $result);

        $result = cast( literal( 'hello' ) , Type::VARCHAR , 5 );
        $this->assertEquals("CAST('hello' AS VARCHAR(5))", $result);
    }

    public function testThrowConstantException(): void
    {
        $this->expectException(ConstantException::class);
        $this->expectExceptionMessage('Invalid constant : "FOO"');
        cast('hello', 'FOO' , 5 );
    }
}