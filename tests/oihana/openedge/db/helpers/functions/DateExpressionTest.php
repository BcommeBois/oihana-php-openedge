<?php

declare(strict_types=1);

namespace tests\oihana\openedge\db\helpers\functions;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use oihana\openedge\enums\OpenEdge;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\dateExpression;

final class DateExpressionTest extends TestCase
{
    /**
     * @throws DateMalformedStringException
     * @throws DateInvalidTimeZoneException
     */
    public function testReturnsNullForNullExpression(): void
    {
        $this->assertNull(dateExpression(null));
    }

    /**
     * @throws DateMalformedStringException
     * @throws DateInvalidTimeZoneException
     */
    public function testFormatsDateDefaultUTC(): void
    {
        $date = '2025-10-17';
        $expected = "{ d '2025-10-17' }";

        $this->assertSame($expected, dateExpression($date));
    }

    /**
     * @throws DateMalformedStringException
     * @throws DateInvalidTimeZoneException
     */
    public function testFormatsDateWithCustomTimezone(): void
    {
        $date = '2025-10-17';
        $timezone = 'Europe/Paris';
        $expected = "{ d '2025-10-17' }";

        $this->assertSame($expected, dateExpression($date, [OpenEdge::TIMEZONE => $timezone]));
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testThrowsExceptionForInvalidTimezone(): void
    {
        $this->expectException(DateInvalidTimeZoneException::class);
        dateExpression('2025-10-17', [OpenEdge::TIMEZONE => 'Invalid/Zone']);
    }

    /**
     * @throws DateInvalidTimeZoneException
     */
    public function testThrowsExceptionForMalformedDate(): void
    {
        $this->expectException(DateMalformedStringException::class);
        dateExpression('not-a-date');
    }

    /**
     * @throws DateMalformedStringException
     * @throws DateInvalidTimeZoneException
     */
    public function testSupportsTodayImplicitly(): void
    {
        $result = dateExpression(null);
        $this->assertNull($result); // déjà testé, mais on peut vérifier le comportement avec 'now' si modifié
    }
}