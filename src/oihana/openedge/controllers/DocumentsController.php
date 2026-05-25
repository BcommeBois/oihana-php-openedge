<?php

namespace oihana\openedge\controllers;

use ReflectionException;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use oihana\controllers\Controller;
use oihana\models\traits\DocumentsTrait;
use oihana\openedge\controllers\traits\DocumentsControllerCountTrait;
use oihana\openedge\controllers\traits\DocumentsControllerGetTrait;
use oihana\openedge\controllers\traits\DocumentsControllerListTrait;

/**
 * The Progress OpenEdge document controller class.
 */
class DocumentsController extends Controller
{
    /**
     * Creates a new DocumentsController instance.
     * @param Container $container
     * @param array $init
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function __construct( Container $container , array  $init = [] )
    {
        parent::__construct( $container , $init );

        $this->initializeForceUrl    ( $init )
             ->initializeHasTotal    ( $init )
             ->initializeLimit       ( $init )
             ->initializeModel       ( $init )
             ->initializeOwner       ( $init )
             ->initializeParams      ( $init )
             ->initializeSortDefault ( $init )
             ->initializeSkins       ( $init ) ;
    }

    use DocumentsTrait ,
        DocumentsControllerCountTrait ,
        DocumentsControllerGetTrait ,
        DocumentsControllerListTrait ;
}