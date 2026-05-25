<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use InvalidArgumentException;
use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\casts\castLVARBINARY;

final class CastLVarBinaryTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastLVarBinary(): void
    {
        $result = castLVARBINARY('column' );
        $this->assertEquals('CAST(column AS LVARBINARY(256))', $result);

        $result = castLVARBINARY('column' ,  256 );
        $this->assertEquals('CAST(column AS LVARBINARY(256))', $result);

        $result = castLVARBINARY('column' , 512 );
        $this->assertEquals('CAST(column AS LVARBINARY(512))', $result);
    }

    /**
     * @throws ConstantException
     */
    public function testCastLVarBinaryThrowsExceptionForSmallLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('LVARBINARY length must be at least 256 bytes.');

        castLVARBINARY('column', 128);
    }
}