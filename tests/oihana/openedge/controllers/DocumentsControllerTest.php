<?php

namespace tests\oihana\openedge\controllers;

use DI\Container;
use oihana\models\interfaces\DocumentsModel;
use oihana\openedge\controllers\DocumentsController;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;

final class DocumentsControllerTest extends TestCase
{
    private function controller( DocumentsModel $model, array $extra = [] ): DocumentsController
    {
        $app = AppFactory::create();

        return new DocumentsController( new Container(), [
            'app'    => $app,
            'router' => $app->getRouteCollector()->getRouteParser(),
            'model'  => $model,
            ...$extra,
        ] );
    }

    private function request()
    {
        return ( new ServerRequestFactory() )->createServerRequest( 'GET', '/things' );
    }

    // --- count -------------------------------------------------------------

    public function testCountWithoutResponseReturnsModelCount(): void
    {
        $model = $this->createStub( DocumentsModel::class );
        $model->method( 'count' )->willReturn( 42 );

        $this->assertSame( 42, $this->controller( $model )->count( null, null, [], [] ) );
    }

    public function testCountWithResponseReturnsSuccessResponse(): void
    {
        $model = $this->createStub( DocumentsModel::class );
        $model->method( 'count' )->willReturn( 42 );

        $result = $this->controller( $model )->count( $this->request(), new Response(), [], [] );

        $this->assertInstanceOf( ResponseInterface::class, $result );
        $this->assertSame( 200, $result->getStatusCode() );
    }

    public function testCountFailureReturnsFailResponseWithExceptionCode(): void
    {
        // count()'s catch forwards $e->getCode() (the `?? 500` only guards null,
        // not code 0), so the thrown code must be a valid HTTP status.
        $model = $this->createStub( DocumentsModel::class );
        $model->method( 'count' )->willThrowException( new \RuntimeException( 'boom', 503 ) );

        $result = $this->controller( $model )->count( $this->request(), new Response(), [], [] );

        $this->assertInstanceOf( ResponseInterface::class, $result );
        $this->assertSame( 503, $result->getStatusCode() );
    }

    // --- get ---------------------------------------------------------------

    public function testGetReturnsDocument(): void
    {
        $document = (object) [ 'id' => 7 ];
        $model    = $this->createStub( DocumentsModel::class );
        $model->method( 'get' )->willReturn( $document );

        $result = $this->controller( $model )->get( null, null, [], [] );

        $this->assertSame( $document, $result );
    }

    public function testGetForcesTheDocumentUrlWhenEnabled(): void
    {
        $model = $this->createStub( DocumentsModel::class );
        $model->method( 'get' )->willReturn( (object) [ 'id' => 7 ] );

        $result = $this->controller( $model, [ 'forceUrl' => true ] )
            ->get( $this->request(), null, [], [] );

        $this->assertIsObject( $result );
        $this->assertObjectHasProperty( 'url', $result );
    }

    public function testGetReturnsFailResponseOnModelException(): void
    {
        $model = $this->createStub( DocumentsModel::class );
        $model->method( 'get' )->willThrowException( new \RuntimeException( 'boom' ) );

        $result = $this->controller( $model )->get( $this->request(), new Response(), [], [] );

        $this->assertInstanceOf( ResponseInterface::class, $result );
        $this->assertSame( 500, $result->getStatusCode() );
    }

    // --- list --------------------------------------------------------------

    public function testListReturnsDocuments(): void
    {
        $documents = [ (object) [ 'id' => 1 ], (object) [ 'id' => 2 ] ];
        $model     = $this->createStub( DocumentsModel::class );
        $model->method( 'list' )->willReturn( $documents );

        $result = $this->controller( $model )->list( null, null, [], [] );

        $this->assertSame( $documents, $result );
    }

    public function testListForcesDocumentUrlsWhenEnabled(): void
    {
        $documents = [ (object) [ 'id' => 1 ] ];
        $model     = $this->createStub( DocumentsModel::class );
        $model->method( 'list' )->willReturn( $documents );

        $result = $this->controller( $model, [ 'forceUrl' => true ] )
            ->list( $this->request(), null, [], [] );

        $this->assertIsArray( $result );
        $this->assertObjectHasProperty( 'url', $result[ 0 ] );
    }

    public function testListReturnsFailResponseOnModelException(): void
    {
        $model = $this->createStub( DocumentsModel::class );
        $model->method( 'list' )->willThrowException( new \RuntimeException( 'boom' ) );

        $result = $this->controller( $model )->list( $this->request(), new Response(), [], [] );

        $this->assertInstanceOf( ResponseInterface::class, $result );
        $this->assertSame( 500, $result->getStatusCode() );
    }
}
