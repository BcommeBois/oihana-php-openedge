<?php

namespace tests\oihana\openedge\db\traits;

use oihana\openedge\db\enums\Logic;
use oihana\openedge\db\enums\Predicate;
use oihana\openedge\db\traits\WhereTrait;
use oihana\openedge\enums\OpenEdge;
use PHPUnit\Framework\TestCase;

final class WhereTraitTest extends TestCase
{
    private object $query ;

    protected function setUp() :void
    {
        $this->query = new class
        {
            use WhereTrait ;
        };
    }

    public function testEmptyWhere(): void
    {
        $this->assertEquals
        (
            expected : "",
            actual   : $this->query->where()
        );
    }

    public function testStringWhere(): void
    {
        $this->assertEquals
        (
            expected : "WHERE products.status = 0",
            actual   : $this->query->where("products.status = 0")
        );
    }

    public function testBasicArrayPredicate(): void
    {
        $this->assertEquals
        (
            expected : "WHERE age >= 18" ,
            actual   : $this->query->where([ [ [ OpenEdge::COLUMN  => 'age' ] , '>=' , 18 ] ] )
        );
    }

    public function testAndLogic(): void
    {
        $definition =
        [
            [ [ OpenEdge::COLUMN  => 'age'    ] , '>=' , 18 ] ,
            [ [ OpenEdge::COLUMN  => 'status' ] , '='  , 1  ]
        ];

        $this->assertEquals
        (
            expected : "WHERE age >= 18 AND status = 1",
            actual   : $this->query->where( $definition )
        );
    }

    public function testOrLogic(): void
    {
        $definition =
        [
            'operator'   => Logic::OR,
            'conditions' =>
            [
                [ [ OpenEdge::COLUMN  => 'age'    ] , '>=' , 18 ] ,
                [ [ OpenEdge::COLUMN  => 'status' ] , '='  , 1  ]
            ]
        ];

        $this->assertEquals
        (
            expected : "WHERE age >= 18 OR status = 1",
            actual   : $this->query->where($definition)
        );
    }

    public function testBetweenPredicate(): void
    {
        $definition = [ [ [ OpenEdge::COLUMN  => 'price' ] , Predicate::BETWEEN , 0 , 500 ] ] ;

        $this->assertEquals
        (
            expected : "WHERE price BETWEEN 0 AND 500",
            actual   : $this->query->where($definition)
        );
    }

    public function testNullPredicate(): void
    {
        $definition = [ [ [ OpenEdge::COLUMN  => 'name' ] , Predicate::NULL ] ];

        $this->assertEquals
        (
            expected : "WHERE name IS NULL",
            actual   : $this->query->where($definition)
        );
    }

    public function testNestedLogic(): void
    {
        $definition =
        [
            [ [ OpenEdge::COLUMN  => 'status' ] , '=' , 0 ] ,
            [
                OpenEdge::OPERATOR   => Logic::OR,
                OpenEdge::CONDITIONS =>
                [
                    [ [ OpenEdge::COLUMN  => 'name' ] , '=' , 'foo' ] ,
                    [ [ OpenEdge::COLUMN  => 'name' ] , '=' , 'bar' ]
                ]
            ]
        ];

        $this->assertEquals
        (
            expected : "WHERE status = 0 AND (name = 'foo' OR name = 'bar')",
            actual   : $this->query->where($definition)
        );
    }
}