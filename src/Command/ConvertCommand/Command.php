<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand;

use ReflectionClass;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as Base;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\Filler as AstFiller;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\ClassGenerator\Generator as ClassGenerator;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\ReflectionFiller\Filler as ReflectionFiller;

class Command extends Base
{
    protected static $defaultName = 'convert';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('inputNamespace', InputArgument::OPTIONAL);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $namespace = 'App\Common\Domain\Model';

        /** @var ClassLoader $classLoader */
        $classLoader = require 'vendor/autoload.php';

        $reflectionFiller = new ReflectionFiller();
        $astFiller = new AstFiller();

        $entityList = [];
        foreach ($classLoader->getClassMap() as $className => $ignored) {
            if (true === str_starts_with($className, $namespace)) {
                /** @psalm-var class-string $className */
                $reflectionClass = new ReflectionClass($className);

                $entity = new Entity($reflectionClass->getShortName());
                $reflectionFiller->fill($entity, $reflectionClass);
                $astFiller->fill($entity, $reflectionClass);

                if (count($entity->properties) > 0) {
                    $entityList[] = $entity;
                }
            }
        }

        $classGenerator = new ClassGenerator();
        foreach ($entityList as $entity) {
            $classText = $classGenerator->generate('App\Common\Domain\DoctrineEntity', $entity);
            $path = "/home/user/PhpstormProjects/DirectLine/FlowersDelivery/FlowersDeliveryViewerTest/app/Common/Domain/DoctrineEntity/$entity->name.php";

            file_put_contents($path, $classText);
        }

        return self::SUCCESS;
    }
}