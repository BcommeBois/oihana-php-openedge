<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\casts\castTINYINT;

final class CastTinyIntTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastTinyInt(): void
    {
        $this->assertEquals('CAST(column AS TINYINT)', castTINYINT('column' ) );
        $this->assertEquals('CAST(1 AS TINYINT)', castTINYINT(1 ) );
    }
}