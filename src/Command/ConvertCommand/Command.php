<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand;

use ReflectionClass;
use Composer\Autoload\ClassLoader;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command as Base;
use Symfony\Component\Console\Output\OutputInterface;
use \BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\ClassGenerator\Generator as ClassGenerator;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\ReflectionFiller;

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
     * @return PhpDocParser
     */
    private function createPhpDocParser(): PhpDocParser
    {
        $constExprParser = new ConstExprParser();
        $typeParser = new TypeParser($constExprParser);

        return new PhpDocParser($typeParser, $constExprParser);
    }

    private function fillByPhpDoc(Entity $entity, ReflectionClass $reflectionClass): void
    {
        $lexer = new Lexer();
        $tokens = $lexer->tokenize($reflectionClass->getDocComment());
        $phpDocParser = $this->createPhpDocParser();
        $node = $phpDocParser->parse(new TokenIterator($tokens));
        //$node->children
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $namespace = 'App\Common\Domain\Model';

        /** @var ClassLoader $classLoader */
        $classLoader = require 'vendor/autoload.php';

        $reflectionFiller = new ReflectionFiller();

        $entityList = [];
        foreach ($classLoader->getClassMap() as $className => $ignored) {
            if (true === str_starts_with($className, $namespace)) {
                /** @noinspection PhpUnhandledExceptionInspection */
                $reflectionClass = new ReflectionClass($className);

                $entity = new Entity($reflectionClass->getShortName());

                if (true === $reflectionFiller->fill($entity, $reflectionClass)) {
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