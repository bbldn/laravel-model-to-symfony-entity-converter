<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\ClassGenerator;

use Nette\PhpGenerator\PhpFile;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper\Helper;
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
     * @param string $namespace
     * @param Entity $entity
     * @return string
     */
    public function generate(string $namespace, Entity $entity): string
    {
        Helper::sortEntityProperties($entity);

        $phpFile = new PhpFile;

        $classNamespace = $phpFile->addNamespace($namespace);
        $classType = $classNamespace->addClass($entity->name);

        $this->propertiesGenerator->generate($entity, $classType, $classNamespace);
        $this->methodsGenerator->generate($entity, $classType, $classNamespace);
        $this->metadataGenerator->generate($entity, $classType, $classNamespace);

        return rtrim($this->psrPrinter->printFile($phpFile));
    }
}