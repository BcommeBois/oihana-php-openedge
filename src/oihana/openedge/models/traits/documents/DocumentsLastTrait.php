<?php

namespace oihana\openedge\models\traits\documents;

use oihana\exceptions\UnsupportedOperationException;
use oihana\traits\UnsupportedTrait;

trait DocumentsLastTrait
{
    use UnsupportedTrait ;

    /**
     * Returns a the last document in the model.
     * @param array $init
     * @return object|null
     * @throws UnsupportedOperationException
     */
    public function last( array $init = [] ) :?object
    {
        $this->unsupported( __FUNCTION__ ) ;
    }
}