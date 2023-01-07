<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper;

use DateTime;
use DateTimeImmutable;
use Nette\PhpGenerator\Literal;

final class TypeHelper
{
    /** @var array<string, string> */
    private static array $simpleTypeMap = [
        'int' => 'int',
        'bool' => 'bool',
        'null' => 'null',
        'float' => 'float',
        'mixed' => 'mixed',
        'integer' => 'int',
        'boolean' => 'bool',
        'double' => 'float',
        'string' => 'string',
    ];

    /** @var array<string, string> */
    private static array $castTypeMap = [
        'date' => DateTimeImmutable::class,
        'real' => DateTimeImmutable::class,
        'datetime' => DateTimeImmutable::class,
        'timestamp' => DateTimeImmutable::class,
        DateTime::class => DateTimeImmutable::class,
        'immutable_date' => DateTimeImmutable::class,
        'immutable_datetime' => DateTimeImmutable::class,
        DateTimeImmutable::class => DateTimeImmutable::class,
    ];

    /**
     * @param string $type
     * @return bool
     */
    public static function isSimpleType(string $type): bool
    {
        return true === key_exists($type, self::$simpleTypeMap);
    }

    /**
     * @param string $type
     * @return string|null
     */
    public static function getSimpleType(string $type): ?string
    {
        if (true === key_exists($type, self::$castTypeMap)) {
            return self::$castTypeMap[$type];
        }

        if (true === key_exists($type, self::$simpleTypeMap)) {
            return self::$simpleTypeMap[$type];
        }

        return null;
    }

    /**
     * @param string $type
     * @return Literal|null
     */
    public static function getDoctrineType(string $type): ?Literal
    {
        $map = [
            'int' => 'Types::INTEGER',
            'float' => 'Types::FLOAT',
            'bool' => 'Types::BOOLEAN',
            'double' => 'Types::FLOAT',
            'string' => 'Types::STRING',
            'integer' => 'Types::INTEGER',
            'boolean' => 'Types::BOOLEAN',
            'date' => 'Types::DATETIME_IMMUTABLE',
            'real' => 'Types::DATETIME_IMMUTABLE',
            'datetime' => 'Types::DATETIME_IMMUTABLE',
            'timestamp' => 'Types::DATETIME_IMMUTABLE',
            DateTime::class => 'Types::DATETIME_IMMUTABLE',
            'immutable_date' => 'Types::DATETIME_IMMUTABLE',
            'immutable_datetime' => 'Types::DATETIME_IMMUTABLE',
            DateTimeImmutable::class => 'Types::DATETIME_IMMUTABLE',
        ];

        if (true === key_exists($type, $map)) {
            return new Literal($map[$type]);
        }

        return null;
    }
}