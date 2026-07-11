<?php

namespace tests\oihana\openedge\db\helpers;

use oihana\enums\Char;
use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\parseExtent;

final class ParseExtentTest extends TestCase
{
    public function testNullValueReturnsEmpty(): void
    {
        $this->assertSame( Char::EMPTY, parseExtent( null ) );
    }

    public function testEmptyStringReturnsEmpty(): void
    {
        $this->assertSame( Char::EMPTY, parseExtent( Char::EMPTY ) );
    }

    public function testDefaultIndexReturnsFirstElement(): void
    {
        $this->assertSame( '5', parseExtent( '5;0;0;0' ) );
    }

    public function testExplicitIndexReturnsElement(): void
    {
        $this->assertSame( '0', parseExtent( '5;0;0;0', 1 ) );
        $this->assertSame( '7', parseExtent( '5;0;7;0', 2 ) );
        $this->assertSame( '3', parseExtent( '5;0;0;3', 3 ) );
    }

    public function testElementIsTrimmed(): void
    {
        $this->assertSame( '5', parseExtent( ' 5 ; 0 ; 0 ' ) );
        $this->assertSame( '0', parseExtent( ' 5 ; 0 ; 0 ', 1 ) );
    }

    public function testIndexOutOfRangeReturnsEmpty(): void
    {
        $this->assertSame( Char::EMPTY, parseExtent( '5;0;0;0', 4 ) );
        $this->assertSame( Char::EMPTY, parseExtent( '5;0;0;0', 99 ) );
    }

    public function testNegativeIndexReturnsEmpty(): void
    {
        $this->assertSame( Char::EMPTY, parseExtent( '5;0;0;0', -1 ) );
    }

    public function testSingleElementWithoutSeparator(): void
    {
        $this->assertSame( '42', parseExtent( '42' ) );
        $this->assertSame( Char::EMPTY, parseExtent( '42', 1 ) );
    }

    public function testEmptyElementBetweenSeparators(): void
    {
        $this->assertSame( Char::EMPTY, parseExtent( '5;;0', 1 ) );
    }

    public function testTextualElements(): void
    {
        $this->assertSame( 'foo', parseExtent( 'foo;bar;baz' ) );
        $this->assertSame( 'bar', parseExtent( 'foo;bar;baz', 1 ) );
        $this->assertSame( 'baz', parseExtent( 'foo;bar;baz', 2 ) );
    }
}
