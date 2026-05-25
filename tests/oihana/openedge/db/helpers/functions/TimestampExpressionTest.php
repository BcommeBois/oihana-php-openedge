<?php

declare(strict_types=1);

namespace tests\oihana\openedge\db\helpers\functions;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use oihana\openedge\enums\OpenEdge;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\timestampExpression;

final class TimestampExpressionTest extends TestCase
{
    /**
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testReturnsNullForNullExpression(): void
    {
        $this->assertNull(timestampExpression(null));
    }

    /**
     * @throws DateMalformedStringException
     * @throws DateInvalidTimeZoneException
     */
    public function testFormatsTimestampDefaultUTC(): void
    {
        $timestamp = '2025-10-17 14:30:45';
        $expected = "{ ts '2025-10-17 14:30:45' }";

        $this->assertSame($expected, timestampExpression($timestamp));
    }

    /**
     * @throws DateMalformedStringException
     * @throws DateInvalidTimeZoneException
     */
    public function testFormatsTimestampWithCustomTimezone(): void
    {
        $timestamp = '2025-10-17 14:30:45';
        $timezone = 'Europe/Paris';
        $expected = "{ ts '2025-10-17 14:30:45' }";

        $this->assertSame($expected, timestampExpression($timestamp, [OpenEdge::TIMEZONE => $timezone]));
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testThrowsExceptionForInvalidTimezone(): void
    {
        $this->expectException(DateInvalidTimeZoneException::class);
        timestampExpression('2025-10-17 14:30:45', [OpenEdge::TIMEZONE => 'Invalid/Zone']);
    }

    /**
     * @throws DateInvalidTimeZoneException
     */
    public function testThrowsExceptionForMalformedTimestamp(): void
    {
        $this->expectException(DateMalformedStringException::class);
        timestampExpression('not-a-timestamp');
    }
}