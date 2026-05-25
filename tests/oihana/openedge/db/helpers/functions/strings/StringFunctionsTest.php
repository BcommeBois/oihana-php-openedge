<?php

namespace tests\oihana\openedge\db\helpers\functions\strings;

use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\strings\ascii;
use function oihana\openedge\db\helpers\functions\strings\char;
use function oihana\openedge\db\helpers\functions\strings\chr;
use function oihana\openedge\db\helpers\functions\strings\concat;
use function oihana\openedge\db\helpers\functions\strings\difference;
use function oihana\openedge\db\helpers\functions\strings\initCap;
use function oihana\openedge\db\helpers\functions\strings\insertInString;
use function oihana\openedge\db\helpers\functions\strings\inString;
use function oihana\openedge\db\helpers\functions\strings\lcase;
use function oihana\openedge\db\helpers\functions\strings\left;
use function oihana\openedge\db\helpers\functions\strings\length;
use function oihana\openedge\db\helpers\functions\strings\locate;
use function oihana\openedge\db\helpers\functions\strings\lower;
use function oihana\openedge\db\helpers\functions\strings\lpad;
use function oihana\openedge\db\helpers\functions\strings\ltrim;
use function oihana\openedge\db\helpers\functions\strings\prefix;
use function oihana\openedge\db\helpers\functions\strings\proArrayDescape;
use function oihana\openedge\db\helpers\functions\strings\proArrayEscape;
use function oihana\openedge\db\helpers\functions\strings\proElement;
use function oihana\openedge\db\helpers\functions\strings\repeat;
use function oihana\openedge\db\helpers\functions\strings\replace;
use function oihana\openedge\db\helpers\functions\strings\right;
use function oihana\openedge\db\helpers\functions\strings\rpad;
use function oihana\openedge\db\helpers\functions\strings\rtrim;
use function oihana\openedge\db\helpers\functions\strings\substr;
use function oihana\openedge\db\helpers\functions\strings\substring;
use function oihana\openedge\db\helpers\functions\strings\suffix;
use function oihana\openedge\db\helpers\functions\strings\translate;
use function oihana\openedge\db\helpers\functions\strings\ucase;
use function oihana\openedge\db\helpers\functions\strings\upper;

final class StringFunctionsTest extends TestCase
{
    public function testAscii(): void
    {
        $this->assertSame('ASCII(foo)', ascii('foo'));
    }

    public function testChar(): void
    {
        $this->assertSame('CHAR(65)', char(65));
    }

    public function testChr(): void
    {
        $this->assertSame('CHR(65)', chr(65));
    }

    public function testConcat(): void
    {
        $this->assertSame('CONCAT(foo,bar)', concat('foo', 'bar'));
    }

    public function testDifference(): void
    {
        $this->assertSame('DIFFERENCE(foo,bar)', difference('foo', 'bar'));
    }

    public function testInitCap(): void
    {
        $this->assertSame('INITCAP(foo)', initCap('foo'));
    }

    public function testInsertInString(): void
    {
        $this->assertSame('INSERT(foo,1,2,bar)', insertInString('foo', 1, 2, 'bar'));
    }

    public function testInString(): void
    {
        $this->assertSame('INSTR(foo,bar,1,1)', inString('foo', 'bar'));
    }

    public function testLcase(): void
    {
        $this->assertSame('LCASE(foo)', lcase('foo'));
    }

    public function testLeft(): void
    {
        $this->assertSame('LEFT(foo,3)', left('foo', 3));
    }

    public function testLength(): void
    {
        $this->assertSame('LENGTH(foo)', length('foo'));
    }

    public function testLocate(): void
    {
        $this->assertSame('LOCATE(foo,bar,1)', locate('foo', 'bar', 1));
    }

    public function testLower(): void
    {
        $this->assertSame('LOWER(foo)', lower('foo'));
    }

    public function testLpad(): void
    {
        $this->assertSame('LPAD(foo,5,bar)', lpad('foo', 5, 'bar'));
    }

    public function testLtrim(): void
    {
        $this->assertSame('LTRIM(foo,bar)', ltrim('foo', 'bar'));
    }

    public function testPrefix(): void
    {
        $this->assertSame('PREFIX(foo,1,bar)', prefix('foo', 1, 'bar'));
    }

    public function testProArrayDescape(): void
    {
        $this->assertSame('PRO_ARR_DESCAPE(foo)', proArrayDescape('foo'));
    }

    public function testProArrayEscape(): void
    {
        $this->assertSame('PRO_ARR_ESCAPE(foo)', proArrayEscape('foo'));
    }

    public function testProElement(): void
    {
        $this->assertSame('PRO_ELEMENT(foo,1,2)', proElement('foo', 1, 2));
    }

    public function testRepeat(): void
    {
        $this->assertSame('REPEAT(foo,3)', repeat('foo', 3));
    }

    public function testReplace(): void
    {
        $this->assertSame('REPLACE(foo,bar,baz)', replace('foo', 'bar', 'baz'));
    }

    public function testRight(): void
    {
        $this->assertSame('RIGHT(foo,3)', right('foo', 3));
    }

    public function testRpad(): void
    {
        $this->assertSame('RPAD(foo,5,bar)', rpad('foo', 5, 'bar'));
    }

    public function testRtrim(): void
    {
        $this->assertSame('RTRIM(foo,bar)', rtrim('foo', 'bar'));
    }

    public function testSubstr(): void
    {
        $this->assertSame('SUBSTR(foo,1,3)', substr('foo', 1, 3));
    }

    public function testSubstring(): void
    {
        $this->assertSame('SUBSTRING(foo,1,3)', substring('foo', 1, 3));
    }

    public function testSuffix(): void
    {
        $this->assertSame('SUFFIX(foo,1,bar)', suffix('foo', 1, 'bar'));
    }

    public function testTranslate(): void
    {
        $this->assertSame('TRANSLATE(foo,bar,baz)', translate('foo', 'bar', 'baz'));
    }

    public function testUcase(): void
    {
        $this->assertSame('UCASE(foo)', ucase('foo'));
    }

    public function testUpper(): void
    {
        $this->assertSame('UPPER(foo)', upper('foo'));
    }
}
