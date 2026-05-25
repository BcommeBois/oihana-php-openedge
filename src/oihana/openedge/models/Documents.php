<?php

namespace oihana\openedge\models;

use DI\Container;

use oihana\models\interfaces\DocumentsModel;
use oihana\models\pdo\PDOModel;
use oihana\models\traits\AlterBindVarsTrait;
use oihana\models\traits\CacheableTrait;
use oihana\models\traits\EnsureKeysTrait;
use oihana\openedge\models\traits\documents\DocumentsCountTrait;
use oihana\openedge\models\traits\documents\DocumentsDeleteAllTrait;
use oihana\openedge\models\traits\documents\DocumentsDeleteTrait;
use oihana\openedge\models\traits\documents\DocumentsExistTrait;
use oihana\openedge\models\traits\documents\DocumentsGetTrait;
use oihana\openedge\models\traits\documents\DocumentsInsertTrait;
use oihana\openedge\models\traits\documents\DocumentsLastTrait;
use oihana\openedge\models\traits\documents\DocumentsListTrait;
use oihana\openedge\models\traits\documents\DocumentsReplaceTrait;
use oihana\openedge\models\traits\documents\DocumentsStreamTrait;
use oihana\openedge\models\traits\documents\DocumentsTruncateTrait;
use oihana\openedge\models\traits\documents\DocumentsUpdateTrait;
use oihana\openedge\models\traits\documents\DocumentsUpsertTrait;
use oihana\openedge\models\traits\OpenEdgeHelperTrait;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Represents a collection of OpenEdge "Thing" documents and provides full CRUD operations :
 * list, count, existence checks, and caching support.
 *
 * $docs = new Documents($container, ['someOption' => 'value']);
 * $things    = $docs->list();
 * $something = $docs->get(['id' => '123']);
 * ```
 *
 * @package oihana\openedge\models
 */
class Documents extends PDOModel implements DocumentsModel
{
    /**
     * Creates a new Documents instance.
     * @param Container $container
     * @param array $init The optional properties to passed-in in the model.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct( Container $container , array $init = [] )
    {
        parent::__construct( $container , $init ) ;

        $this->initializeBindVarsAlters       ( $init )
             ->initializeCache                ( $init , $container )
             ->initializeEnsure               ( $init )
             ->initializeOpenEdgeQueryBuilder ( $init , $container ) ;
    }

    use AlterBindVarsTrait ,
        CacheableTrait     ,
        EnsureKeysTrait    ,
        // ----------------
        DocumentsCountTrait ,
        DocumentsDeleteTrait ,
        DocumentsDeleteAllTrait ,
        DocumentsExistTrait ,
        DocumentsGetTrait ,
        DocumentsInsertTrait ,
        DocumentsLastTrait ,
        DocumentsListTrait ,
        DocumentsReplaceTrait ,
        DocumentsStreamTrait ,
        DocumentsTruncateTrait ,
        DocumentsUpdateTrait ,
        DocumentsUpsertTrait ,
        // ----------------
        OpenEdgeHelperTrait ; // ------- Special OpenEdge Methods
}