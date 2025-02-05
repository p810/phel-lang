<?php

declare(strict_types=1);

namespace PhelTest\Integration\Command\Test;

use Gacela\Framework\Config;
use PhelTest\Integration\Command\AbstractCommandTest;
use Symfony\Component\Console\Input\InputInterface;

final class TestCommandTest extends AbstractCommandTest
{
    public static function setUpBeforeClass(): void
    {
        Config::setApplicationRootDir(__DIR__);
    }

    public function testAllInProject(): void
    {
        $currentDir = __DIR__ . '/Fixtures/test-cmd-project-success/';

        $command = $this
            ->createCommandFactory()
            ->createTestCommand()
            ->addRuntimePath('test-cmd-project-success\\', [$currentDir]);

        $this->expectOutputRegex('/\.\..*/');
        $this->expectOutputRegex('/.*Passed: 2.*/');
        $this->expectOutputRegex('/.*Failed: 0.*/');
        $this->expectOutputRegex('/.*Error: 0.*/');
        $this->expectOutputRegex('/.*Total: 2.*/');

        $command->run($this->stubInput([]), $this->stubOutput());
    }

    public function testOneFileInProject(): void
    {
        $currentDir = __DIR__ . '/Fixtures/test-cmd-project-success/';

        $command = $this
            ->createCommandFactory()
            ->createTestCommand()
            ->addRuntimePath('test-cmd-project-success\\', [$currentDir]);

        $this->expectOutputRegex('/\..*/');
        $this->expectOutputRegex('/.*Passed: 1.*/');
        $this->expectOutputRegex('/.*Failed: 0.*/');
        $this->expectOutputRegex('/.*Error: 0.*/');
        $this->expectOutputRegex('/.*Total: 1.*/');

        $command->run(
            $this->stubInput([$currentDir . '/test1.phel']),
            $this->stubOutput()
        );
    }

    public function testAllInFailedProject(): void
    {
        $currentDir = __DIR__ . '/Fixtures/test-cmd-project-failure/';

        $command = $this
            ->createCommandFactory()
            ->createTestCommand()
            ->addRuntimePath('test-cmd-project-failure\\', [$currentDir]);

        $this->expectOutputRegex('/E.*/');
        $this->expectOutputRegex('/.*Passed: 0.*/');
        $this->expectOutputRegex('/.*Failed: 1.*/');
        $this->expectOutputRegex('/.*Error: 0.*/');
        $this->expectOutputRegex('/.*Total: 1.*/');

        $command->run($this->stubInput([]), $this->stubOutput());
    }

    private function stubInput(array $paths): InputInterface
    {
        $input = $this->createStub(InputInterface::class);
        $input->method('getArgument')->willReturn($paths);

        return $input;
    }
}
