<?php

namespace oihana\openedge\db\enums;

use oihana\reflect\traits\ConstantsTrait;

class Type
{
    use ConstantsTrait ;

    /**
     * The ARRAY data type is a composite data value that consists of zero or more elements
     * of a specified data type (known as the element type).
     */
    public const string ARRAY = 'ARRAY' ;

    public const string BIGINT = 'BIGINT' ;

    /**
     * Corresponds to a bit field of the specified length of bytes.
     * The default length is 1 byte. The maximum length is 2000 bytes.
     * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Bit-string-data-types.html
     */
    public const string BINARY = 'BINARY' ;

    /**
     * Corresponds to a single bit value of 0 or 1.
     * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Bit-string-data-types.html
     */
    public const string BIT = 'BIT' ;

    /**
     * A BLOB is an object of data type LVARBINARY.
     * Corresponds to an arbitrarily long byte array with the maximum length defined
     * by the amount of available disk storage up to 1,073,741,823.
     * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Bit-string-data-types.html
     */
    public const string BLOB = 'BLOB' ;

    /**
     * CHAR corresponds to a null‑terminated character string with the length specified.
     * Values are padded with blanks to the specified length.
     * The default length is 1. The maximum length is 2,000 characters.
     * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Character-data-types.html
     */
    public const string CHAR = 'CHAR' ;

    /**
     * CHARACTER VARYING, CHAR VARYING, and VARCHAR corresponds to a variable‑length character string
     * with the maximum length specified.
     * The default length is 1 character. The maximum length is 31,995 characters.
     * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Character-data-types.html
     */
    public const string CHAR_VARYING = 'CHAR VARYING' ;

    /**
     * CLOB correspond to a variable‑length character string with the maximum length specified.
     * CLOB has a maximum length of 1,073,741,823. A CLOB is an object of data type CLOB.
     * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Character-data-types.html
     */
    public const string CLOB = 'CLOB' ;

    /**
     * Stores a date value as three parts: year, month, and day. The ranges for the parts are:
     * - Year: 1 to 9999
     * - Month: 1 to 12
     * - Day: Lower limit is 1; the upper limit depends on the month and the year
     * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Date-time-data-types.html
     */
    public const string DATE = 'DATE' ;

    /**
     * Equivalent to type NUMERIC.
     */
    public const string DECIMAL = 'DECIMAL' ;

    public const string DOUBLE_PRECISION = 'DOUBLE PRECISION' ;

    public const string FLOAT = 'FLOAT' ;

    public const string INTEGER = 'INTEGER' ;

    /**
     * Corresponds to an arbitrarily long byte array with the maximum length defined by the amount
     * of available disk storage up to 1,073,741,823.
     * A BLOB is an object of data type LVARBINARY.
     * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Bit-string-data-types.html
     */
    public const string LVARBINARY = 'LVARBINARY' ;

    /**
     * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Character-data-types.html
     */
    public const string LVARCHAR = 'LVARCHAR' ;

    /**
     * Corresponds to a number with the given precision (maximum number of digits)
     * and scale (the number of digits to the right of the decimal point).
     *
     * By default, NUMERIC columns have a precision of 32 and a scale of 0.
     * If NUMERIC columns omit the scale, the default scale is 0.
     *
     * The range of values for a NUMERIC type column is -n to +n where n is the largest number that can be represented with the specified precision and scale.
     * If a value exceeds the precision of a NUMERIC column, SQL generates an overflow error. If a value exceeds the scale of a NUMERIC column, SQL rounds the value.
     *
     * NUMERIC type columns cannot specify a negative scale or specify a scale larger than the precision.
     */
    public const string NUMERIC = 'NUMERIC' ;
    public const string NUMBER  = 'NUMBER' ;

    public const string REAL = 'REAL' ;

    /**
     * Corresponds to an integer value in the range of -32768 to 32767 inclusive.
     */
    public const string SMALLINT = 'SMALLINT' ;

    /**
     * Stores a time value as four parts: hours, minutes, seconds, and milliseconds. The ranges for the parts are:
     * - Hours: 0 to 23
     * - Minutes: 0 to 59
     * - Seconds: 0 to 59
     * - Milliseconds: 0 to 999
     * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Date-time-data-types.html
     */
    public const string TIME = 'TIME' ;

    /**
     * Combines the parts of DATE and TIME
     * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Date-time-data-types.html
     */
    public const string TIMESTAMP = 'TIMESTAMP' ;

    /**
     * Combines the elements of TIMESTAMP with a time zone offset
     * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Date-time-data-types.html
     */
    public const string TIMESTAMP_WITH_TIME_ZONE = 'TIMESTAMP WITH TIME ZONE' ;

    /**
     * Corresponds to an integer value in the range -128 to +127 inclusive.
     * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Exact-numeric-data-types.html
     */
    public const string TINYINT = 'TINYINT' ;

    /**
     * VARARRAY data type allows the size of an individual element value to exceed its declared size
     * as long as the total size of the array is smaller than the array's SQL width.
     */
    public const string VARARRAY = 'VARARRAY' ;

    /**
     * Corresponds to a variable‑length bit field of the specified length in bytes.
     * The default length is 1 byte. The maximum length is 31,995 bytes. The default length is 1.
     * Due to index limitations, only the narrower VARBINARY columns can be indexed.
     * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Bit-string-data-types.html
     */
    public const string VARBINARY = 'VARBINARY' ;

    /**
     * VARCHAR correspond to a variable‑length character string with the maximum length specified.
     * The default length is 1 character. The maximum length is 31,995 characters.
     * @see https://docs.progress.com/bundle/openedge-sql-reference/page/Character-data-types.html
     */
    public const string VARCHAR = 'VARCHAR' ;

    public const string NULL = 'NULL' ;
}