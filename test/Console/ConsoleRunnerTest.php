<?php
namespace Acelaya\Test\Doctrine\Console;

use Acelaya\Doctrine\Console\ConsoleRunner;
use Acelaya\Doctrine\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleRunnerTest extends TestCase
{
    /**
     * @test
     */
    public function printConfigMissingErrorOutputsError()
    {
        $output = $this->prophesize(OutputInterface::class);

        ConsoleRunner::printConfigMissingError($output->reveal());

        $output->writeln(Argument::cetera())->shouldHaveBeenCalledTimes(3);
    }

    /**
     * @test
     */
    public function createAppReturnsApplication()
    {
        $app = ConsoleRunner::createApp(['enum_types' => []]);

        $this->assertInstanceOf(Application::class, $app);
        $this->assertTrue($app->has('det:dump-type-files'));
    }

    /**
     * @test
     */
    public function createAppThrowsExceptionIfInvalidConfigIsProvided()
    {
        $this->expectException(InvalidArgumentException::class);
        ConsoleRunner::createApp([]);
    }
}
