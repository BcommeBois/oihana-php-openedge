<?php

namespace tests\oihana\openedge\db\traits;

use oihana\openedge\db\enums\LockingHint;
use oihana\openedge\db\traits\LookingHintTrait;
use oihana\openedge\enums\OpenEdge;
use PHPUnit\Framework\TestCase;

final class LookingHintTraitTest extends TestCase
{
    private object $query;

    protected function setUp(): void
    {
        $this->query = new class {
            use LookingHintTrait;
        };
        $this->query->lockingHint = null;
    }

    public function testEmptyWhenNoHint(): void
    {
        $this->assertSame( '', $this->query->withLockingHint() );
    }

    public function testNoLock(): void
    {
        $this->assertSame(
            'WITH (NOLOCK)',
            $this->query->withLockingHint( [ OpenEdge::LOCKING_HINT => LockingHint::NOLOCK ] )
        );
    }

    public function testNoWait(): void
    {
        $this->assertSame(
            'WITH (READPAST NOWAIT)',
            $this->query->withLockingHint( [ OpenEdge::LOCKING_HINT => LockingHint::NOWAIT ] )
        );
    }

    public function testIntegerWaitTimeout(): void
    {
        $this->assertSame(
            'WITH (READPAST WAIT 2)',
            $this->query->withLockingHint( [ OpenEdge::LOCKING_HINT => 2 ] )
        );
    }

    public function testFallsBackToProperty(): void
    {
        $this->query->lockingHint = LockingHint::NOLOCK;
        $this->assertSame( 'WITH (NOLOCK)', $this->query->withLockingHint() );
    }

    public function testUnknownStringHintYieldsEmpty(): void
    {
        $this->assertSame(
            '',
            $this->query->withLockingHint( [ OpenEdge::LOCKING_HINT => 'UNKNOWN' ] )
        );
    }

    public function testWithNoLockTrue(): void
    {
        $this->assertSame( 'WITH (NOLOCK)', $this->query->withNoLock() );
    }

    public function testWithNoLockFalse(): void
    {
        $this->assertSame( '', $this->query->withNoLock( false ) );
    }
}
