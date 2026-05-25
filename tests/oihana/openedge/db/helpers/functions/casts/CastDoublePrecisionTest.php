<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\casts\castDOUBLE_PRECISION;

final class CastDoublePrecisionTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastDoublePrecision(): void
    {
        $this->assertEquals('CAST(column AS DOUBLE PRECISION)', castDOUBLE_PRECISION('column' ) );
    }
}