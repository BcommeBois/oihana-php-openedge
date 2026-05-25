<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\casts\castDECIMAL;

final class CastDecimalTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastDecimal(): void
    {
        $result = castDECIMAL('column' );
        $this->assertEquals('CAST(column AS DECIMAL(32,0))', $result);

        $result = castDECIMAL('column' , 10 );
        $this->assertEquals('CAST(column AS DECIMAL(10,0))', $result);

        $result = castDECIMAL('column' , 10 , 2 );
        $this->assertEquals('CAST(column AS DECIMAL(10,2))', $result);
    }
}