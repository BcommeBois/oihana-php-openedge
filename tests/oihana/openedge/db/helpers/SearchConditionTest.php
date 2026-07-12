<?php

namespace tests\oihana\openedge\db\helpers;

use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\searchCondition;

final class SearchConditionTest extends TestCase
{
    public function testNonEmptyStringIsReturnedAsIs(): void
    {
        $this->assertSame( 'a = 1', searchCondition( 'a = 1' ) );
    }

    public function testEmptyStringReturnsEmpty(): void
    {
        $this->assertSame( '', searchCondition( '' ) );
    }

    public function testNonArrayReturnsEmpty(): void
    {
        $this->assertSame( '', searchCondition( 123 ) );
    }

    public function testEmptyArrayReturnsEmpty(): void
    {
        $this->assertSame( '', searchCondition( [] ) );
    }

    public function testNullReturnsEmpty(): void
    {
        $this->assertSame( '', searchCondition( null ) );
    }
}
