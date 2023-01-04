<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Enum;

final class LaravelModelEnum
{
    public const TABLE = 'table';

    public const CASTS = 'casts';

    public const FILLABLE = 'fillable';

    public const PRIMARY_KEY = 'primaryKey';

    public const INCREMENTING = 'incrementing';

    private function __construct()
    {
    }
}