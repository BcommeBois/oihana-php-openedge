<?php

namespace tests\oihana\openedge\db\helpers\functions\casts;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\casts\castTIMESTAMP;

final class CastTimeStampTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastTimeStamp(): void
    {
        $this->assertEquals
        (
            'CAST(created_at AS TIMESTAMP)',
            castTIMESTAMP( expression: 'created_at' )
        );

        $this->assertEquals
        (
            'CAST(created_at AS TIMESTAMP WITH TIME ZONE)',
            castTIMESTAMP( expression: 'created_at' , useTimeZone: true )
        );
    }
}