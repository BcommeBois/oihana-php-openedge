<?php

namespace oihana\openedge\controllers\traits;

use Exception;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use oihana\controllers\traits\CheckOwnerArgumentsTrait;
use oihana\controllers\traits\prepare\PrepareBench;
use oihana\controllers\traits\prepare\PrepareMock;
use oihana\controllers\traits\PrepareParamTrait;
use oihana\controllers\traits\StatusTrait;
use oihana\enums\Output;
use oihana\models\traits\ModelTrait;
use oihana\openedge\enums\OpenEdge;

trait DocumentsControllerCountTrait
{
    use CheckOwnerArgumentsTrait ,
        ModelTrait ,
        PrepareBench ,
        PrepareMock ,
        PrepareParamTrait ,
        StatusTrait ;

    /**
     * Count the number of documents.
     * @param Request|null $request
     * @param Response|null $response
     * @param array $args An associative array that contains values for the current route’s named placeholders.
     * @param array $init An associative array to extends the method behaviors
     * @return Response|int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function count( ?Request $request = null, ?Response $response = null , array $args = []  , array $init = [] ) :Response|int
    {
        try
        {
            $bindVars   = [ ...( $init[ OpenEdge::BINDS ] ?? [] ) , ...$args ]  ;
            $conditions = $init[ OpenEdge::CONDITIONS ] ?? [] ;
            $params     = $init[ OpenEdge::PARAMS  ] ?? [] ;
            $options    = $init[ OpenEdge::OPTIONS ] ?? [] ;

            if( $response )
            {
                $timestamp = $this->startBench( $request , $init , $params ) ;
            }

            $this->checkOwnerArguments( $args ) ;

            $count = $this->model->count
            ([
                OpenEdge::BINDS      => $bindVars ,
                OpenEdge::CONDITIONS => $conditions ,
                OpenEdge::FACETS     => $this->prepareFacets ( $request , $init , $params ) ,
                OpenEdge::MOCK       => $this->prepareMock   ( $request , $init ,$params  ) ,
            ]) ;

            if( $response )
            {
                $this->endBench( $timestamp , $options ) ;

                return $this->success( $request , $response , $count ,
                [
                    Output::OPTIONS => $options ,
                    Output::PARAMS  => $params
                ] ) ;
            }

            return $count ;
        }
        catch ( Exception $e )
        {
            return $this->fail
            (
                $request ,
                $response ,
                $e->getCode() ?? 500 ,
                $e->getMessage()
            ) ;
        }
    }
}