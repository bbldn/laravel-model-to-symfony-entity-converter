<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\FileParser\ClassParser\HasManyMethodParser;

use PhpParser\Node\Arg as NodeArg;
use PhpParser\Node\Stmt\Return_ as StmtReturn;
use PhpParser\Node\Identifier as NodeIdentifier;
use PhpParser\Node\Expr\Variable as ExprVariable;
use PhpParser\Node\Expr\MethodCall as ExprMethodCall;
use PhpParser\Node\Stmt\ClassMethod as StmtClassMethod;
use PhpParser\Node\VariadicPlaceholder as NodeVariadicPlaceholder;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Entity;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Property;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\DTO\Type\HasManyType;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\DTO\ClassItem;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\Helper\Helper;

class Parser
{
    /**
     * @param Entity $entity
     * @param string $methodName
     * @param ClassItem $classItem
     * @param array<NodeArg|NodeVariadicPlaceholder> $args
     * @return void
     */
    private function parseArgs(
        array $args,
        Entity $entity,
        string $methodName,
        ClassItem $classItem,
    ): void
    {
        if (3 !== count($args)) {
            return;
        }

        [$firstArg, $secondArg, $thirdArg] = $args;
        if (
            false === is_a($firstArg, NodeArg::class)
            || false === is_a($thirdArg, NodeArg::class)
            || false === is_a($secondArg, NodeArg::class)
        ) {
            return;
        }

        /** @var NodeArg $firstArg */
        /** @var NodeArg $thirdArg */
        /** @var NodeArg $secondArg */

        $typeName = Helper::parseNodeValue($firstArg->value, $classItem);
        if (null === $typeName) {
            return;
        }

        $localKey = Helper::parseNodeValue($thirdArg->value, $classItem);
        if (null === $localKey) {
            return;
        }

        $foreignKey = Helper::parseNodeValue($secondArg->value, $classItem);
        if (null === $foreignKey) {
            return;
        }

        $entity->properties[$methodName] = new Property(
            name: $methodName,
            type: new HasManyType($typeName, $localKey, $foreignKey),
        );

        if (true === key_exists($localKey, $entity->properties)) {
            if (false === $entity->properties[$localKey]->isPrimary) {
                unset($entity->properties[$localKey]);
            }
        }
    }

    /**
     * @param Entity $entity
     * @param ClassItem $classItem
     * @param StmtClassMethod $stmtClassMethod
     * @return void
     */
    public function parse(
        Entity $entity,
        ClassItem $classItem,
        StmtClassMethod $stmtClassMethod,
    ): void
    {
        $methodName = $stmtClassMethod->name->name;
        foreach ($stmtClassMethod->stmts as $stmt) {
            if (false === is_a($stmt, StmtReturn::class)) {
                continue;
            }

            /** @var StmtReturn $stmt */
            $expr = $stmt->expr;
            if (false === is_a($expr, ExprMethodCall::class)) {
                continue;
            }

            /** @var ExprMethodCall $expr */
            $var = $expr->var;
            if (false === is_a($var, ExprVariable::class)) {
                continue;
            }

            /** @var ExprVariable $var */
            if ('this' !== $var->name) {
                continue;
            }

            $name = $expr->name;
            if (false === is_a($name, NodeIdentifier::class)) {
                continue;
            }

            /** @var NodeIdentifier $name */
            if ('hasMany' !== $name->name) {
                continue;
            }

            $this->parseArgs($expr->args, $entity, $methodName, $classItem);
        }
    }
}