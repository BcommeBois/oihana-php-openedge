<?php

namespace tests\oihana\openedge\db\helpers\functions\conversions;

use PHPUnit\Framework\TestCase;
use oihana\openedge\db\enums\functions\ConversionFunction;
use function oihana\openedge\db\helpers\functions\conversions\toChar;

final class ToCharTest extends TestCase
{
    public function testToCharWithFormat(): void
    {
        $expr = 'order_date';
        $format = 'YYYY-MM-DD';

        $result = toChar($expr, $format);

        $this->assertIsString($result);
        $this->assertStringContainsString(ConversionFunction::TO_CHAR, $result);
        $this->assertStringContainsString($expr, $result);
        $this->assertStringContainsString($format, $result);
    }

    public function testToCharWithoutFormat(): void
    {
        $expr = 'salary';

        $result = toChar($expr);

        $this->assertIsString($result);
        $this->assertStringContainsString(ConversionFunction::TO_CHAR, $result);
        $this->assertStringContainsString($expr, $result);
        $this->assertStringNotContainsString(',', $result); // Pas de virgule si pas de format
    }

    public function testToCharNullFormatHandled(): void
    {
        $expr = 'price';
        $format = null;

        $result = toChar($expr, $format);

        $this->assertIsString($result);
        $this->assertStringContainsString(ConversionFunction::TO_CHAR, $result);
        $this->assertStringContainsString($expr, $result);
    }
}