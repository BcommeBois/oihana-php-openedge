<?php

namespace tests\oihana\openedge\db\helpers\functions\dates;

use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\dates\curDate;
use function oihana\openedge\db\helpers\functions\dates\curTime;
use function oihana\openedge\db\helpers\functions\dates\now;
use function oihana\openedge\db\helpers\functions\dates\sysDate;
use function oihana\openedge\db\helpers\functions\dates\sysTime;
use function oihana\openedge\db\helpers\functions\dates\sysTimestamp;

final class DateFunctionsTest extends TestCase
{
    public function testCurDate(): void
    {
        $this->assertSame( 'CURDATE()' , curDate() ) ;
    }

    public function testCurTime(): void
    {
        $this->assertSame( 'CURTIME()' , curTime() ) ;
    }

    public function testNow(): void
    {
        $this->assertSame( 'NOW()' , now() ) ;
    }

    public function testSysDate(): void
    {
        $this->assertSame( 'SYSDATE()' , sysDate() ) ;
    }

    public function testSysTime(): void
    {
        $this->assertSame( 'SYSTIME()' , sysTime() ) ;
    }

    public function testSysTimestamp(): void
    {
        $this->assertSame( 'SYSTIMESTAMP()' , sysTimestamp() ) ;
    }
}