<?php

namespace tests\oihana\openedge\db\helpers\functions\numerics;

use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\numerics\abs;
use function oihana\openedge\db\helpers\functions\numerics\acos;
use function oihana\openedge\db\helpers\functions\numerics\asin;
use function oihana\openedge\db\helpers\functions\numerics\atan;
use function oihana\openedge\db\helpers\functions\numerics\atan2;
use function oihana\openedge\db\helpers\functions\numerics\ceiling;
use function oihana\openedge\db\helpers\functions\numerics\cos;
use function oihana\openedge\db\helpers\functions\numerics\degrees;
use function oihana\openedge\db\helpers\functions\numerics\exp;
use function oihana\openedge\db\helpers\functions\numerics\floor;
use function oihana\openedge\db\helpers\functions\numerics\greatest;
use function oihana\openedge\db\helpers\functions\numerics\least;
use function oihana\openedge\db\helpers\functions\numerics\log10;
use function oihana\openedge\db\helpers\functions\numerics\mod;
use function oihana\openedge\db\helpers\functions\numerics\pi;
use function oihana\openedge\db\helpers\functions\numerics\power;
use function oihana\openedge\db\helpers\functions\numerics\radians;
use function oihana\openedge\db\helpers\functions\numerics\rand;
use function oihana\openedge\db\helpers\functions\numerics\round;
use function oihana\openedge\db\helpers\functions\numerics\sign;
use function oihana\openedge\db\helpers\functions\numerics\sin;
use function oihana\openedge\db\helpers\functions\numerics\sqrt;
use function oihana\openedge\db\helpers\functions\numerics\tan;

final class NumericFunctionsTest extends TestCase
{
    public function testAbs(): void
    {
        $this->assertSame('ABS(foo)', abs('foo'));
    }

    public function testAcos(): void
    {
        $this->assertSame('ACOS(foo)', acos('foo'));
    }

    public function testAsin(): void
    {
        $this->assertSame('ASIN(foo)', asin('foo'));
    }

    public function testAtan(): void
    {
        $this->assertSame('ATAN(foo)', atan('foo'));
    }

    public function testAtan2(): void
    {
        $this->assertSame('ATAN2(foo,bar)', atan2('foo', 'bar'));
    }

    public function testCeiling(): void
    {
        $this->assertSame('CEILING(foo)', ceiling('foo'));
    }

    public function testCos(): void
    {
        $this->assertSame('COS(foo)', cos('foo'));
    }

    public function testDegrees(): void
    {
        $this->assertSame('DEGREES(foo)', degrees('foo'));
    }

    public function testExp(): void
    {
        $this->assertSame('EXP(foo)', exp('foo'));
    }

    public function testFloor(): void
    {
        $this->assertSame('FLOOR(foo)', floor('foo'));
    }

    public function testGreatest(): void
    {
        $this->assertSame('GREATEST(foo,bar,baz)', greatest('foo', 'bar', 'baz'));
    }

    public function testLeast(): void
    {
        $this->assertSame('LEAST(foo,bar,baz)', least('foo', 'bar', 'baz'));
    }

    public function testLog10(): void
    {
        $this->assertSame('LOG10(foo)', log10('foo'));
    }

    public function testMod(): void
    {
        $this->assertSame('MOD(foo,bar)', mod('foo', 'bar'));
    }

    public function testPi(): void
    {
        $this->assertSame('PI()', pi());
    }

    public function testPower(): void
    {
        $this->assertSame('POWER(foo,bar)', power('foo', 'bar'));
    }

    public function testRadians(): void
    {
        $this->assertSame('RADIANS(foo)', radians('foo'));
    }

    public function testRand(): void
    {
        $this->assertSame('RAND()', rand());
        $this->assertSame('RAND(foo)', rand('foo'));
    }

    public function testRound(): void
    {
        $this->assertSame('ROUND(foo,2)', round('foo', 2));
    }

    public function testSign(): void
    {
        $this->assertSame('SIGN(foo)', sign('foo'));
    }

    public function testSin(): void
    {
        $this->assertSame('SIN(foo)', sin('foo'));
    }

    public function testSqrt(): void
    {
        $this->assertSame('SQRT(foo)', sqrt('foo'));
    }

    public function testTan(): void
    {
        $this->assertSame('TAN(foo)', tan('foo'));
    }
}
