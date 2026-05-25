<?php

namespace tests\oihana\openedge\db\helpers;

use oihana\enums\Char;
use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\asAlias;

final class AsAliasTest extends TestCase
{
    public function testAliasWithoutAlias(): void
    {
        $this->assertSame( 'customer_id', asAlias('customer_id') );
    }

    public function testAliasWithCaseSensitive(): void
    {
        $this->assertSame(
            'customer_id AS "id"',
            asAlias('customer_id', 'id')
        );
    }

    public function testAliasWithCaseInsensitive(): void
    {
        $this->assertSame(
            'customer_id AS id',
            asAlias('customer_id', 'id', false)
        );
    }

    public function testAliasWithEmptyAlias(): void
    {
        $this->assertSame(
            'customer_id',
            asAlias('customer_id', Char::EMPTY )
        );
    }

    public function testAliasWithSpecialCharacters(): void
    {
        $this->assertSame(
            'full name AS "Customer ID"',
            asAlias('full name', 'Customer ID' )
        );
    }
}