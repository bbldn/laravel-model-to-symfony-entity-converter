<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Enum;

final class DoctrineTypeEnum
{
    public const TYPES = 'Doctrine\DBAL\Types\Types';

    public const COLLECTION = 'Doctrine\Common\Collections\Collection';

    public const ARRAY_COLLECTION = 'Doctrine\Common\Collections\ArrayCollection';

    private function __construct()
    {
    }
}