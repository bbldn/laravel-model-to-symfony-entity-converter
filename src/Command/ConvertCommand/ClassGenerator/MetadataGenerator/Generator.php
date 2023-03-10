<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\ClassGenerator\MetadataGenerator;

use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace as ClassNamespace;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper\TypeHelper;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper\StringHelper;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\SimpleType;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\HasOneType;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\HasManyType;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Enum\DoctrineTypeEnum;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Enum\DoctrineEntityEnum;

class Generator
{
    /**
     * @param Entity $entity
     * @param ClassType $classType
     * @param ClassNamespace $classNamespace
     * @return void
     */
    public function generate(
        Entity $entity,
        ClassType $classType,
        ClassNamespace $classNamespace
    ): void
    {
        if (0 === count($classType->getProperties())) {
            return;
        }

        $classNamespace->addUse(DoctrineTypeEnum::TYPES);
        $classNamespace->addUse(DoctrineEntityEnum::NAMESPACE, 'ORM');

        $classType->addAttribute(DoctrineEntityEnum::ENTITY);
        $classType->addAttribute(DoctrineEntityEnum::TABLE, [
            'name' => StringHelper::toDatabasePropertyName($entity->table)
        ]);

        foreach ($classType->getProperties() as $classProperty) {
            $name = $classProperty->getName();
            $properties = $entity->properties;
            if (false === key_exists($name, $properties)) {
                $name = StringHelper::camelCaseToSnakeCase($name);
                if (false === key_exists($name, $properties)) {
                    continue;
                }
            }

            $entityProperty = $properties[$name];

            /* Primary Key | Start */
            if (true === $entityProperty->isPrimary) {
                $classProperty->addAttribute(DoctrineEntityEnum::ID);
            }
            /* Primary Key | End */

            /* Autoincrement | Start */
            if (true === $entityProperty->autoincrement) {
                $classProperty->addAttribute(DoctrineEntityEnum::GENERATED_VALUE);
            }
            /* Autoincrement | End */

            $propertyType = $entityProperty->type;
            switch (true) {
                case is_a($propertyType, SimpleType::class):
                    /** @var SimpleType $propertyType */

                    /* Column | Start */
                    $args = [
                        'name' => StringHelper::toDatabasePropertyName($entityProperty->name),
                    ];

                    $type = TypeHelper::getDoctrineType($propertyType->name);
                    if (null !== $type) {
                        $args['type'] = $type;
                    }

                    if (true === $propertyType->nullable && false === $entityProperty->isPrimary) {
                        $args['nullable'] = true;
                    }

                    $classProperty->addAttribute(DoctrineEntityEnum::COLUMN, $args);
                    /* Column | End */

                    break;
                case is_a($propertyType, HasOneType::class):
                    /** @var HasOneType $propertyType */

                    /* ManyToOne | Start */
                    $args = [
                        'targetEntity' => new Literal(
                            value: sprintf('%s::class', $classNamespace->simplifyName($propertyType->name))
                        ),
                    ];

                    $inversedBy = $propertyType->inversedBy;
                    if (null !== $inversedBy) {
                        $args['inversedBy'] = $inversedBy;
                    }

                    $classProperty->addAttribute(DoctrineEntityEnum::MANY_TO_ONE, $args);
                    /* ManyToOne | End */

                    /* JoinColumn | Start */
                    $classProperty->addAttribute(DoctrineEntityEnum::JOIN_COLUMN, [
                        'name' => StringHelper::toDatabasePropertyName($propertyType->localKey),
                        'referencedColumnName' => StringHelper::toDatabasePropertyName($propertyType->foreignKey),
                    ]);
                    /* JoinColumn | End */

                    break;
                case is_a($propertyType, HasManyType::class):
                    /** @var HasManyType $propertyType */

                    /* OneToMany | Start */
                    $args = [
                        'orphanRemoval' => true,
                        'fetch' => 'EXTRA_LAZY',
                        'cascade' => ['persist', 'remove'],
                        'targetEntity' => new Literal(
                            value: sprintf('%s::class', $classNamespace->simplifyType($propertyType->name))
                        ),
                    ];

                    $mappedBy = $propertyType->mappedBy;
                    if (null !== $mappedBy) {
                        $args['mappedBy'] = $mappedBy;
                    }

                    $classProperty->addAttribute(DoctrineEntityEnum::ONE_TO_MANY, $args);
                    /* OneToMany | End */

                    break;
            }
        }
    }
}