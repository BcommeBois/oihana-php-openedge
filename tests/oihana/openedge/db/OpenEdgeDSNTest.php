<?php

namespace tests\oihana\openedge\db;

use oihana\openedge\db\OpenEdgeDSN;
use PHPUnit\Framework\TestCase;
use TypeError;

final class OpenEdgeDSNTest extends TestCase
{
    public function testFullDsnStringAndComponentOrder(): void
    {
        $dsn = new OpenEdgeDSN( [
            'scheme'                 => 'odbc',
            'driver'                 => '/usr/dlc/odbc/lib/pgoe27.so',
            'hostName'               => 'erp.example.com',
            'portNumber'             => 20931,
            'database'               => 'erp_db',
            'arraySize'              => 200,
            'defaultLongDataBuffLen' => 8,
            'queryTimeout'           => 300,
        ] );

        $this->assertSame(
            'odbc:Driver=/usr/dlc/odbc/lib/pgoe27.so;HostName=erp.example.com;PortNumber=20931;'
            . 'Database=erp_db;IANAAppCodePage=106;ArraySize=200;DefaultLongDataBuffLen=8;QueryTimeout=300',
            (string) $dsn
        );
    }

    public function testMinimalDsnOnlyEmitsCharSet(): void
    {
        // Every other component is null and skipped; the character set is
        // always emitted (default IANAAppCodePage=106, UTF-8).
        $this->assertSame( 'odbc:IANAAppCodePage=106', (string) new OpenEdgeDSN( [ 'scheme' => 'odbc' ] ) );
    }

    public function testStringPortNumberIsAccepted(): void
    {
        $this->assertSame(
            'odbc:PortNumber=1234;IANAAppCodePage=106',
            (string) new OpenEdgeDSN( [ 'scheme' => 'odbc', 'portNumber' => '1234' ] )
        );
    }

    public function testCustomCharSet(): void
    {
        $this->assertSame(
            'odbc:IANAAppCodePage=105',
            (string) new OpenEdgeDSN( [ 'scheme' => 'odbc', 'charSet' => 105 ] )
        );
    }

    public function testNonIntNumericComponentsAreSkipped(): void
    {
        // arraySize / defaultLongDataBuffLen / queryTimeout only appear when int.
        $dsn = new OpenEdgeDSN( [
            'scheme'                 => 'odbc',
            'hostName'               => 'h',
            'arraySize'              => null,
            'defaultLongDataBuffLen' => null,
            'queryTimeout'           => null,
        ] );

        $this->assertSame( 'odbc:HostName=h;IANAAppCodePage=106', (string) $dsn );
    }

    public function testQueryTimeoutZeroIsEmitted(): void
    {
        $this->assertSame(
            'odbc:IANAAppCodePage=106;QueryTimeout=0',
            (string) new OpenEdgeDSN( [ 'scheme' => 'odbc', 'queryTimeout' => 0 ] )
        );
    }

    public function testDatabaseOnlyWhenString(): void
    {
        $this->assertSame(
            'odbc:Database=mydb;IANAAppCodePage=106',
            (string) new OpenEdgeDSN( [ 'scheme' => 'odbc', 'database' => 'mydb' ] )
        );
    }

    public function testPublicPropertyMutationReflectedInDsn(): void
    {
        $dsn = new OpenEdgeDSN( [ 'scheme' => 'odbc' ] );
        $dsn->hostName   = 'db.local';
        $dsn->arraySize  = 50;

        $this->assertSame( 'odbc:HostName=db.local;IANAAppCodePage=106;ArraySize=50', (string) $dsn );
    }

    public function testMissingSchemeThrowsTypeError(): void
    {
        // $scheme is a non-nullable string but the constructor assigns `?? null`.
        $this->expectException( TypeError::class );
        new OpenEdgeDSN();
    }
}
