<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\FileParser\ClassParser;

use PhpParser\Node\Stmt;
use PhpParser\Node\Name as NodeName;
use PhpParser\Node\Stmt\Use_ as StmtUse;
use PhpParser\Node\Stmt\Class_ as StmtClass;
use PhpParser\Node\Stmt\Namespace_ as StmtNamespace;
use PhpParser\Node\Stmt\ClassMethod as StmtClassMethod;
use \BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Enum\LaravelTypeEnum;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\DTO\ClassItem;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\Helper\Helper;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\FileParser\ClassParser\HasOneMethodParser\Parser as HasOneMethodParser;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\FileParser\ClassParser\HasManyMethodParser\Parser as HasManyMethodParser;

class Parser
{
    private HasOneMethodParser $hasOneMethodParser;

    private HasManyMethodParser $hasManyMethodParser;

    public function __construct()
    {
        $this->hasOneMethodParser = new HasOneMethodParser();
        $this->hasManyMethodParser = new HasManyMethodParser();
    }

    /**
     * @param ClassItem $classItem
     * @param StmtNamespace $stmtNamespace
     * @return void
     */
    private function parseNamespace(ClassItem $classItem, StmtNamespace $stmtNamespace): void
    {
        $name = $stmtNamespace->name;
        if (null !== $name) {
            $classItem->namespace = implode('\\', $name->parts);
        }
    }

    /**
     * @param StmtUse $stmtUse
     * @param ClassItem $classItem
     * @return void
     */
    private function parseUse(StmtUse $stmtUse, ClassItem $classItem): void
    {
        foreach ($stmtUse->uses as $useUse) {
            $parts = $useUse->name->parts;

            $namespace = implode('\\', $parts);
            $classItem->useMap[$namespace] = $namespace;

            $alias = $useUse->alias;
            if (null === $alias) {
                $classItem->useMap[$parts[count($parts) - 1]] = $namespace;
            } else {
                $classItem->useMap[$alias->name] = $namespace;
            }
        }
    }

    /**
     * @param Entity $entity
     * @param ClassItem $classItem
     * @param StmtClass $stmtClass
     * @return void
     */
    private function parseClass(
        Entity $entity,
        ClassItem $classItem,
        StmtClass $stmtClass,
    ): void
    {
        foreach ($stmtClass->stmts as $stmt) {
            if (false === is_a($stmt, StmtClassMethod::class)) {
                continue;
            }

            /** @var StmtClassMethod $stmtClassMethod */
            $stmtClassMethod = $stmt;

            $type = $stmtClassMethod->returnType;
            if (false === is_a($type, NodeName::class)) {
                continue;
            }

            /** @var NodeName $type */

            /** @psalm-var class-string $useType */
            $useType = Helper::resolveClassName(implode('\\', $type->parts), $classItem);
            switch (true) {
                case Helper::checkType($useType, LaravelTypeEnum::HAS_ONE):
                    $this->hasOneMethodParser->parse($entity, $classItem, $stmtClassMethod);
                    break;
                case Helper::checkType($useType, LaravelTypeEnum::HAS_MANY):
                    $this->hasManyMethodParser->parse($entity, $classItem, $stmtClassMethod);
                    break;
            }
        }
    }

    /**
     * @param Stmt $stmt
     * @param Entity $entity
     * @return void
     */
    public function parse(Stmt $stmt, Entity $entity): void
    {
        if (false === is_a($stmt, StmtNamespace::class)) {
            return;
        }

        /** @var StmtNamespace $namespaceStmt */
        $namespaceStmt = $stmt;

        $classItem = new ClassItem();

        $this->parseNamespace($classItem, $namespaceStmt);

        foreach ($namespaceStmt->stmts as $stmt) {
            $stmtType = get_class($stmt);
            switch ($stmtType) {
                case StmtUse::class:
                    /** @var StmtUse $stmt */
                    $this->parseUse($stmt, $classItem);
                    break;
                case StmtClass::class:
                    /** @var StmtClass $stmt */
                    $this->parseClass($entity, $classItem, $stmt);
                    break;
            }
        }
    }
}