<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\ClassGenerator;

use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace as ClassNamespace;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper\TypeHelper;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper\StringHelper;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\ClassGenerator\Printer\PsrPrinter;

class Generator
{
    private PsrPrinter $psrPrinter;

    public function __construct()
    {
        $this->psrPrinter = new PsrPrinter();
    }

    /**
     * @param Entity $entity
     * @param ClassType $classType
     * @param ClassNamespace $classNamespace
     * @return void
     */
    private function generateProperties(
        Entity $entity,
        ClassType $classType,
        ClassNamespace $classNamespace
    ): void
    {
        foreach ($entity->properties as $property) {
            $type = $property->type;
            if (false === TypeHelper::isSimpleType($type)) {
                $classNamespace->addUse($type);
            }

            $type = $classNamespace->simplifyType($type);
            $name = StringHelper::snakeCaseToCamelCase($property->name);

            $classProperty = $classType->addProperty($name)->setPrivate();
            if (true === $property->nullable) {
                $classProperty->setType("null|$type")->setInitialized();
            } else {
                $classProperty->setType($type);
            }
        }
    }

    /**
     * @param ClassType $classType
     * @return void
     */
    private function generateGettersAndSetters(ClassType $classType): void
    {
        foreach ($classType->getProperties() as $classProperty) {
            $propertyName = $classProperty->getName();
            $propertyType = $classProperty->getType();

            /* Getter | Start */
            $getterMethod = $classType->addMethod('get' . ucfirst($propertyName))
                ->setPublic()
                ->setReturnType($propertyType)
                ->setBody("return \$this->$propertyName;");
            $getterMethod->addComment("@return $propertyType");
            /* Getter | End */

            /* Setter | Start */
            $setterMethod = $classType->addMethod('set' . ucfirst($propertyName))
                ->setPublic()
                ->setReturnType('self')
                ->setBody("\$this->$propertyName = $$propertyName;" . PHP_EOL . PHP_EOL . 'return $this;');
            $setterMethod->addParameter($propertyName)->setType($propertyType);

            $setterMethod->addComment("@param $propertyType $$propertyName");
            $setterMethod->addComment('@return self');
            /* Setter | End */
        }
    }

    /**
     * @param Entity $entity
     * @param ClassType $classType
     * @param ClassNamespace $classNamespace
     * @return void
     */
    private function generateDoctrineMetadata(
        Entity $entity,
        ClassType $classType,
        ClassNamespace $classNamespace
    ): void
    {
        $ormUse = 'Doctrine\ORM\Mapping';

        $classNamespace->addUse($ormUse, 'ORM');

        $classType->addAttribute("$ormUse\Entity");
        $classType->addAttribute("$ormUse\Table", ['name' => StringHelper::prepareTableTable($entity->table)]);
    }

    /**
     * @param string $namespace
     * @param Entity $entity
     * @return string
     */
    public function generate(string $namespace, Entity $entity): string
    {
        $phpFile = new PhpFile;

        $classNamespace = $phpFile->addNamespace($namespace);
        $classType = $classNamespace->addClass($entity->name);

        $this->generateProperties($entity, $classType, $classNamespace);
        $this->generateGettersAndSetters($classType);
        $this->generateDoctrineMetadata($entity, $classType, $classNamespace);

        return $this->psrPrinter->printFile($phpFile);
    }
}