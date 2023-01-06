<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\Helper;

use ReflectionClass;
use ReflectionException;
use PhpParser\Node\Expr;
use PhpParser\Node\Name as NameNode;
use PhpParser\Node\Scalar\String_ as StringNode;
use PhpParser\Node\Expr\ClassConstFetch as ClassConstFetchNode;
use BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\Filler\AstFiller\DTO\ClassItem;

final class Helper
{
    /**
     * @param string $type
     * @param string $expectedType
     * @return bool
     *
     * @psalm-param class-string $type
     * @psalm-param class-string $expectedType
     */
    public static function checkType(string $type, string $expectedType): bool
    {
        if ($type === $expectedType) {
            return true;
        }

        try {
            $reflectionClass = new ReflectionClass($type);
        } catch (ReflectionException) {
            return false;
        }

        return true === $reflectionClass->isSubclassOf($expectedType);
    }

    /**
     * @param Expr $node
     * @param ClassItem $classItem
     * @return string|null
     */
    public static function parseNodeValue(Expr $node, ClassItem $classItem): ?string
    {
        if (true === is_a($node, StringNode::class)) {
            /** @var StringNode $node */
            return $node->value;
        }

        if (true === is_a($node, ClassConstFetchNode::class)) {
            /** @var ClassConstFetchNode $node */
            $class = $node->class;
            if (true === is_a($class, NameNode::class)) {
                return self::resolveClassName(implode('\\', $class->parts), $classItem);
            }
        }

        return null;
    }

    /**
     * @param string $className
     * @param ClassItem $classItem
     * @return string
     */
    public static function resolveClassName(string $className, ClassItem $classItem): string
    {
        $useMap = $classItem->useMap;
        if (true === key_exists($className, $useMap)) {
            return $useMap[$className];
        }

        $currentNamespace = $classItem->currentNamespace;
        if (mb_strlen($currentNamespace) > 0) {
            return "$currentNamespace\\$className";
        }

        return $className;
    }

    private function __construct()
    {
    }
}