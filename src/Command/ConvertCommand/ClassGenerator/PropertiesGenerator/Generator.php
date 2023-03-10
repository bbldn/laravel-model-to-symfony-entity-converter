<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\ClassGenerator\PropertiesGenerator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace as ClassNamespace;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper\TypeHelper;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper\StringHelper;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\HasManyType;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Enum\DoctrineTypeEnum;

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
        foreach ($entity->properties as $property) {
            $name = StringHelper::snakeCaseToCamelCase($property->name);

            $type = $property->type;
            $typeName = $type->name;
            if (false === TypeHelper::isSimpleType($typeName)) {
                $classNamespace->addUse($typeName);
            }

            if (true === is_a($type, HasManyType::class)) {
                $doctypeType = $classNamespace->simplifyName($typeName);
                $classNamespace->addUse(DoctrineTypeEnum::COLLECTION);
                $classProperty = $classType->addProperty($name)->setPrivate()->setType(DoctrineTypeEnum::COLLECTION);
                $classProperty->addComment("@var Collection<int, $doctypeType>");
            } else {
                $classProperty = $classType->addProperty($name)->setPrivate()->setType($typeName);
                if (true === $type->nullable) {
                    $classProperty->setNullable()->setInitialized();
                }
            }
        }
    }
}