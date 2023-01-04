<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler;

use ReflectionClass;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Property;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper\TypeHelper;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Enum\LaravelModelEnum;

class ReflectionFiller
{
    /**
     * @param ReflectionClass $reflectionClass
     * @return bool
     * @noinspection RedundantSuppression
     * @noinspection PhpUndefinedClassInspection
     * @noinspection PhpUndefinedNamespaceInspection
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    private function validate(ReflectionClass $reflectionClass): bool
    {
        return (
            false === $reflectionClass->isAbstract()
            && false === $reflectionClass->isInterface()
            && true === $reflectionClass->isSubclassOf(\Illuminate\Database\Eloquent\Model::class)
        );
    }

    /**
     * @param Entity $entity
     * @param ReflectionClass $reflectionClass
     * @return void
     */
    private function fillTable(Entity $entity, ReflectionClass $reflectionClass): void
    {
        if (true === $reflectionClass->hasProperty(LaravelModelEnum::TABLE)) {
            $reflectionProperty = $reflectionClass->getProperty(LaravelModelEnum::TABLE);
            if (true === $reflectionProperty->hasDefaultValue()) {
                $entity->table = (string)$reflectionProperty->getDefaultValue();
            }
        }
    }

    /**
     * @param Entity $entity
     * @param ReflectionClass $reflectionClass
     * @return void
     */
    private function fillPrimaryKey(Entity $entity, ReflectionClass $reflectionClass): void
    {
        if (true === $reflectionClass->hasProperty(LaravelModelEnum::PRIMARY_KEY)) {
            $reflectionProperty = $reflectionClass->getProperty(LaravelModelEnum::PRIMARY_KEY);
            if (true === $reflectionProperty->hasDefaultValue()) {
                $primaryKey = $reflectionProperty->getDefaultValue();
                if (false === is_array($primaryKey)) {
                    $primaryKey = (string)$primaryKey;
                    if (false === key_exists($primaryKey, $entity->properties)) {
                        $entity->properties[$primaryKey] = new Property(
                            type: 'int',
                            name: $primaryKey,
                            isPrimary: true,
                        );
                    } else {
                        $entity->properties[$primaryKey]->isPrimary = true;
                    }

                    if (true === $reflectionClass->hasProperty(LaravelModelEnum::INCREMENTING)) {
                        $reflectionProperty = $reflectionClass->getProperty(LaravelModelEnum::INCREMENTING);
                        if (true === $reflectionProperty->hasDefaultValue()) {
                            $entity->properties[$primaryKey]->autoincrement = true === $reflectionProperty->getDefaultValue();
                        }
                    }
                } else {
                    /** @var list<string> $primaryKey */
                    foreach ($primaryKey as $item) {
                        if (true === key_exists($item, $entity->properties)) {
                            $entity->properties[$item]->isPrimary = true;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Entity $entity
     * @param ReflectionClass $reflectionClass
     * @return void
     */
    private function fillByFillable(Entity $entity, ReflectionClass $reflectionClass): void
    {
        if (true === $reflectionClass->hasProperty(LaravelModelEnum::FILLABLE)) {
            $reflectionProperty = $reflectionClass->getProperty(LaravelModelEnum::FILLABLE);
            if (true === $reflectionProperty->hasDefaultValue()) {
                $fillable = $reflectionProperty->getDefaultValue();
                if (true === is_array($fillable)) {
                    /** @var array<array-key, string> $fillable */
                    foreach ($fillable as $propertyName) {
                        if (false === key_exists($propertyName, $entity->properties)) {
                            $entity->properties[$propertyName] = new Property(
                                type: 'string',
                                name: $propertyName,
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Entity $entity
     * @param ReflectionClass $reflectionClass
     * @return void
     */
    private function fillByCast(Entity $entity, ReflectionClass $reflectionClass): void
    {
        if (true === $reflectionClass->hasProperty(LaravelModelEnum::CASTS)) {
            $reflectionProperty = $reflectionClass->getProperty(LaravelModelEnum::CASTS);
            if (true === $reflectionProperty->hasDefaultValue()) {
                $casts = $reflectionProperty->getDefaultValue();
                if (true === is_array($casts)) {
                    /** @var array<string, string> $casts */
                    foreach ($casts as $propertyName => $propertyType) {
                        $type = TypeHelper::getSimpleType($propertyType);
                        if (null !== $type) {
                            if (false === key_exists($propertyName, $entity->properties)) {
                                $entity->properties[$propertyName] = new Property(
                                    type: $type,
                                    name: $propertyName,
                                );
                            } else {
                                $entity->properties[$propertyName]->type = $type;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Entity $entity
     * @param ReflectionClass $reflectionClass
     * @return bool
     */
    public function fill(Entity $entity, ReflectionClass $reflectionClass): bool
    {
        if (true === $this->validate($reflectionClass)) {
            $this->fillTable($entity, $reflectionClass);
            $this->fillByCast($entity, $reflectionClass);
            $this->fillByFillable($entity, $reflectionClass);
            $this->fillPrimaryKey($entity, $reflectionClass);

            return true;
        }

        return false;
    }
}