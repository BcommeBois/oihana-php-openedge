<?php

namespace tests\oihana\openedge\db\helpers\functions\conditionals;

use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\conditionals\ifNull;
use function oihana\openedge\db\helpers\functions\conditionals\nullIf;

class IfNullAndNullIfTest extends TestCase
{
    /**
     * @test
     * @group conversions
     */
    public function testIfNullWithBasicStrings(): void
    {
        $result = ifNull('col1', "'default'");
        $this->assertSame('IFNULL(col1,\'default\')', $result);
    }

    /**
     * @test
     * @group conversions
     */
    public function testIfNullWithNumbers(): void
    {
        $result = ifNull('price', 0);
        $this->assertSame('IFNULL(price,0)', $result);
    }

    /**
     * @test
     * @group conversions
     */
    public function testIfNullWithNullValue(): void
    {
        $result = ifNull('description' , 'NULL');
        $this->assertSame('IFNULL(description,NULL)', $result);
    }

    /**
     * @test
     * @group conversions
     */
    public function testIfNullWithNestedExpressions(): void
    {
        $inner = ifNull('amount', 0);
        $result = ifNull($inner, "'N/A'");
        $this->assertSame('IFNULL(IFNULL(amount,0),\'N/A\')', $result);
    }

    /**
     * @test
     * @group conditionals
     */
    public function testNullIfWithDifferentValues(): void
    {
        $result = nullIf('col1', 'col2');
        $this->assertSame('NULLIF(col1,col2)', $result);
    }

    /**
     * @test
     * @group conditionals
     */
    public function testNullIfWithSameValues(): void
    {
        $result = nullIf('status', 'status');
        $this->assertSame('NULLIF(status,status)', $result);
    }

    /**
     * @test
     * @group conditionals
     */
    public function testNullIfWithStringComparison(): void
    {
        $result = nullIf('name', "'N/A'");
        $this->assertSame('NULLIF(name,\'N/A\')', $result);
    }

    /**
     * @test
     * @group conditionals
     */
    public function testNullIfWithNestedExpressions(): void
    {
        $inner = nullIf('col1', 'col2');
        $result = nullIf($inner, 0);
        $this->assertSame('NULLIF(NULLIF(col1,col2),0)', $result);
    }

}