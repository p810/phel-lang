<?php

declare(strict_types=1);

namespace Phel\Commands;

use Phel\GlobalEnvironment;
use Phel\Lang\Symbol;
use Phel\Lang\Tuple;
use Phel\Lexer;
use Phel\Reader;
use Phel\Runtime;
use RuntimeException;

class Run
{
    private ?Runtime $runtime;

    public function __construct(?Runtime $runtime = null)
    {
        $this->runtime = $runtime;
    }

    public function run(string $currentDirectory, string $fileOrPath): void
    {
        $ns = $fileOrPath;

        if (file_exists($fileOrPath)) {
            $ns = $this->getNamespaceFromFile($fileOrPath);
        }

        $rt = $this->loadRuntime($currentDirectory);
        $result = $rt->loadNs($ns);

        if (!$result) {
            throw new RuntimeException('Cannot load namespace: ' . $ns);
        }
    }

    protected function loadRuntime(string $currentDirectory): Runtime
    {
        if ($this->runtime) {
            return $this->runtime;
        }

        $runtimePath = $currentDirectory
            . DIRECTORY_SEPARATOR . 'vendor'
            . DIRECTORY_SEPARATOR . 'PhelRuntime.php';

        if (file_exists($runtimePath)) {
            return require $runtimePath;
        }

        throw new \RuntimeException('The Runtime could not be loaded from: ' . $runtimePath);
    }

    protected function getNamespaceFromFile(string $path): string
    {
        $lexer = new Lexer();
        $reader = new Reader(new GlobalEnvironment());
        $content = file_get_contents($path);

        try {
            $tokenStream = $lexer->lexString($content);
            $readerResult = $reader->readNext($tokenStream);

            if (!$readerResult) {
                throw new RuntimeException('Cannot read file: ' . $path);
            }

            $ast = $readerResult->getAst();

            if ($ast instanceof Tuple
                && $ast[0] instanceof Symbol
                && $ast[1] instanceof Symbol
                && $ast[0]->getName() === Symbol::NAME_NS
            ) {
                return $ast[1]->getName();
            }

            throw new RuntimeException('Cannot extract namespace from file: ' . $path);
        } catch (\Phel\Exceptions\ReaderException $e) {
            throw new RuntimeException('Cannot parse file: ' . $path);
        }
    }
}
