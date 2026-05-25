<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\casts\castBIT;

final class CastBitTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastBit(): void
    {
        $this->assertEquals('CAST(column AS BIT)', castBIT('column' ) );
        $this->assertEquals('CAST(1 AS BIT)', castBIT(1 ) );
    }
}