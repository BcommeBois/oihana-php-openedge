<?php

namespace oihana\openedge\models\traits\documents;

use oihana\exceptions\UnsupportedOperationException;
use oihana\traits\UnsupportedTrait;

trait DocumentsDeleteTrait
{
    use UnsupportedTrait ;

    /**
     * Delete an item in the model.
     * @param array $init
     * @return null|array|object
     * @throws UnsupportedOperationException
     */
    public function delete( array $init = [] ) :null|array|object
    {
        $this->unsupported( __FUNCTION__ ) ;
    }
}