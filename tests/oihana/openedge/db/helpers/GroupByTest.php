<?php

namespace tests\oihana\openedge\db\helpers;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use DI\DependencyException;
use DI\NotFoundException;
use oihana\openedge\db\traits\GroupByTrait;
use oihana\openedge\enums\OpenEdge;
use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;

final class GroupByTest extends TestCase
{
    use GroupByTrait ;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     * @throws ConstantException
     */
    public function testStringSingleColumn(): void
    {
        $result = $this->groupBy('name' ) ;
        $this->assertEquals('GROUP BY name', $result ) ;
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testStringDefinition(): void
    {
        $result = $this->groupBy("name, country" );
        $this->assertEquals('GROUP BY name, country', $result);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testArraySimpleColumns(): void
    {
        $result = $this->groupBy
        ([
            OpenEdge::GROUP_BY =>
            [
                [ OpenEdge::COLUMN => 'name'   , OpenEdge::TABLE => 'places' ] ,
                [ OpenEdge::COLUMN => 'country']
            ]
        ]);
        $this->assertEquals('GROUP BY places.name, country' , $result);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testArrayWithExpressions(): void
    {
        $result = $this->groupBy
        ([
            OpenEdge::GROUP_BY =>
                [
                    [ OpenEdge::COLUMN => "YEAR(orderDate)" ] ,
                    "customerId"
                ]
        ] ) ;
        $this->assertEquals('GROUP BY YEAR(orderDate), "customerId"', $result);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testFallbackToProperty(): void
    {
        $this->groupBy =
        [
            [ OpenEdge::COLUMN => "YEAR(orderDate)" ] ,
            "customerId"
        ] ;

        $result = $this->groupBy();

        $this->assertEquals('GROUP BY YEAR(orderDate), "customerId"' , $result ) ;
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testEmptyStringReturnsEmpty(): void
    {
        $result = $this->groupBy("");
        $this->assertEquals('', $result);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testEmptyArrayReturnsEmpty(): void
    {
        $result = $this->groupBy([]);
        $this->assertEquals('', $result);
    }

}