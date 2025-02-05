<?php

declare(strict_types=1);

namespace PhelTest\Integration\Command\Run;

use Gacela\Framework\Config;
use PhelTest\Integration\Command\AbstractCommandTest;
use Symfony\Component\Console\Input\InputInterface;

final class RunCommandTest extends AbstractCommandTest
{
    public static function setUpBeforeClass(): void
    {
        Config::setApplicationRootDir(__DIR__);
    }

    public function testRunByNamespace(): void
    {
        $this->expectOutputRegex('~hello world~');

        $this->createCommandFactory()
            ->createRunCommand()
            ->addRuntimePath('test\\', [__DIR__ . '/Fixtures'])
            ->run(
                $this->stubInput('test\\test-script'),
                $this->stubOutput()
            );
    }

    public function testRunByFilename(): void
    {
        $this->expectOutputRegex('~hello world~');

        $this->createCommandFactory()
            ->createRunCommand()
            ->addRuntimePath('test\\', [__DIR__ . '/Fixtures'])
            ->run(
                $this->stubInput(__DIR__ . '/Fixtures/test-script.phel'),
                $this->stubOutput()
            );
    }

    public function testCannotParseFile(): void
    {
        $filename = __DIR__ . '/Fixtures/test-script-not-parsable.phel';
        $this->expectOutputRegex(sprintf('~Cannot parse file: %s~', $filename));

        $this->createCommandFactory()
            ->createRunCommand()
            ->run($this->stubInput($filename), $this->stubOutput());
    }

    public function testCannotReadFile(): void
    {
        $filename = __DIR__ . '/Fixtures/this-file-does-not-exist.phel';
        $this->expectOutputRegex(sprintf('~Cannot load namespace: %s~', $filename));

        $this->createCommandFactory()
            ->createRunCommand()
            ->run($this->stubInput($filename), $this->stubOutput());
    }

    public function testFileWithoutNs(): void
    {
        $filename = __DIR__ . '/Fixtures/test-script-without-ns.phel';
        $this->expectOutputRegex(sprintf('~Cannot extract namespace from file: %s~', $filename));

        $this->createCommandFactory()
            ->createRunCommand()
            ->run($this->stubInput($filename), $this->stubOutput());
    }

    private function stubInput(string $path): InputInterface
    {
        $input = $this->createStub(InputInterface::class);
        $input->method('getArgument')->willReturn($path);

        return $input;
    }
}
