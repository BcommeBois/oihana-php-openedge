<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\casts\castDATE;

final class CastDateTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastDate(): void
    {
        $this->assertEquals('CAST(column AS DATE)', castDATE('column' ) );
    }
}