<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\ClassGenerator\MethodsGenerator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace as ClassNamespace;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\HasManyType;

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
        $collectionFieldMap = [];
        foreach ($classType->getProperties() as $classProperty) {
            $propertyName = $classProperty->getName();
            if (true === key_exists($propertyName, $entity->properties)) {
                $laravelType = $entity->properties[$propertyName]->type;
                if (true === is_a($laravelType, HasManyType::class)) {
                    $collectionFieldMap[$propertyName] = $laravelType->name;
                }
            }
        }

        if (count($collectionFieldMap) > 0) {
            $classNamespace->addUse('Doctrine\Common\Collections\ArrayCollection');

            $list = [];
            foreach ($collectionFieldMap as $field => $ignored) {
                $list[] = "\$this->$field = new ArrayCollection();";
            }

            $classType->addMethod('__construct')->setPublic()->setBody(implode(PHP_EOL, $list));
        }

        foreach ($classType->getProperties() as $classProperty) {
            $nullable = $classProperty->isNullable();
            $propertyName = $classProperty->getName();
            $type = (string)$classProperty->getType();

            if (false === key_exists($propertyName, $collectionFieldMap)) {
                $doctypeType = $classNamespace->simplifyName($type);
                if (true === $nullable) {
                    $doctypeType = "null|$doctypeType";
                }
            } else {
                $doctypeType = $classNamespace->simplifyName($collectionFieldMap[$propertyName]);
                $doctypeType = "Collection<int, $doctypeType>";
            }

            /* Getter | Start */
            $getterMethod = $classType->addMethod('get' . ucfirst($propertyName))
                ->setPublic()
                ->setReturnType($type)
                ->setReturnNullable($nullable)
                ->setBody("return \$this->$propertyName;");
            $getterMethod->addComment("@return $doctypeType");
            /* Getter | End */

            if (false === key_exists($propertyName, $collectionFieldMap)) {
                /* Setter | Start */
                $setterMethod = $classType->addMethod('set' . ucfirst($propertyName))
                    ->setPublic()
                    ->setReturnType('self')
                    ->setBody("\$this->$propertyName = $$propertyName;" . PHP_EOL . PHP_EOL . 'return $this;');
                $setterMethod->addParameter($propertyName)->setType($type)->setNullable($nullable);

                $setterMethod->addComment("@param $doctypeType $$propertyName");
                $setterMethod->addComment('@return self');
                /* Setter | End */
            }
        }
    }
}