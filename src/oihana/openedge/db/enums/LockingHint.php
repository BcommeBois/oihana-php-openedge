<?php

namespace oihana\openedge\db\enums;

use oihana\reflect\traits\ConstantsTrait;

/**
 * This option causes a transaction to skip rows locked by other transactions that would ordinarily appear in the result set, rather than block the transaction waiting for the other transactions to release their locks on these rows.
 * @see https://docs.progress.com/fr-FR/bundle/openedge-sql-development-117/page/The-READPAST-locking-hint.html
 */
class LockingHint
{
    use ConstantsTrait ;

    /**
     * The NOLOCK option.
     */
    const string NOLOCK = 'NOLOCK' ;

    /**
     * The NOWAIT option.
     */
    const string NOWAIT = 'NOWAIT' ;

    /**
     * The READPAST clause.
     */
    const string READPAST = 'READPAST' ;

    /**
     * The 'WAIT' option.
     */
    const string WAIT = 'WAIT' ;

    /**
     * The WITH(NOLOCK) option.
     */
    const string WITH_NOLOCK = 'WITH (NOLOCK)' ;
}
