<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\casts\castFLOAT;

final class CastFloatTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastFloat(): void
    {
        $this->assertEquals('CAST(column AS FLOAT)', castFLOAT('column' ) );
        $this->assertEquals('CAST(column AS FLOAT(16))', castFLOAT('column' , 16 ) );
    }
}