<?php

namespace oihana\openedge\models\traits\documents;

use DI\DependencyException;
use DI\NotFoundException;

use oihana\exceptions\BindException;
use oihana\exceptions\ValidationException;
use oihana\enums\http\HttpMethod;
use oihana\openedge\enums\OpenEdge;

trait DocumentsExistTrait
{
    use DocumentsCountTrait ;

    /**
     * Indicates if the passed-in thing exist.
     * @param array $init
     * @param string|null $context
     * @return bool
     * @throws BindException
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function exist( array $init = [] , ?string $context = HttpMethod::get ) :bool
    {
        return $this->count( [ ...$init , OpenEdge::CONTEXT => $init[ OpenEdge::CONTEXT ] ?? $context ] ) > 0 ;
    }
}