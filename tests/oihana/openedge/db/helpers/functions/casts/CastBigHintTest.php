<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\casts\castBIGINT;

final class CastBigHintTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastBigHint(): void
    {
        $result = castBIGINT('column' );
        $this->assertEquals('CAST(column AS BIGINT)', $result);
    }
}