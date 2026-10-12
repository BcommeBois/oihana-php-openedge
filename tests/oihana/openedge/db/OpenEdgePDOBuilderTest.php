<?php

namespace tests\oihana\openedge\db;

use oihana\openedge\db\OpenEdgeDSN;
use oihana\openedge\db\OpenEdgePDOBuilder;
use PDO;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

final class OpenEdgePDOBuilderTest extends TestCase
{
    public function testConstructorMapsDsnAndCredentials(): void
    {
        $builder = new OpenEdgePDOBuilder( [
            'scheme'                       => 'odbc',
            'hostName'                     => 'erp.example.com',
            OpenEdgePDOBuilder::LOGON_ID   => 'reader',
            OpenEdgePDOBuilder::PASSWORD   => 'secret',
        ] );

        $this->assertSame( 'odbc:HostName=erp.example.com;IANAAppCodePage=106', (string) $builder->dsn );
        $this->assertSame( 'reader', $builder->logonID );
        $this->assertSame( 'secret', $builder->password );
    }

    public function testCredentialsDefaultToNull(): void
    {
        $builder = new OpenEdgePDOBuilder( [ 'scheme' => 'odbc' ] );

        $this->assertNull( $builder->logonID );
        $this->assertNull( $builder->password );
    }

    #[RequiresPhpExtension( 'pdo_sqlite' )]
    public function testInvokeOpensAConfiguredPdo(): void
    {
        // No OpenEdge ODBC driver locally: swap the DSN for an in-memory SQLite
        // one so __invoke() runs for real. Driver-specific attributes SQLite
        // does not support (cursor, persistent, emulate) are silently refused.
        $builder = new OpenEdgePDOBuilder( [ 'scheme' => 'odbc' ] );
        $builder->dsn = new class( [ 'scheme' => 'sqlite' ] ) extends OpenEdgeDSN {
            public function __toString(): string { return 'sqlite::memory:'; }
        };

        $pdo = $builder();

        $this->assertInstanceOf( PDO::class, $pdo );
        $this->assertSame( PDO::FETCH_ASSOC, $pdo->getAttribute( PDO::ATTR_DEFAULT_FETCH_MODE ) );
        $this->assertSame( PDO::ERRMODE_EXCEPTION, $pdo->getAttribute( PDO::ATTR_ERRMODE ) );
    }
}
