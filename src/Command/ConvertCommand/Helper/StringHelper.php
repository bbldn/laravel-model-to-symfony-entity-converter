<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper;

final class StringHelper
{
    /**
     * @param string $string
     * @return string
     */
    public static function camelCaseToSnakeCase(string $string): string
    {
        return mb_strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    /**
     * @param string $string
     * @return string
     */
    public static function snakeCaseToCamelCase(string $string): string
    {
        $array = explode('_', $string);
        foreach ($array as $index => $value) {
            if ($index > 0) {
                $array[$index] = ucfirst($value);
            }
        }

        return implode($array);
    }

    /**
     * @param string $tableName
     * @return string
     */
    public static function toDatabasePropertyName(string $tableName): string
    {
        return sprintf('`%s`', trim($tableName, '`'));
    }

    private function __construct()
    {
    }
}