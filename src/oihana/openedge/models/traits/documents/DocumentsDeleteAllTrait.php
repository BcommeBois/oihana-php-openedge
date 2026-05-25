<?php

namespace oihana\openedge\models\traits\documents;

use oihana\exceptions\UnsupportedOperationException;
use oihana\traits\UnsupportedTrait;

trait DocumentsDeleteAllTrait
{
    use UnsupportedTrait ;

    /**
     * Delete all items in the model.
     * @param array $init
     * @return object|null
     * @throws UnsupportedOperationException
     */
    public function deleteAll( array $init = [] ) :?object
    {
        $this->unsupported( __FUNCTION__) ;
    }
}