<?php

declare(strict_types=1);

namespace tests\oihana\openedge\db\helpers\functions;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use oihana\openedge\enums\OpenEdge;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\timeExpression;

final class TimeExpressionTest extends TestCase
{
    /**
     * @throws DateMalformedStringException
     * @throws DateInvalidTimeZoneException
     */
    public function testReturnsNullForNullExpression(): void
    {
        $this->assertNull(timeExpression(null));
    }

    /**
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testFormatsTimeDefaultUTC(): void
    {
        $time = '15:30:45';
        $expected = "{ t '15:30:45' }";

        $this->assertSame($expected, timeExpression($time));
    }

    /**
     * @throws DateMalformedStringException
     * @throws DateInvalidTimeZoneException
     */
    public function testFormatsTimeWithMilliseconds(): void
    {
        $time = '15:30:45.678';
        $expected = "{ t '15:30:45:678' }";

        $this->assertSame(
            $expected,
            timeExpression($time, [OpenEdge::MILLISECONDS => true])
        );
    }

    /**
     * @throws DateMalformedStringException
     * @throws DateInvalidTimeZoneException
     */
    public function testFormatsTimeWithMillisecondsAndTimezone(): void
    {
        $time = '15:30:45.123';
        $timezone = 'Europe/Paris';
        $expected = "{ t '15:30:45:123' }";

        $this->assertSame(
            $expected,
            timeExpression($time, [
                OpenEdge::MILLISECONDS => true,
                OpenEdge::TIMEZONE => $timezone
            ])
        );
    }

    public function testThrowsExceptionForInvalidTimezone(): void
    {
        $this->expectException(DateInvalidTimeZoneException::class);
        timeExpression('15:30:45', [OpenEdge::TIMEZONE => 'Invalid/Zone']);
    }

    /**
     * @throws DateInvalidTimeZoneException
     */
    public function testThrowsExceptionForMalformedTime(): void
    {
        $this->expectException(DateMalformedStringException::class);
        timeExpression('not-a-time');
    }

    /**
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     */
    public function testFormatsTimeWithoutMillisecondsEvenIfProvidedFalse(): void
    {
        $time = '15:30:45.999';
        $expected = "{ t '15:30:45' }";

        $this->assertSame(
            $expected,
            timeExpression($time, [OpenEdge::MILLISECONDS => false])
        );
    }
}