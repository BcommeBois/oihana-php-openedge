<?php

namespace tests\oihana\openedge\db\helpers\functions\strings;

use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\strings\proElement;

final class ProElementTest extends TestCase
{
    public function testExplicitEndPosition(): void
    {
        $this->assertSame( 'PRO_ELEMENT(arr,2,4)', proElement( 'arr', 2, 4 ) );
    }

    public function testNullEndPositionDefaultsToStartPosition(): void
    {
        $this->assertSame( 'PRO_ELEMENT(arr,2,2)', proElement( 'arr', 2, null ) );
    }
}
