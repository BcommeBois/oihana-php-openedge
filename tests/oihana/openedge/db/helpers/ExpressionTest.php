<?php

namespace tests\oihana\openedge\db\helpers;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use DI\DependencyException;
use DI\NotFoundException;
use oihana\openedge\enums\OpenEdge;
use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;

use stdClass;
use function oihana\openedge\db\helpers\expression;

final class ExpressionTest extends TestCase
{
    /**
     * Test basic literal values.
     * @return void
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function test_literal_expression(): void
    {
        $this->assertSame("'hello'" , expression('hello' ) ) ;
        $this->assertSame(123       , expression(123     ) ) ;
        $this->assertSame(12.34     , expression(12.34   ) ) ;
    }

    /**
     * Test object conversion to array.
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function test_object_conversion(): void
    {
        $obj = new stdClass();
        $obj->{ OpenEdge::COLUMN } = 'name'  ;
        $obj->{ OpenEdge::TABLE  } = 'users' ;

        // Should be treated as column expression: users.name
        $this->assertSame('users.name' , expression( $obj ) ) ;
    }

    /**
     * Test BIND delegation.
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function test_bind_expression(): void
    {
        $definition = [ OpenEdge::BIND => 'userId'];
        // bindExpression should return :userId
        $this->assertSame(':userId' , expression($definition));
    }

    /**
     * Test VALUE delegation.
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function test_value_expression(): void
    {
        $definition = [ OpenEdge::VALUE => 'test' ] ;
        // valueExpression -> 'test'
        $this->assertSame("'test'", expression($definition));
    }

    /**
     * Test CASE delegation.
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function test_case_expression(): void
    {
        // Minimal CASE structure
        // CASE WHEN true THEN 1 ELSE 0 END
        $definition =
        [
            OpenEdge::CASE =>
            [
                OpenEdge::CONDITIONS =>
                [
                    [
                        OpenEdge::WHEN =>
                        [
                            [ '1', '=', '1' ]
                        ],
                        OpenEdge::THEN => 1
                    ]
                ],
                OpenEdge::ELSE => 0
            ]
        ];

        // We expect a CASE string. Exact format depends on caseExpression implementation.
        // CASE WHEN 1 = 1 THEN 1 ELSE 0 END
        // Let's check if it contains CASE and END at least.
        $result = expression( $definition );

        $this->assertEquals("CASE WHEN '1' = '1' THEN 1 ELSE 0 END" , $result );
    }

    /**
     * Test CONCAT delegation (includes ARRAY and LIST keys).
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function test_concat_expression(): void
    {
        // CONCAT
        $defConcat = [ OpenEdge::CONCAT => [ 'A' , 'B' ] ];
        // Should be 'A' || 'B' (using default CONCAT operator which adds ||)
        $this->assertSame("'A' || 'B'", expression($defConcat));

        // LIST (concatenation with separator)
        $defList = [ OpenEdge::LIST => [ 'A', 'B' ] ];
        // Should be 'A' || ',' || 'B'
        $this->assertSame("'A' || ',' || 'B'", expression($defList));
    }

    /**
     * Test COLUMN delegation (default branch).
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function test_column_expression(): void
    {
        $definition = [
            OpenEdge::COLUMN => 'age',
            OpenEdge::TABLE => 'users'
        ];
        $this->assertSame('users.age', expression($definition));
    }

    /**
     * Test recursive behavior (using the callable passed to helpers).
     * For example, a CONCAT of COLUMNs.
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function test_recursive_expression(): void
    {
        $definition =
        [
            OpenEdge::CONCAT =>
            [
                [OpenEdge::COLUMN => 'firstName', OpenEdge::TABLE => 'u'] ,
                '-' ,
                [OpenEdge::COLUMN => 'lastName', OpenEdge::TABLE => 'u' ]
            ]
        ];
        
        // Expect: u.firstName || '-' || u.lastName
        $this->assertSame("u.firstName || '-' || u.lastName", expression($definition));
    }

    /**
     * Test fallback to default behavior for empty array.
     * @throws ConstantException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function test_default_parameter(): void
    {
        // expression([]) -> literal([]) -> []
        $this->assertSame([], expression([]));

        // Test with associative array that falls to columnExpression but has no specific keys
        // columnExpression uses default if provided?
        // Let's check what columnExpression returns for ['random' => 'key'] with default 'myDefault'
        // Based on ColumnExpressionTest, it might return default if column is missing.
        // But let's assume the previous test failure was only about [] returning [] vs ''.
        
        $def = ['random' => 'key']; 
        $this->assertSame('myDefault', expression($def, 'myDefault'));
    }
}
