<?php

namespace oihana\openedge\db;

use PDO;

use oihana\traits\ToStringTrait;

class OpenEdgePDOBuilder
{
    /**
     * Creates a new OpenEdgePDOBuilder instance.
     * @param array $init The settings definition.
     */
    public function __construct( array $init = [] )
    {
        $this->dsn      = new OpenEdgeDSN( $init ) ;
        $this->logonID  = $init[ self::LOGON_ID ] ?? null ;
        $this->password = $init[ self::PASSWORD ] ?? null ;
    }

    public const string LOGON_ID = 'logonID' ;
    public const string PASSWORD = 'password' ;

    use ToStringTrait ;

    /**
     * The dsn definition.
     * @var OpenEdgeDSN
     */
    public OpenEdgeDSN $dsn ;

    /**
     * The user identifier.
     * @var ?string
     */
    public ?string $logonID ;

    /**
     * The user password.
     * @var ?string
     */
    public ?string $password ;

    /**
     * Returns a PDO instance.
     * @return ?PDO
     */
    public function __invoke(): ?PDO
    {
        $pdo = new PDO( $this->dsn->__toString() , $this->logonID , $this->password ) ;
        $pdo->setAttribute (PDO::ATTR_DEFAULT_FETCH_MODE , PDO::FETCH_ASSOC ) ;
        $pdo->setAttribute (PDO::ATTR_ERRMODE            , PDO::ERRMODE_EXCEPTION ) ;
        $pdo->setAttribute (PDO::ATTR_CURSOR             , PDO::CURSOR_FWDONLY ) ;
        $pdo->setAttribute (PDO::ATTR_PERSISTENT         , true ) ;
        $pdo->setAttribute (PDO::ATTR_EMULATE_PREPARES   , false ) ;
        $pdo->setAttribute (PDO::ATTR_STRINGIFY_FETCHES  , false ) ;
        return $pdo ;
    }
}