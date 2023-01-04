<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper;

final class StringHelper
{
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
    public static function prepareTableTable(string $tableName): string
    {
        return sprintf('`%s`', trim($tableName, '`'));
    }

    private function __construct()
    {
    }
}