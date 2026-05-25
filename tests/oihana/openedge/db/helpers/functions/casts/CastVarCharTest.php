<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\casts\castVARCHAR;

final class CastVarCharTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastVarChar(): void
    {
        $result = castVARCHAR('column' , 16 );
        $this->assertEquals('CAST(column AS VARCHAR(16))', $result);

        $result = castVARCHAR('column'  );
        $this->assertEquals('CAST(column AS VARCHAR(1))', $result);
    }
}