<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\casts\castVARBINARY;

final class CastVarBinaryTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastVarBinary(): void
    {
        $result = castVARBINARY('column' , 16 );
        $this->assertEquals('CAST(column AS VARBINARY(16))', $result);

        $result = castVARBINARY('column'  );
        $this->assertEquals('CAST(column AS VARBINARY(1))', $result);
    }
}