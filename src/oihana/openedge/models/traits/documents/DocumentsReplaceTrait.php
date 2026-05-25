<?php

namespace oihana\openedge\models\traits\documents;

use oihana\exceptions\UnsupportedOperationException;
use oihana\traits\UnsupportedTrait;

trait DocumentsReplaceTrait
{
    use UnsupportedTrait ;

    /**
     * Replace a document in the model.
     * @param array $init
     * @return null|array|object
     * @throws UnsupportedOperationException
     */
    public function replace( array $init = [] ) :null|array|object
    {
        $this->unsupported( __FUNCTION__ ) ;
    }
}