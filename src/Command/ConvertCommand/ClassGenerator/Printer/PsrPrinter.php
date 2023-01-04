<?php

namespace BBLDN\LaravelModelToSymfonyEntityConverter\Command\ConvertCommand\ClassGenerator\Printer;

use Nette\PhpGenerator\Printer;

final class PsrPrinter extends Printer
{
    public string $indentation = '    ';

    public int $linesBetweenMethods = 1;

    public int $linesBetweenUseTypes = 1;

    public int $linesBetweenProperties = 1;
}