<?php

namespace tests\oihana\openedge\db\helpers\cases;

use oihana\openedge\enums\OpenEdge;
use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\cases\whenExpression;

final class WhenExpressionTest extends TestCase
{
    public function testMissingWhenReturnsEmpty(): void
    {
        $this->assertSame( '', whenExpression( [] ) );
    }

    public function testArrayWhenResultIsCompiled(): void
    {
        // A $map returning an array exercises the `is_array($whenResult)` branch,
        // where the parts are compiled (space-joined) before assembly.
        $definition = [ OpenEdge::WHEN => 'x', OpenEdge::THEN => 'y' ];
        $result     = whenExpression( $definition, true, fn( ...$args ) => [ 'A', 'B' ] );

        $this->assertSame( 'WHEN A B THEN A B', $result );
    }
}
