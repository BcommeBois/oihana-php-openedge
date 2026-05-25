<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\casts\castCLOB;

final class CastClobTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastClob(): void
    {
        $this->assertEquals('CAST(column AS CLOB)', castCLOB('column' ) );
    }
}