<?php

namespace oihana\openedge\models\traits\documents;

use oihana\exceptions\UnsupportedOperationException;
use oihana\traits\UnsupportedTrait;

trait DocumentsUpsertTrait
{
    use UnsupportedTrait ;

    /**
     * Upsert a document into the collection.
     * @param array $init
     * @return null|array|object
     * @throws UnsupportedOperationException
     */
    public function upsert( array $init = [] ) :null|array|object
    {
        $this->unsupported( __FUNCTION__ ) ;
    }
}