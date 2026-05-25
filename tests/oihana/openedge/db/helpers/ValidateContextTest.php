<?php

namespace tests\oihana\openedge\db\helpers;

use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\validateContext;

final class ValidateContextTest extends TestCase
{
    public function testValidContextStringInArray(): void
    {
        $this->assertTrue(validateContext('WHERE', ['WHERE', 'HAVING']));
    }

    public function testValidContextStringExact(): void
    {
        $this->assertTrue(validateContext('GROUP', 'GROUP'));
    }

    public function testContextNotAllowedWithArray(): void
    {
        $this->assertFalse(validateContext('ORDER', ['WHERE', 'HAVING']));
    }

    public function testContextNotAllowedWithString(): void
    {
        $this->assertFalse(validateContext('ORDER', 'WHERE'));
    }

    public function testNullContextDoesNotThrow(): void
    {
        $this->assertTrue(validateContext(null, ['WHERE', 'HAVING']));
    }

    public function testEmptyStringContextDoesNotThrow(): void
    {
        $this->assertTrue(validateContext('', ['WHERE', 'HAVING']));
    }

    public function testNullAllowedDoesNotThrow(): void
    {
        $this->assertTrue(validateContext('WHERE', null));
    }

    public function testEmptyAllowedArray(): void
    {
        $this->assertFalse(validateContext('WHERE', []));
    }
}