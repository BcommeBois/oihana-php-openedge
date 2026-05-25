<?php

namespace tests\oihana\openedge\db\helpers\functions\alters;

use oihana\openedge\db\enums\functions\DateFunction;
use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\alters\alterDate;

final class AlterDateTest extends TestCase
{
    public function testCurDate(): void
    {
        $this->assertEquals('CURDATE()', alterDate('any_column', DateFunction::CURDATE));
    }

    public function testCurTime(): void
    {
        $this->assertEquals('CURTIME()', alterDate('any_column', DateFunction::CURTIME));
    }

    public function testNow(): void
    {
        $this->assertEquals('NOW()', alterDate('any_column', DateFunction::NOW));
    }

    public function testSysDate(): void
    {
        $this->assertEquals('SYSDATE()', alterDate('any_column', DateFunction::SYSDATE));
    }

    public function testSysTime(): void
    {
        $this->assertEquals('SYSTIME()', alterDate('any_column', DateFunction::SYSTIME));
    }

    public function testSysTimestamp(): void
    {
        $this->assertEquals('SYSTIMESTAMP()', alterDate('any_column', DateFunction::SYSTIMESTAMP));
    }

    public function testDefaultReturnsKey(): void
    {
        $this->assertEquals('column_name', alterDate('column_name', null));
        $this->assertEquals('column_name', alterDate('column_name', 'unknown_function'));
    }

    public function testArgsAreIgnored(): void
    {
        // Date functions don't use $args, but the function accepts them
        $this->assertEquals('CURDATE()', alterDate('column', DateFunction::CURDATE, ['ignored']));
        $this->assertEquals('NOW()', alterDate('column', DateFunction::NOW, [1, 2, 3]));
    }
}