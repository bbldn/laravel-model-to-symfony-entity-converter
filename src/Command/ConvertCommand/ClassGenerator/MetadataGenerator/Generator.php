<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\ClassGenerator\MetadataGenerator;

use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace as ClassNamespace;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper\TypeHelper;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper\StringHelper;
use \BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\SimpleType;
use \BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\HasOneType;
use \BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\HasManyType;

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
        if (count($classType->getProperties()) > 0) {
            $classNamespace->addUse('Doctrine\DBAL\Types\Types');
        }

        $ormUse = 'Doctrine\ORM\Mapping';
        $classNamespace->addUse($ormUse, 'ORM');

        $classType->addAttribute("$ormUse\Entity");
        $classType->addAttribute("$ormUse\Table", ['name' => StringHelper::toDatabasePropertyName($entity->table)]);

        foreach ($classType->getProperties() as $classProperty) {
            $name = StringHelper::camelCaseToSnakeCase($classProperty->getName());
            if (false === key_exists($name, $entity->properties)) {
                continue;
            }

            $entityProperty = $entity->properties[$name];

            /* Primary Key | Start */
            if (true === $entityProperty->isPrimary) {
                $classProperty->addAttribute("$ormUse\Id");
            }
            /* Primary Key | End */

            /* Autoincrement | Start */
            if (true === $entityProperty->autoincrement) {
                $classProperty->addAttribute("$ormUse\GeneratedValue");
            }
            /* Autoincrement | End */

            $propertyType = $entityProperty->type;
            switch (get_class($propertyType)) {
                case SimpleType::class:
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

                    $classProperty->addAttribute("$ormUse\Column", $args);
                    /* Column | End */

                    break;
                case HasOneType::class:
                    /** @var HasOneType $propertyType */

                    /* ManyToOne | Start */
                    $classProperty->addAttribute("$ormUse\ManyToOne", [
                        'targetEntity' => new Literal(
                            value: sprintf('%s::class', $classNamespace->simplifyName($propertyType->name))
                        ),
                    ]);
                    /* ManyToOne | End */

                    /* JoinColumn | Start */
                    $classProperty->addAttribute("$ormUse\JoinColumn", [
                        'name' => StringHelper::toDatabasePropertyName($propertyType->localKey),
                        'referencedColumnName' => StringHelper::toDatabasePropertyName($propertyType->foreignKey),
                    ]);
                    /* JoinColumn | End */

                    break;
                case HasManyType::class:
                    /** @var HasManyType $propertyType */

                    /* OneToMany | Start */
                    $classProperty->addAttribute("$ormUse\OneToMany", [
                        'orphanRemoval' => true,
                        'fetch' => 'EXTRA_LAZY',
                        'cascade' => ['persist', 'remove'],
                        'targetEntity' => new Literal(
                            value: sprintf('%s::class', $classNamespace->simplifyType($propertyType->name))
                        ),
                    ]);
                    /* OneToMany | End */

                    break;
            }
        }
    }
}