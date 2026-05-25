<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\casts\castTIME;

final class CastTimeTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastTime(): void
    {
        $this->assertEquals('CAST(start_column AS TIME)', castTIME('start_column' ) );
    }
}