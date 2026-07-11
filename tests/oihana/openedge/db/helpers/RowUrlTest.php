<?php

namespace tests\oihana\openedge\db\helpers;

use DI\Container;
use oihana\openedge\enums\OpenEdge;
use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\rowUrl;

final class RowUrlTest extends TestCase
{
    private function containerWithBaseUrl( string $baseUrl ): Container
    {
        $container = new Container();
        $container->set( 'baseUrl', $baseUrl );
        return $container;
    }

    public function testConcatsBaseUrlPathAndExpression(): void
    {
        $container  = $this->containerWithBaseUrl( 'https://api.example.com' );
        $expression = [ 'column' => 'products.id' ];

        $this->assertSame(
            [
                OpenEdge::CONCAT => [
                    'https://api.example.com' . 'products' . '/',
                    $expression,
                ],
            ],
            rowUrl( $container, 'products', $expression )
        );
    }

    public function testMissingDefinitionYieldsEmptyBasePath(): void
    {
        $container = new Container(); // no 'baseUrl' defined

        $this->assertSame(
            [
                OpenEdge::CONCAT => [
                    'products/',
                    null,
                ],
            ],
            rowUrl( $container, 'products' )
        );
    }

    public function testCustomDefinitionKey(): void
    {
        $container = new Container();
        $container->set( 'apiRoot', 'https://root.example.com/' );

        $this->assertSame(
            [
                OpenEdge::CONCAT => [
                    'https://root.example.com/' . 'items' . '/',
                    'expr',
                ],
            ],
            rowUrl( $container, 'items', 'expr', 'apiRoot' )
        );
    }

    public function testDefaultsWithEmptyPath(): void
    {
        $container = $this->containerWithBaseUrl( 'https://api.example.com/' );

        $this->assertSame(
            [
                OpenEdge::CONCAT => [
                    'https://api.example.com/' . '' . '/',
                    null,
                ],
            ],
            rowUrl( $container )
        );
    }

    public function testNullDefinitionValueFallsBackToEmpty(): void
    {
        $container = new Container();
        $container->set( 'baseUrl', null );

        $this->assertSame(
            [
                OpenEdge::CONCAT => [
                    'products/',
                    null,
                ],
            ],
            rowUrl( $container, 'products' )
        );
    }
}
