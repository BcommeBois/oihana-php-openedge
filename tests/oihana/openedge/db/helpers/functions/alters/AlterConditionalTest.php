<?php

namespace tests\oihana\openedge\db\helpers\functions\alters;

use oihana\openedge\db\enums\functions\ConditionalFunction;
use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\alters\alterConditional;

final class AlterConditionalTest extends TestCase
{
    public function testIfNull(): void
    {
        $this->assertSame( "IFNULL(k,'x')", alterConditional( 'k', ConditionalFunction::IFNULL, [ "'x'" ] ) );
    }

    public function testNullIf(): void
    {
        $this->assertSame( 'NULLIF(k,0)', alterConditional( 'k', ConditionalFunction::NULLIF, [ 0 ] ) );
    }

    public function testNullIfEmpty(): void
    {
        $this->assertSame( "NULLIF(k,'')", alterConditional( 'k', ConditionalFunction::NULLIF_EMPTY ) );
    }

    public function testNullIfZero(): void
    {
        $this->assertSame( 'NULLIF(k, 0)', alterConditional( 'k', ConditionalFunction::NULLIF_ZERO ) );
    }

    public function testNvl(): void
    {
        $this->assertSame( 'NVL(k,0)', alterConditional( 'k', ConditionalFunction::NVL, [ '0' ] ) );
    }

    public function testUnknownFunctionReturnsKey(): void
    {
        $this->assertSame( 'k', alterConditional( 'k', 'UNKNOWN' ) );
    }

    public function testNullFunctionReturnsKey(): void
    {
        $this->assertSame( 'k', alterConditional( 'k' ) );
    }
}
