<?php

namespace oihana\openedge\controllers\traits;

use Exception;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use oihana\controllers\traits\CheckOwnerArgumentsTrait;
use oihana\controllers\traits\ForceDocumentUrlTrait;
use oihana\controllers\traits\OutputDocumentsTrait;
use oihana\controllers\traits\prepare\PrepareBench;
use oihana\controllers\traits\prepare\PrepareMock;
use oihana\controllers\traits\PrepareParamTrait;
use oihana\controllers\traits\StatusTrait;
use oihana\enums\Output;
use oihana\models\traits\ModelTrait;
use oihana\openedge\enums\OpenEdge;

trait DocumentsControllerGetTrait
{
    use CheckOwnerArgumentsTrait ,
        ForceDocumentUrlTrait    ,
        ModelTrait               ,
        OutputDocumentsTrait     ,
        PrepareBench             ,
        PrepareMock              ,
        PrepareParamTrait        ,
        StatusTrait              ;

    /**
     * Returns a specific document with a specific identifier.
     * @param Request|null $request
     * @param Response|null $response
     * @param array $args An associative array that contains values for the current route’s named placeholders.
     * @param array $init
     * @return object|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function get
    (
        ?Request  $request  = null ,
        ?Response $response = null ,
        array     $args     = []   ,
        array     $init     = []
    )
    :?object
    {
        try
        {
            $binds   = [ ...( $init[ OpenEdge::BINDS ] ?? [] ) , ...$args ] ;
            $options = $init[ Output::OPTIONS ] ?? [] ;
            $params  = $init[ Output::PARAMS  ] ?? [] ;
            $mock    = $this->prepareMock( $request , $init , $params ) ;

            $timestamp  = $this->startBench( $request , $init , $params ) ;
            // $skin = $this->prepareSkin( $request , $init , $params , Method::get ) ; TODO implement it

            $this->checkOwnerArguments( $args ) ;

            $document = $this->model->get
            ([
                ...$init ,
                OpenEdge::BINDS => $binds ,
                OpenEdge::MOCK  => $mock ,
            ]) ;

            if( $document )
            {
                if( $this->forceUrl )
                {
                    $this->forceDocumentUrl( $document , $this->getDocumentUrl( $request ) ) ;
                }
            }

            $this->endBench( $timestamp , $options ) ;

            return $this->success( $request , $response , $document , [ Output::OPTIONS => $options ] ) ;
        }
        catch ( Exception $e )
        {
            return $this->fail( $request , $response , 500 , $e->getMessage() ) ;
        }
    }
}