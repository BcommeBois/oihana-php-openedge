<?php

declare(strict_types=1);

namespace tests\oihana\openedge\db\helpers\functions\conditionals;

use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\conditionals\nvl;

final class NvlTest extends TestCase
{
    public function testReturnsNVLString()
    {
        $result = nvl('price', 0);
        $this->assertStringContainsString('NVL', $result);
        $this->assertStringContainsString('price', $result);
        $this->assertStringContainsString('0', $result);
    }

    public function testCallbackIsApplied()
    {
        $callback = fn($value) => $value * 2;
        $result = nvl('price', 10, $callback);

        // Vérifie que le callback est appliqué (ici on s'attend à 20)
        $this->assertStringContainsString('20', $result);
    }

    public function testWorksWithStrings()
    {
        $result = nvl('name', "'unknown'");
        $this->assertStringContainsString('name', $result);
        $this->assertStringContainsString("'unknown'", $result);
    }

    public function testWorksWithCallbackReturningString()
    {
        $callback = fn($value) => strtoupper($value);
        $result = nvl('category', 'default', $callback);
        $this->assertStringContainsString('DEFAULT', $result);
    }

    public function testWorksWithNullSecondArgument()
    {
        $result = nvl('discount', null);
        $this->assertStringContainsString('discount', $result);
        $this->assertStringContainsString('NULL', $result);
    }
}