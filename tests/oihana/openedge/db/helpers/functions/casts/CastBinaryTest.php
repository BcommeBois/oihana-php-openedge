<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\casts\castBINARY;

final class CastBinaryTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastBinary(): void
    {
        $result = castBINARY('column' , 16 );
        $this->assertEquals('CAST(column AS BINARY(16))', $result);

        $result = castBINARY('column'  );
        $this->assertEquals('CAST(column AS BINARY(1))', $result);
    }
}