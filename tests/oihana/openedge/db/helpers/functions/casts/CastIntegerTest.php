<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\casts\castINTEGER;

final class CastIntegerTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastInteger(): void
    {
        $this->assertEquals('CAST(column AS INTEGER)', castINTEGER('column' ) );
    }
}