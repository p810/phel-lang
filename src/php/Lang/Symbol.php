<?php

declare(strict_types=1);

namespace Phel\Lang;

use Phel\Printer;

final class Symbol extends AbstractType implements IIdentical
{
    public const NAME_DEF = 'def';
    public const NAME_NS = 'ns';
    public const NAME_FN = 'fn';
    public const NAME_QUOTE = 'quote';
    public const NAME_DO = 'do';
    public const NAME_IF = 'if';
    public const NAME_APPLY = 'apply';
    public const NAME_LET = 'let';
    public const NAME_PHP_NEW = 'php/new';
    public const NAME_PHP_OBJECT_CALL = 'php/->';
    public const NAME_PHP_OBJECT_STATIC_CALL = 'php/::';
    public const NAME_PHP_ARRAY_GET = 'php/aget';
    public const NAME_PHP_ARRAY_SET = 'php/aset';
    public const NAME_PHP_ARRAY_PUSH = 'php/apush';
    public const NAME_PHP_ARRAY_UNSET = 'php/aunset';
    public const NAME_RECUR = 'recur';
    public const NAME_TRY = 'try';
    public const NAME_THROW = 'throw';
    public const NAME_LOOP = 'loop';
    public const NAME_FOREACH = 'foreach';
    public const NAME_DEFSTRUCT = 'defstruct*';

    private static int $symGenCounter = 1;

    private ?string $namespace;

    private string $name;

    public function __construct(?string $namespace, string $name)
    {
        $this->namespace = $namespace;
        $this->name = $name;
    }

    public static function create($name)
    {
        $pos = strpos($name, '/');

        if ($pos === false || $name === '/') {
            return new Symbol(null, $name);
        }

        return new Symbol(substr($name, 0, $pos), substr($name, $pos + 1));
    }

    public static function createForNamespace($namespace, $name)
    {
        return new Symbol($namespace, $name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function getFullName(): string
    {
        if ($this->namespace) {
            return $this->namespace . '/' . $this->name;
        }

        return $this->name;
    }

    public function __toString(): string
    {
        return Printer::readable()->print($this);
    }

    public static function gen(string $prefix = '__phel_'): Symbol
    {
        return Symbol::create($prefix . (self::$symGenCounter++));
    }

    public static function resetGen(): void
    {
        self::$symGenCounter = 1;
    }

    public function hash(): string
    {
        return $this->getName();
    }

    public function equals($other): bool
    {
        return $other instanceof Symbol
            && $this->name === $other->getName()
            && $this->namespace === $other->getNamespace();
    }

    public function identical($other): bool
    {
        return $this->equals($other);
    }
}
