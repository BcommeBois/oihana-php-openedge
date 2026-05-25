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
use oihana\controllers\traits\ParamsTrait;
use oihana\controllers\traits\prepare\PrepareBench;
use oihana\controllers\traits\prepare\PrepareMock;
use oihana\controllers\traits\PrepareParamTrait;
use oihana\controllers\traits\StatusTrait;
use oihana\enums\Output;
use oihana\models\traits\ModelTrait;
use oihana\openedge\enums\OpenEdge;

trait DocumentsControllerListTrait
{
    use CheckOwnerArgumentsTrait ,
        ForceDocumentUrlTrait ,
        ModelTrait ,
        OutputDocumentsTrait ,
        ParamsTrait ,
        PrepareBench ,
        PrepareMock ,
        PrepareParamTrait ,
        StatusTrait ;

    /**
     * List the documents.
     * @param Request|null  $request
     * @param Response|null $response
     * @param array         $args
     * @param array         $init
     *
     * @return array|null|object
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function list
    (
        ?Request  $request  = null,
        ?Response $response = null ,
            array $args     = [] ,
            array $init     = []
    )
    :array|null|object
    {
        try
        {
            $bindVars   = [ ...( $init[ OpenEdge::BINDS ] ?? [] ) , ...$args ]  ;
            $cacheable  = $init[ OpenEdge::CACHEABLE  ] ?? null ;
            $conditions = $init[ OpenEdge::CONDITIONS ] ?? [] ;
            $options    = $init[ OpenEdge::OPTIONS    ] ?? [] ;
            $params     = $init[ OpenEdge::PARAMS     ] ?? [] ;

            $timestamp = $this->startBench( $request , $init , $params ) ;

            $this->checkOwnerArguments( $args ) ;

            $facets = $this->prepareFacets( $request , $init  , $params ) ;

            // $skin = $this->prepareSkin( $request , $args , $params , Method::list ) ;
            // $this->logger?->debug( 'skin :: ' . json_encode( $skin ) ) ;

            // $facets = [ 'id' => [ '401' , '101' ] ] ;

            $documents = $this->model->list
            ([
                OpenEdge::BINDS      => $bindVars ,
                OpenEdge::CACHEABLE  => $cacheable ,
                OpenEdge::CONDITIONS => $conditions ,
                OpenEdge::FACETS     => $facets ,
                OpenEdge::MOCK       => $this->prepareMock   ( $request , $init , $params ) ,
                OpenEdge::LIMIT      => $this->prepareLimit  ( $request , $init , $params ) ,
                OpenEdge::OFFSET     => $this->prepareOffset ( $request , $init , $params ) ,
                OpenEdge::SORT       => $this->prepareSort   ( $request , $init , $params ) ,
            ]) ;

            // $this->model->clearCache() ; // force to clean the cache.

            if( $this->forceUrl )
            {
                $this->forceDocumentsUrl( $documents , $this->getDocumentUrl( $request ) ) ;
            }

            $hasTotal = $this->prepareHasTotal( $request , $init , $params ) ;
            if( $hasTotal === true )
            {
                $options =
                [
                    ...$options ,
                    Output::TOTAL => $this->model->count
                    ([
                        OpenEdge::BINDS      => $bindVars  ,
                        OpenEdge::CONDITIONS => $conditions ,
                        OpenEdge::FACETS     => $facets ,
                    ])
                ];
            }

            $this->endBench( $timestamp , $options ) ;

            return $this->outputDocuments( $request , $response , $documents , $params , $options ) ;
        }
        catch ( Exception $e )
        {
            return $this->fail( $request , $response , 500 , $e->getMessage() ) ;
        }
    }
}