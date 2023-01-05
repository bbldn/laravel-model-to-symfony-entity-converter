<?php
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Enum;

final class LaravelTypeEnum
{
    /** @psalm-suppress UndefinedClass */
    public const MODEL = Illuminate\Database\Eloquent\Model::class;

    /** @psalm-suppress UndefinedClass */
    public const HAS_ONE = Illuminate\Database\Eloquent\Relations\HasOne::class;

    /** @psalm-suppress UndefinedClass */
    public const HAS_MANY = Illuminate\Database\Eloquent\Relations\HasMany::class;

    private function __construct()
    {
    }
}