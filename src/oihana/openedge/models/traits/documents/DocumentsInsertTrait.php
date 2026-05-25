<?php

namespace oihana\openedge\models\traits\documents;

use oihana\exceptions\UnsupportedOperationException;
use oihana\traits\UnsupportedTrait;

trait DocumentsInsertTrait
{
    use UnsupportedTrait ;

    /**
     * Insert a new document into the model.
     * @param array $init
     * @return object|null
     * @throws UnsupportedOperationException
     */
    public function insert( array $init = [] ) :?object
    {
        $this->unsupported( __FUNCTION__ ) ;
    }
}