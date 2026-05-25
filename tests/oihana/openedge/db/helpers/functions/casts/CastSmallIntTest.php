<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\casts\castSMALLINT;

final class CastSmallIntTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastSmallInt(): void
    {
        $this->assertEquals('CAST(column AS SMALLINT)', castSMALLINT('column' ) );
    }
}