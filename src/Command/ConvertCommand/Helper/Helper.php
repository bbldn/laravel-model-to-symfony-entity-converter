<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper;

use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\HasOneType;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\HasManyType;

final class Helper
{
    /**
     * @param Entity $entity
     * @return void
     */
    public static function sortEntityProperties(Entity $entity): void
    {
        $properties = [];
        foreach ($entity->properties as $propertyName => $property) {
            if (true === $property->isPrimary) {
                $properties[$propertyName] = $property;
            }
        }

        foreach ($entity->properties as $propertyName => $property) {
            if (false === $property->isPrimary) {
                if (false === is_a($property->type, HasManyType::class)) {
                    $properties[$propertyName] = $property;
                }
            }
        }

        foreach ($entity->properties as $propertyName => $property) {
            if (true === is_a($property->type, HasManyType::class)) {
                $properties[$propertyName] = $property;
            }
        }

        $entity->properties = $properties;
    }

    /**
     * @param array<string, Entity> $entityMap
     * @return void
     */
    public static function fillMappedByAndInversedBy(array $entityMap): void
    {
        foreach ($entityMap as $entity) {
            $entityFullName = "$entity->newNamespace\\$entity->name";
            foreach ($entity->properties as $entityProperty) {
                $type = $entityProperty->type;
                switch (true) {
                    case is_a($type, HasOneType::class):
                        /** @var HasOneType $type */
                        $inversedBy = $type->inversedBy;
                        if (null === $inversedBy) {
                            [, $shortClassName] = self::getNamespaceAndShortClassName($type->name);
                            if (true === key_exists($shortClassName, $entityMap)) {
                                foreach ($entityMap[$shortClassName]->properties as $property) {
                                    $rType = $property->type;
                                    if (true === is_a($rType, HasManyType::class)) {
                                        if ($rType->name === $entityFullName) {
                                            $type->inversedBy = $property->name;
                                        }
                                    }
                                }
                            }
                        }

                        break;
                    case is_a($type, HasManyType::class):
                        /** @var HasManyType $type */
                        $mappedBy = $type->mappedBy;
                        if (null === $mappedBy) {
                            [, $shortClassName] = self::getNamespaceAndShortClassName($type->name);
                            if (true === key_exists($shortClassName, $entityMap)) {
                                $properties = $entityMap[$shortClassName]->properties;

                                $rPropertyName = lcfirst($entity->name);
                                if (true === key_exists($rPropertyName, $properties)) {
                                    $rType = $properties[$rPropertyName]->type;
                                    if (true === is_a($rType, HasOneType::class)) {
                                        if ($rType->name === $entityFullName) {
                                            $type->mappedBy = $rPropertyName;
                                        }
                                    }
                                }
                            }
                        }

                        break;
                }
            }
        }
    }

    /**
     * @param string $fullClassName
     * @return array{0: string, 1: string}
     */
    public static function getNamespaceAndShortClassName(string $fullClassName): array
    {
        $array = explode('\\', $fullClassName);

        $index = count($array) - 1;

        $shortClassName = $array[$index];
        $namespace = implode('\\', array_splice($array, 0, $index));

        return [$namespace, $shortClassName];
    }

    private function __construct()
    {
    }
}