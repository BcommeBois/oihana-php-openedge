<?php

namespace tests\oihana\openedge\integration;

use oihana\openedge\enums\OpenEdge;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Live, read-only smoke test of the `Documents` model against a real Progress
 * OpenEdge database. Only `count()`, `list()` and `get()` (reads) are exercised
 * — mutations are unsupported by design.
 *
 * Runs only via `composer test:integration`; skips when no server / no
 * `[test.documents]` table is configured (see {@see RequiresOpenEdgeTrait}).
 */
#[Group( 'integration' )]
final class DocumentsModelIntegrationTest extends TestCase
{
    use RequiresOpenEdgeTrait;

    public function testCountReturnsANonNegativeInteger(): void
    {
        $count = $this->openEdgeDocuments()->count();

        $this->assertIsInt( $count );
        $this->assertGreaterThanOrEqual( 0, $count );
    }

    public function testListReturnsAtMostTheRequestedNumberOfRows(): void
    {
        $rows = $this->openEdgeDocuments()->list( [ OpenEdge::LIMIT => 5 ] );

        $this->assertIsArray( $rows );
        $this->assertLessThanOrEqual( 5, count( $rows ) );
    }

    public function testGetReturnsAnObjectOrNull(): void
    {
        $thing = $this->openEdgeDocuments()->get();

        $this->assertTrue(
            $thing === null || is_object( $thing ),
            'get() should return a row object or null, never throw against a real table.'
        );
    }

    public function testStreamYieldsRows(): void
    {
        $count = 0;

        foreach ( $this->openEdgeDocuments()->stream( [ OpenEdge::LIMIT => 3 ] ) as $thing )
        {
            $this->assertIsObject( $thing );
            if ( ++$count >= 3 )
            {
                break;
            }
        }

        $this->assertLessThanOrEqual( 3, $count );
    }
}
