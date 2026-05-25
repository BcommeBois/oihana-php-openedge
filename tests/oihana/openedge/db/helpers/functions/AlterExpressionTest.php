<?php

declare(strict_types=1);

namespace tests\oihana\openedge\db\helpers\functions;

use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\alters\alterExpression;

final class AlterExpressionTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    #[DataProvider('provideAlterExpressionCases')]
    public function testAlterExpression(string $expected, string $expression, array|string|null $definition): void
    {
        $result = alterExpression($expression, $definition, null ) ;
        $this->assertSame($expected, $result);
    }

    public static function provideAlterExpressionCases(): array
    {
        return
        [
            // --- Cas simples : on applique une fonction SQL ---
            'fonction string simple' =>
            [
                'NOW()',
                'created',
                'NOW'
            ],
            'fonction string upper' =>
            [
                'UPPER(name)',
                'name',
                'UPPER'
            ],

            // --- Cas avec tableau : fonction + arguments ---
            'array avec arguments' =>
            [
                'IFNULL(stock,0)',
                'stock',
                ['IFNULL', 0]
            ],
            'array avec plusieurs arguments' =>
            [
                'SUBSTRING(name,1,3)',
                'name',
                ['SUBSTRING', 1, 3]
            ],

            // --- Cas array mal formé : premier élément non string ---
            'array mal formé' =>
            [
                'price',
                'price',
                [123, 'oops']
            ],

            // --- Cas où on ne fait rien : definition null ---
            'definition null' =>
            [
                'price',
                'price',
                null
            ],
        ];
    }
}