<?php

namespace oihana\openedge\db\traits;

use oihana\enums\Char;
use oihana\exceptions\BindException;
use oihana\openedge\enums\OpenEdge as SQL;
use oihana\traits\QueryIDTrait;
use function oihana\core\strings\wrap;

trait BindTrait
{
    use QueryIDTrait ;

    /**
     * Bind a value to a variable.
     * @param mixed $value
     * @param array $binds
     * @param string|null $to
     * @param string|null $prefix (default ':')
     * @return string
     * @throws BindException
     */
    public function bind
    (
        mixed   $value  ,
        array   &$binds ,
        ?string $to     = null ,
        ?string $prefix = Char::COLON
    )
    :string
    {
        $this->validateBindVariable( $to ) ;
        $to = $this->generateBindVariable( $to ) ;
        $binds[ $to ] = $value ;
        return $this->formatBind( $to , $prefix ) ;
    }

    /**
     * Returns true if the passed-in value is a bind parameter.
     * @param string $value
     * @param string $prefix
     * @return bool
     */
    public function isBindParameter( string $value , string $prefix = Char::COLON ): bool
    {
        if ( preg_match('/^' . $prefix . '?[a-zA-Z0-9][a-zA-Z0-9_]*$/', $value ) )
        {
            return true;
        }
        return false;
    }

    /**
     * Returns a bind expression with the specific variable.
     * @param mixed $value
     * @param array $bindVars
     * @param string|null $to
     * @return array|null
     * @throws BindException
     */
    public function toBindExpression( mixed $value , array &$bindVars , ?string $to = null ):?array
    {
        return [ SQL::BIND => $this->bind( $value , $bindVars , $to , null ) ] ;
    }

    /**
     * Formats the bind variable.
     * @param string $bindVariableName
     * @param string|null $prefix
     * @return string
     */
    protected function formatBind( string $bindVariableName , ?string $prefix = Char::COLON ):string
    {
        if ( isset( $prefix ) && ( $prefix != Char::EMPTY ) && stripos( $bindVariableName , $prefix ) === 0 )
        {
            $bindVariableName = wrap( $bindVariableName ) ;
        }
        return ( is_string( $prefix ) ? $prefix : Char::EMPTY ) . $bindVariableName ;
    }

    /**
     * Generates the bind variable name or use the default name.
     * @param string|null $to
     * @return string
     */
    protected function generateBindVariable( ?string $to = null ): string
    {
        if ( $to == null )
        {
            $to = $this->queryId . Char::UNDERLINE . mt_rand() ;
        }
        return $to;
    }

    /**
     * @throws BindException
     */
    protected function validateBindVariable( ?string $to ): void
    {
        if ( isset($to) && !$this->isBindParameter( $to ) )
        {
            throw new BindException('Invalid bind parameter.') ;
        }
    }
}