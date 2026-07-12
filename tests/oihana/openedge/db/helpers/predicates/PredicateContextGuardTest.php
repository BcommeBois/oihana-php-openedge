<?php

namespace tests\oihana\openedge\db\helpers\predicates;

use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\predicates\prepareBasicPredicate;
use function oihana\openedge\db\helpers\predicates\prepareBetweenPredicate;
use function oihana\openedge\db\helpers\predicates\prepareExistPredicate;
use function oihana\openedge\db\helpers\predicates\prepareInPredicate;
use function oihana\openedge\db\helpers\predicates\prepareLikePredicate;
use function oihana\openedge\db\helpers\predicates\prepareNullPredicate;
use function oihana\openedge\db\helpers\predicates\prepareQuantifiedPredicate;

/**
 * Each prepare*Predicate() returns an empty string when the current context is
 * not allowed (validateContext returns false). Here the context 'get' is never
 * in the allowed list ['count'].
 */
final class PredicateContextGuardTest extends TestCase
{
    private const string CONTEXT = 'get';
    private const array  ALLOWED = [ 'count' ];

    public function testBasicPredicateIsGuardedByContext(): void
    {
        $this->assertSame( '', prepareBasicPredicate( 'a', '=', 'b', self::CONTEXT, self::ALLOWED ) );
    }

    public function testBetweenPredicateIsGuardedByContext(): void
    {
        $this->assertSame( '', prepareBetweenPredicate( 'a', 'BETWEEN', 1, 2, self::CONTEXT, self::ALLOWED ) );
    }

    public function testExistPredicateIsGuardedByContext(): void
    {
        $this->assertSame( '', prepareExistPredicate( 'EXISTS', 'SELECT 1', self::CONTEXT, self::ALLOWED ) );
    }

    public function testInPredicateIsGuardedByContext(): void
    {
        $this->assertSame( '', prepareInPredicate( 'a', 'IN', [ 1, 2 ], self::CONTEXT, self::ALLOWED ) );
    }

    public function testInPredicateReturnsEmptyWhenValuesAreNull(): void
    {
        $this->assertSame( '', prepareInPredicate( 'a', 'IN', null ) );
    }

    public function testLikePredicateIsGuardedByContext(): void
    {
        $this->assertSame( '', prepareLikePredicate( 'a', 'LIKE', 'b%', null, self::CONTEXT, self::ALLOWED ) );
    }

    public function testNullPredicateIsGuardedByContext(): void
    {
        $this->assertSame( '', prepareNullPredicate( 'a', 'IS NULL', self::CONTEXT, self::ALLOWED ) );
    }

    public function testQuantifiedPredicateIsGuardedByContext(): void
    {
        $this->assertSame( '', prepareQuantifiedPredicate( 'a', '>', 'ALL', 'SELECT 1', self::CONTEXT, self::ALLOWED ) );
    }
}
