<?php
namespace Acelaya\Test\Doctrine\Console;

use Acelaya\Doctrine\Console\DumpTypeClassesCommand;
use Acelaya\Doctrine\Exception\InvalidArgumentException;
use Acelaya\Doctrine\Registrator\EnumTypeRegistrator;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class DumpTypeClassesCommandTest extends TestCase
{
    /**
     * @var DumpTypeClassesCommand
     */
    private $command;
    /**
     * @var CommandTester
     */
    private $commandTester;
    /**
     * @var ObjectProphecy
     */
    private $registrator;

    public function setUp()
    {
        $this->registrator = $this->prophesize(EnumTypeRegistrator::class);
        $this->command = new DumpTypeClassesCommand($this->registrator->reveal(), []);

        $app = new Application();
        $app->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * @test
     */
    public function correctExecutionPrintsSuccess()
    {
        /** @var MethodProphecy $registerTypes */
        $registerTypes = $this->registrator->registerEnumTypes([])->willReturn(null);

        $this->commandTester->execute([]);

        $this->assertContains('Success!', $this->commandTester->getDisplay());
        $registerTypes->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function exceptionPrintsError()
    {
        /** @var MethodProphecy $registerTypes */
        $registerTypes = $this->registrator->registerEnumTypes([])->willThrow(InvalidArgumentException::class);

        $this->commandTester->execute([]);

        $this->assertContains('Error!', $this->commandTester->getDisplay());
        $registerTypes->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function exceptionIsPrintedInVerboseMode()
    {
        /** @var MethodProphecy $registerTypes */
        $registerTypes = $this->registrator->registerEnumTypes([])->willThrow(new InvalidArgumentException(
            'The exception message'
        ));

        $this->commandTester->execute([], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        $this->assertContains('The exception message', $this->commandTester->getDisplay());
        $registerTypes->shouldHaveBeenCalled();
    }
}
