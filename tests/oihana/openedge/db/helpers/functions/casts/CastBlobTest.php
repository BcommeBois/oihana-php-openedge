<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\casts\castBLOB;

final class CastBlobTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastBlob(): void
    {
        $this->assertEquals('CAST(column AS BLOB)', castBLOB('column' ) );
    }
}