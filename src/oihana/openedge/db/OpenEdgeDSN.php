<?php

namespace oihana\openedge\db;

use oihana\enums\Char;
use oihana\enums\CharacterSet;

class OpenEdgeDSN
{
    /**
     * Creates a new OpenEdgeDSN instance.
     * @param array $init The init object to defines the DSN expression.
     */
    public function __construct( array $init = [] )
    {
        $this->arraySize              = $init[ self::CONFIG_ARRAY_SIZE                  ] ?? $this->arraySize ;
        $this->charSet                = $init[ self::CONFIG_CHAR_SET                    ] ?? $this->charSet ;
        $this->database               = $init[ self::CONFIG_DATABASE                    ] ?? null ;
        $this->defaultLongDataBuffLen = $init[ self::CONFIG_DEFAULT_LONG_DATA_BUFF_LEN ] ?? $this->defaultLongDataBuffLen ;
        $this->driver                 = $init[ self::CONFIG_DRIVER                      ] ?? null ;
        $this->hostName               = $init[ self::CONFIG_HOST_NAME                   ] ?? null ;
        $this->portNumber             = $init[ self::CONFIG_PORT_NUMBER                 ] ?? null ;
        $this->queryTimeout           = $init[ self::CONFIG_QUERY_TIMEOUT               ] ?? null ;
        $this->scheme                 = $init[ self::CONFIG_SCHEME                      ] ?? null ;
    }

    const string ARRAY_SIZE                 = 'ArraySize' ;
    const string CHAR_SET                   = 'IANAAppCodePage' ;
    const string DATABASE                   = 'Database'   ;
    const string DEFAULT_LONG_DATA_BUFF_LEN = 'DefaultLongDataBuffLen' ;
    const string DRIVER                     = 'Driver'     ;
    const string HOST_NAME                  = 'HostName'   ;
    const string PORT_NUMBER                = 'PortNumber' ;
    const string QUERY_TIMEOUT              = 'QueryTimeout' ;

    const string CONFIG_ARRAY_SIZE                 = 'arraySize' ;
    const string CONFIG_CHAR_SET                   = 'charSet' ;
    const string CONFIG_DATABASE                   = 'database' ;
    const string CONFIG_DEFAULT_LONG_DATA_BUFF_LEN = 'defaultLongDataBuffLen' ;
    const string CONFIG_DRIVER                     = 'driver' ;
    const string CONFIG_HOST_NAME                  = 'hostName' ;
    const string CONFIG_PORT_NUMBER                = 'portNumber' ;
    const string CONFIG_QUERY_TIMEOUT              = 'queryTimeout' ;
    const string CONFIG_SCHEME                     = 'scheme' ;

    /**
     * The number of cells the driver retrieves from a server for a fetch.
     * @var ?int
     */
    public ?int $arraySize = null ;

    /**
     * The charSet of the DSN expression.
     * @var int
     */
    public int $charSet = CharacterSet::UTF8 ;

    /**
     * The database name.
     * @var ?string
     */
    public ?string $database = null ;

    /**
     * An integer in multiples of 1024 (default 1024).
     * The maximum length of data (in KB) the driver can fetch from long columns in a single round trip and the maximum length of data that the driver can send using the SQL_DATA_AT_EXEC parameter.
     * @var int|null
     */
    public ?int $defaultLongDataBuffLen = null ;

    /**
     * The driver expression.
     * @var ?string
     */
    public ?string $driver = null ;

    /**
     * The hostname expression.
     * @var ?string
     */
    public ?string $hostName = null ;

    /**
     * The port number.
     * @var string|int|null
     */
    public string|int|null $portNumber = null ;

    /**
     * The number of seconds for the default query timeout for all statements that are created by a connection.
     * Valid values : -1 | 0 | x
     * If set to -1, the query does not timeout.The driver silently ignores the SQL_ATTR_QUERY_TIMEOUT attribute.
     * If set to -0, the query does not timeout, but the driver responds to the SQL_ATTR_QUERY_TIMEOUT attribute.
     * If set to x, all queries time out after the specified number of seconds.
     * @var ?int
     */
    public ?int $queryTimeout ;

    /**
     * The scheme of the dsn expression.
     * @var string
     */
    public string $scheme ;

    /**
     * Prepares the array size configuration for the Data Source Name (DSN).
     * @param array $dsn The array representing the DSN components, passed by reference.
     * @return void
     */
    public function prepareArraySize( array &$dsn ) :void
    {
        if( is_int( $this->arraySize ) )
        {
            $dsn[] = self::ARRAY_SIZE . Char::EQUAL . $this->arraySize ;
        }
    }

    /**
     * Prepares the database configuration for the Data Source Name (DSN).
     * @param array $dsn
     * @return void
     */
    protected function prepareDatabase( array &$dsn ) :void
    {
        if( is_string( $this->database ) )
        {
            $dsn[] = self::DATABASE . Char::EQUAL . $this->database ;
        }
    }

    /**
     * Prepares the default long data buffer length size configuration for the Data Source Name (DSN).
     * @param array $dsn
     * @return void
     */
    protected function prepareDefaultLongDataBuffLen( array &$dsn ) :void
    {
        if( is_int( $this->defaultLongDataBuffLen ) )
        {
            $dsn[] = self::DEFAULT_LONG_DATA_BUFF_LEN . Char::EQUAL . $this->defaultLongDataBuffLen ;
        }
    }

    /**
     * Prepares the driver configuration for the Data Source Name (DSN).
     * @param array $dsn
     * @return void
     */
    protected function prepareDriver( array &$dsn ) :void
    {
        if( is_string( $this->driver ) )
        {
            $dsn[] = self::DRIVER . Char::EQUAL . $this->driver ;
        }
    }

    /**
     * Prepares the host name configuration for the Data Source Name (DSN).
     * @param array $dsn
     * @return void
     */
    protected function prepareHostName( array &$dsn ) :void
    {
        if( is_string( $this->hostName ) )
        {
            $dsn[] = self::HOST_NAME . Char::EQUAL . $this->hostName ;
        }
    }

    /**
     * Prepares the chat set configuration for the Data Source Name (DSN).
     * @param array $dsn
     * @return void
     */
    protected function prepareCharSet( array &$dsn ) :void
    {
        $dsn[] = self::CHAR_SET . Char::EQUAL . $this->charSet ;
    }

    /**
     * Prepares the port number configuration for the Data Source Name (DSN).
     * @param array $dsn
     * @return void
     */
    protected function preparePortNumber( array &$dsn ) :void
    {
        if( is_string( $this->portNumber) || is_int( $this->portNumber ) )
        {
            $dsn[] = self::PORT_NUMBER . Char::EQUAL . $this->portNumber ;
        }
    }

    /**
     * Prepares the query timeout configuration for the Data Source Name (DSN).
     * @param array $dsn
     * @return void
     */
    protected function prepareQueryTimeout( array &$dsn ) :void
    {
        if( is_int( $this->queryTimeout ) )
        {
            $dsn[] = self::QUERY_TIMEOUT . Char::EQUAL . $this->queryTimeout ;
        }
    }

    /**
     * Converts the object to its string representation.
     *
     * This method constructs a Data Source Name (DSN) string by preparing
     * various components such as driver, hostname, port number, database,
     * character set, array size, and buffer length. The components are
     * concatenated using a pre-defined delimiter to form the final DSN string.
     *
     * @return string The constructed DSN string.
     */
    public function __toString() :string
    {
        $dsn = [] ;
        $this->prepareDriver       ( $dsn ) ;
        $this->prepareHostName     ( $dsn ) ;
        $this->preparePortNumber   ( $dsn ) ;
        $this->prepareDatabase     ( $dsn ) ;
        $this->prepareCharSet      ( $dsn ) ;
        $this->prepareArraySize    ( $dsn ) ;
        $this->prepareDefaultLongDataBuffLen( $dsn ) ;
        $this->prepareQueryTimeout ( $dsn ) ;
        return $this->scheme . Char::COLON . implode( Char::SEMI_COLON , $dsn ) ;
    }
}