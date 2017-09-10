<?php
namespace Acelaya\Test\Doctrine\Console;

use Acelaya\Doctrine\Console\ConsoleHelper;
use Acelaya\Doctrine\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleHelperTest extends TestCase
{
    /**
     * @test
     */
    public function printConfigMissingErrorOutputsError()
    {
        $output = $this->prophesize(OutputInterface::class);

        ConsoleHelper::printConfigMissingError($output->reveal());

        $output->writeln(Argument::cetera())->shouldHaveBeenCalledTimes(3);
    }

    /**
     * @test
     */
    public function createAppReturnsApplication()
    {
        $app = ConsoleHelper::createApp(['enum_types' => []]);

        $this->assertInstanceOf(Application::class, $app);
        $this->assertTrue($app->has('det:dump-type-files'));
    }

    /**
     * @test
     */
    public function createAppThrowsExceptionIfInvalidConfigIsProvided()
    {
        $this->expectException(InvalidArgumentException::class);
        ConsoleHelper::createApp([]);
    }
}
