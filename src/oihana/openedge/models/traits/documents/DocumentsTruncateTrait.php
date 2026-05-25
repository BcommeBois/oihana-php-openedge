<?php

namespace oihana\openedge\models\traits\documents;

use oihana\exceptions\UnsupportedOperationException;
use oihana\traits\UnsupportedTrait;

trait DocumentsTruncateTrait
{
    use UnsupportedTrait ;

    /**
     * Truncate a collection and removes all documents.
     * @param array $init
     * @return mixed
     * @throws UnsupportedOperationException
     */
    public function truncate( array $init = [] ) :mixed
    {
        $this->unsupported( __FUNCTION__ ) ;
        return null ;
    }
}