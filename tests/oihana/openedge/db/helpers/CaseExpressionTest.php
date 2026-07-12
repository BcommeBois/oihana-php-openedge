<?php

namespace tests\oihana\openedge\db\helpers;

use oihana\openedge\enums\OpenEdge;
use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\caseExpression;

final class CaseExpressionTest extends TestCase
{
    public function testEmptyDefinitionReturnsEmpty(): void
    {
        $this->assertSame( '', caseExpression( [] ) );
    }

    public function testNonAssociativeCaseReturnsEmpty(): void
    {
        $this->assertSame( '', caseExpression( [ OpenEdge::CASE => [ 1, 2, 3 ] ] ) );
    }

    public function testSimpleCaseExpression(): void
    {
        $definition = [ OpenEdge::CASE => [ OpenEdge::EXPRESSION => 'status' ] ];
        $this->assertSame( 'CASE \'status\' ELSE NULL END', caseExpression( $definition ) );
    }
}
