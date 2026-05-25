<?php

namespace oihana\openedge\db\traits;

use oihana\enums\Char;
use oihana\openedge\db\enums\Clause;
use oihana\openedge\db\enums\LockingHint;
use oihana\openedge\enums\OpenEdge;

use function oihana\core\strings\betweenParentheses;

/**
 * The READPAST locking hint query helper.
 * @example
 * $this->withLockingHint( [ QueryParam::LOCKING_HINT => LockingHint::NOLOCK ] ); // WITH (NOLOCK)
 * $this->withLockingHint( [ QueryParam::LOCKING_HINT => LockingHint::NOWAIT ] );// WITH (READPAST NOWAIT)
 * $this->withLockingHint( [ QueryParam::LOCKING_HINT => 2 ] ); // WITH (READPAST WAIT 2)
 */
trait LookingHintTrait
{
    public int|string|null $lockingHint ;

    public function withLockingHint( array $init = [] ) :string
    {
        $condition   = [];
        $lockingHint = $init[ OpenEdge::LOCKING_HINT ] ?? $this->lockingHint ;

        if( is_int( $lockingHint )  )
        {
            $condition = [ LockingHint::READPAST , LockingHint::WAIT , $lockingHint ] ;
        }
        else if( $lockingHint === LockingHint::NOWAIT )
        {
            $condition = [ LockingHint::READPAST , LockingHint::NOWAIT ] ;
        }
        else if( $lockingHint === LockingHint::NOLOCK )
        {
            $condition = [ LockingHint::NOLOCK ] ;
        }

        return ( count( $condition ) > 0 ) ? Clause::WITH . Char::SPACE . betweenParentheses(  $condition ) : Char::EMPTY ;
    }

    /**
     * Allows a dirty read to occur in the event records are locked by another user.
     * @param bool $flag If true, returns WITH(NOLOCK) else an empty string.
     * @return string
     */
    public function withNoLock( bool $flag = true ):string
    {
        return $flag === true ? LockingHint::WITH_NOLOCK : Char::EMPTY ;
    }
}