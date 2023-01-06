<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper;

use ReflectionClass;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\HasOneType;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\HasManyType;

final class Helper
{
    /**
     * @param array<string, Entity> $entityMap
     * @return void
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
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
                            /** @psalm-var class-string $className */
                            $className = $type->name;
                            $reflectionClass = new ReflectionClass($className);
                            $shortClassName = $reflectionClass->getShortName();
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
                            /** @psalm-var class-string $className */
                            $className = $type->name;
                            $reflectionClass = new ReflectionClass($className);
                            $shortClassName = $reflectionClass->getShortName();
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

    private function __construct()
    {
    }
}