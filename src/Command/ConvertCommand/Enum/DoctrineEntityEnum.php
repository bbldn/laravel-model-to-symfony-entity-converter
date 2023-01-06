<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Enum;

final class DoctrineEntityEnum
{
    public const ID = 'Doctrine\ORM\Mapping\Id';

    public const NAMESPACE = 'Doctrine\ORM\Mapping';

    public const TABLE = 'Doctrine\ORM\Mapping\Table';

    public const COLUMN = 'Doctrine\ORM\Mapping\Column';

    public const ENTITY = 'Doctrine\ORM\Mapping\Entity';

    public const ONE_TO_MANY = 'Doctrine\ORM\Mapping\OneToMany';

    public const MANY_TO_ONE = 'Doctrine\ORM\Mapping\ManyToOne';

    public const JOIN_COLUMN = 'Doctrine\ORM\Mapping\JoinColumn';

    public const GENERATED_VALUE = 'Doctrine\ORM\Mapping\GeneratedValue';

    private function __construct()
    {
    }
}