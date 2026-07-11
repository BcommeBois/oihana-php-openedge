<?php

namespace tests\oihana\openedge\integration;

use DI\Container;
use oihana\openedge\controllers\DocumentsController;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;

/**
 * Live, read-only smoke test of `DocumentsController` end to end: a real Slim
 * app, a real `Documents` model wired on the live PDO, and the cheap read
 * actions called without a PSR-7 response so they return the raw model values.
 *
 * `count()` (a single COUNT) and `get()` (a single fetched row) are used on
 * purpose — `list()` is left to the model test, where it is bounded via
 * `OpenEdge::LIMIT`, to avoid an unbounded scan of a real ERP table here.
 *
 * Runs only via `composer test:integration`; skips when no server / no
 * `[test.documents]` table is configured (see {@see RequiresOpenEdgeTrait}).
 */
#[Group( 'integration' )]
final class DocumentsControllerIntegrationTest extends TestCase
{
    use RequiresOpenEdgeTrait;

    private function controller(): DocumentsController
    {
        $app = AppFactory::create();

        return new DocumentsController( new Container(), [
            'app'    => $app,
            'router' => $app->getRouteCollector()->getRouteParser(),
            'model'  => $this->openEdgeDocuments(),
        ] );
    }

    public function testCountReturnsANonNegativeInteger(): void
    {
        $count = $this->controller()->count( null, null, [], [] );

        $this->assertIsInt( $count );
        $this->assertGreaterThanOrEqual( 0, $count );
    }

    public function testGetReturnsAnObjectOrNull(): void
    {
        $thing = $this->controller()->get( null, null, [], [] );

        $this->assertTrue( $thing === null || is_object( $thing ) );
    }
}
