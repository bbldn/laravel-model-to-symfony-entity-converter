<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\ClassGenerator\MetadataGenerator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace as ClassNamespace;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper\TypeHelper;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper\StringHelper;

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

            /* Column | Start */
            $args = [
                'name' => StringHelper::toDatabasePropertyName($entityProperty->name),
            ];

            $type = TypeHelper::getDoctrineType($entityProperty->type->name);
            if (null !== $type) {
                $args['type'] = $type;
            }

            if (true === $entityProperty->type->nullable && false === $entityProperty->isPrimary) {
                $args['nullable'] = true;
            }

            $classProperty->addAttribute("$ormUse\Column", $args);
            /* Column | End */
        }
    }
}