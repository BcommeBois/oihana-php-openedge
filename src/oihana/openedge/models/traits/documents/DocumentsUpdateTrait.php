<?php

namespace oihana\openedge\models\traits\documents;

use oihana\exceptions\UnsupportedOperationException;
use oihana\traits\UnsupportedTrait;
use org\schema\constants\Schema;

trait DocumentsUpdateTrait
{
    use UnsupportedTrait ;

    /**
     * Updates an item in the model.
     * @param array $init
     * @return object|null
     * @throws UnsupportedOperationException
     */
    public function update( array $init = [] ) :?object
    {
        $this->unsupported( __FUNCTION__ ) ;
    }

    /**
     * Update a single date property in a document with the current date.
     * By default, it updates the `modified` property with the current timestamp.
     *
     * @param array $init      Additional options like binds, return clause, etc.
     * @param string $property The document property to update (default: Schema::MODIFIED).
     *
     * @return object|null The updated document.
     *
     * @throws UnsupportedOperationException
     */
    public function updateDate
    (
        array   $init     = [] ,
        string  $property = Schema::MODIFIED
    )
    : ?object
    {
        $this->unsupported( __FUNCTION__ ) ;
    }
}