<?php

namespace tests\oihana\openedge\db\traits;

use oihana\openedge\db\traits\ColumnTrait;
use oihana\openedge\enums\OpenEdge;
use PHPUnit\Framework\TestCase;

final class ColumnTraitTest extends TestCase
{
    private object $query;

    protected function setUp(): void
    {
        $this->query = new class {
            use ColumnTrait;
        };
    }

    public function testStringInitReturnedAsIs(): void
    {
        $this->assertSame( 'a, b, c', $this->query->columnList( 'a, b, c' ) );
    }

    public function testNullColumnsFallBackToStar(): void
    {
        $this->assertSame( OpenEdge::ALL, $this->query->columnList() );
    }

    public function testColumnsPropertyAsString(): void
    {
        $this->query->columns = 'id, name';
        $this->assertSame( 'id, name', $this->query->columnList() );
    }

    public function testAssociativeStringDefinitionBecomesAlias(): void
    {
        $this->query->columns = [ 'name' => 'nom' ];
        $this->assertSame( 'nom AS "name"', $this->query->columnList() );
    }

    public function testAssociativeArrayDefinitionUsesExpression(): void
    {
        $this->query->columns = [ 'name' => [ OpenEdge::COLUMN => 'nom', OpenEdge::TABLE => 'clients' ] ];
        $this->assertSame( 'clients.nom AS "name"', $this->query->columnList() );
    }

    public function testKeyEqualsDefinitionYieldsBareKey(): void
    {
        $this->query->columns = [ 'name' => 'name' ];
        $this->assertSame( 'name', $this->query->columnList() );
    }

    public function testEmptyKeysAreSkipped(): void
    {
        $this->query->columns = [ '' => 'ignored', 'name' => 'nom' ];
        $this->assertSame( 'nom AS "name"', $this->query->columnList() );
    }

    public function testInitColumnsOverrideProperty(): void
    {
        $this->query->columns = [ 'name' => 'nom' ];
        $this->assertSame(
            'id, name',
            $this->query->columnList( [ OpenEdge::COLUMNS => 'id, name' ] )
        );
    }

    public function testMultipleColumnsJoinedWithSeparator(): void
    {
        $this->query->columns = [ 'id' => 'code', 'name' => 'nom' ];
        $this->assertSame(
            'code AS "id", nom AS "name"',
            $this->query->columnList()
        );
    }
}
