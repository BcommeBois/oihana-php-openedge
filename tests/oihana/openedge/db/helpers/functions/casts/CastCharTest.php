<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\casts\castCHAR;

final class CastCharTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastChar(): void
    {
        $result = castCHAR('column' , 16 );
        $this->assertEquals('CAST(column AS CHAR(16))', $result);

        $result = castCHAR('column'  );
        $this->assertEquals('CAST(column AS CHAR(1))', $result);
    }

    public function testCastCharRejectsLengthBelowOne(): void
    {
        $this->expectException( \InvalidArgumentException::class );
        castCHAR('column', 0 );
    }

    public function testCastCharRejectsLengthAboveMaximum(): void
    {
        $this->expectException( \InvalidArgumentException::class );
        castCHAR('column', 2001 );
    }
}