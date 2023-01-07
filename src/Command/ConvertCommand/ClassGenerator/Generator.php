<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\ClassGenerator;

use Nette\PhpGenerator\PhpFile;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\HasManyType;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\ClassGenerator\Printer\PsrPrinter;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\ClassGenerator\MethodsGenerator\Generator as MethodsGenerator;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\ClassGenerator\MetadataGenerator\Generator as MetadataGenerator;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\ClassGenerator\PropertiesGenerator\Generator as PropertiesGenerator;

class Generator
{
    private PsrPrinter $psrPrinter;

    private MethodsGenerator $methodsGenerator;

    private MetadataGenerator $metadataGenerator;

    private PropertiesGenerator $propertiesGenerator;

    public function __construct()
    {
        $this->psrPrinter = new PsrPrinter();
        $this->methodsGenerator = new MethodsGenerator();
        $this->metadataGenerator = new MetadataGenerator();
        $this->propertiesGenerator = new PropertiesGenerator();
    }

    /**
     * @param Entity $entity
     * @return void
     */
    private function sortProperties(Entity $entity): void
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
     * @param string $namespace
     * @param Entity $entity
     * @return string
     */
    public function generate(string $namespace, Entity $entity): string
    {
        $this->sortProperties($entity);

        $phpFile = new PhpFile;

        $classNamespace = $phpFile->addNamespace($namespace);
        $classType = $classNamespace->addClass($entity->name);

        $this->propertiesGenerator->generate($entity, $classType, $classNamespace);
        $this->methodsGenerator->generate($entity, $classType, $classNamespace);
        $this->metadataGenerator->generate($entity, $classType, $classNamespace);

        return $this->psrPrinter->printFile($phpFile);
    }
}