<?php

declare(strict_types=1);

namespace tests\oihana\openedge\db\helpers\functions;

use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\castKey;

final class CastKeyTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastBigInt(): void
    {
        $this->assertSame('CAST(col AS BIGINT)', castKey('col', Type::BIGINT));
    }

    /**
     * @throws ConstantException
     */
    public function testCastVarChar(): void
    {
        $this->assertSame('CAST(name AS VARCHAR(50))', castKey('name', Type::VARCHAR, [50]));
        $this->assertSame('CAST(name AS VARCHAR(1))', castKey('name', Type::VARCHAR));
    }

    /**
     * @throws ConstantException
     */
    public function testCastVarBinary(): void
    {
        $this->assertSame('CAST(data AS VARBINARY(255))', castKey('data', Type::VARBINARY, [255]));
        $this->assertSame('CAST(data AS VARBINARY(1))', castKey('data', Type::VARBINARY));
    }

    /**
     * @throws ConstantException
     */
    public function testCastLVarBinary(): void
    {
        $this->assertSame('CAST(file AS LVARBINARY(512))', castKey('file', Type::LVARBINARY, [512]));
        $this->assertSame('CAST(file AS LVARBINARY(256))', castKey('file', Type::LVARBINARY));
    }

    /**
     * @throws ConstantException
     */
    public function testCastDecimal(): void
    {
        $this->assertSame('CAST(amount AS DECIMAL(10,2))', castKey('amount', Type::DECIMAL, [10, 2]));
        $this->assertSame('CAST(amount AS DECIMAL(32,0))', castKey('amount', Type::DECIMAL));
    }

    /**
     * @throws ConstantException
     */
    public function testCastFloatAndDouble(): void
    {
        $this->assertSame('CAST(score AS FLOAT)', castKey('score', Type::FLOAT));
        $this->assertSame('CAST(score AS FLOAT(8))', castKey('score', Type::FLOAT, [8]));
        $this->assertSame('CAST(val AS DOUBLE PRECISION)', castKey('val', Type::DOUBLE_PRECISION));
        $this->assertSame('CAST(r AS REAL)', castKey('r', Type::REAL));
    }

    /**
     * @throws ConstantException
     */
    public function testCastIntegers(): void
    {
        $this->assertSame('CAST(id AS INTEGER)', castKey('id', Type::INTEGER));
        $this->assertSame('CAST(s AS SMALLINT)', castKey('s', Type::SMALLINT));
        $this->assertSame('CAST(t AS TINYINT)', castKey('t', Type::TINYINT));
    }

    /**
     * @throws ConstantException
     */
    public function testCastDatesAndTimes(): void
    {
        $this->assertSame('CAST(created_at AS DATE)', castKey('created_at', Type::DATE));
        $this->assertSame('CAST(start_time AS TIME)', castKey('start_time', Type::TIME));
    }

    /**
     * @throws ConstantException
     */
    public function testCastTimestamp(): void
    {
        $this->assertSame(
            'CAST(updated_at AS TIMESTAMP)',
            castKey('updated_at', Type::TIMESTAMP)
        );

        $this->assertSame(
            'CAST(updated_at AS TIMESTAMP WITH TIME ZONE)',
            castKey('updated_at', Type::TIMESTAMP_WITH_TIME_ZONE)
        );
    }

    /**
     * @throws ConstantException
     */
    public function testCastClobAndBlob(): void
    {
        $this->assertSame('CAST(doc AS CLOB)', castKey('doc', Type::CLOB));
        $this->assertSame('CAST(img AS BLOB)', castKey('img', Type::BLOB));
    }

    /**
     * @throws ConstantException
     */
    public function testCastBit(): void
    {
        $this->assertSame('CAST(flag AS BIT)', castKey('flag', Type::BIT));
    }

    /**
     * @throws ConstantException
     */
    public function testCastChar(): void
    {
        $this->assertSame('CAST(initial AS CHAR(3))', castKey('initial', Type::CHAR, [3]));
        $this->assertSame('CAST(initial AS CHAR(1))', castKey('initial', Type::CHAR));
    }

    /**
     * @throws ConstantException
     */
    public function testFallbackReturnsKey(): void
    {
        // Si le type n'est pas reconnu, la fonction retourne simplement la clé inchangée
        $this->assertSame('column', castKey('column', 'UNKNOWN_TYPE'));
        $this->assertSame('column', castKey('column'));
    }
}