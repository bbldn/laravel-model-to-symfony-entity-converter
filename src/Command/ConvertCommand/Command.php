<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand;

use ReflectionClass;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as Base;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Helper\Helper;
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
        $this->addArgument(
            name: 'inputNamespace',
            mode: InputArgument::OPTIONAL,
            default: 'App\Common\Domain\Model',
        );
        $this->addArgument(
            name: 'exportNamespace',
            mode: InputArgument::OPTIONAL,
            default: 'App\Common\Domain\DoctrineEntity',
        );
    }

    /**
     * @param string $exportNamespace
     * @param ClassLoader $classLoader
     * @return string|null
     */
    private function getExportPath(string $exportNamespace, ClassLoader $classLoader): ?string
    {
        $map = $classLoader->getPrefixesPsr4();

        $currentPath = null;
        $currentNamespace = '';
        $parts = explode('\\', $exportNamespace);
        foreach ($parts as $index => $part) {
            $currentNamespace .= $part . '\\';

            if (true === key_exists($currentNamespace, $map)) {
                $currentPath = $map[$currentNamespace][0] . DIRECTORY_SEPARATOR;
                $array = array_slice($parts, $index + 1);
                if (count($array) > 0) {
                    $currentPath .= implode(DIRECTORY_SEPARATOR, $array);
                }
            }
        }

        return $currentPath;
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
        $classLoaderPath = getcwd() . '/vendor/autoload.php';
        if (false === file_exists($classLoaderPath)) {
            $output->writeln('File vendor/autoload.php not found');

            return self::FAILURE;
        }

        /** @var ClassLoader $classLoader */
        /** @psalm-suppress UnresolvableInclude */
        $classLoader = require $classLoaderPath;

        $oldNamespace = $input->getArgument('inputNamespace');
        $newNamespace = $input->getArgument('exportNamespace');
        $exportPath = $this->getExportPath($newNamespace, $classLoader);

        $astFiller = new AstFiller();
        $reflectionFiller = new ReflectionFiller();

        $entityMap = [];
        foreach ($classLoader->getClassMap() as $className => $ignored) {
            if (true === str_starts_with($className, $oldNamespace)) {
                /** @psalm-var class-string $className */
                $reflectionClass = new ReflectionClass($className);

                $name = $reflectionClass->getShortName();

                $entity = new Entity(name: $name, newNamespace: $newNamespace);
                $reflectionFiller->fill($entity, $reflectionClass);
                $astFiller->fill($entity, $reflectionClass);

                if (count($entity->properties) > 0) {
                    $entityMap[$name] = $entity;
                }
            }
        }

        Helper::fillMappedByAndInversedBy($entityMap);

        $classGenerator = new ClassGenerator();
        foreach ($entityMap as $entity) {
            file_put_contents(
                filename: "$exportPath/$entity->name.php",
                data: $classGenerator->generate($newNamespace, $entity),
            );
        }

        return self::SUCCESS;
    }
}