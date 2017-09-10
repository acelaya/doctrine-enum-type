<?php
namespace Acelaya\Test\Doctrine\Registrator;

use Acelaya\Doctrine\Exception\InvalidArgumentException;
use Acelaya\Doctrine\Registrator\EnumTypeRegistrator;
use Acelaya\Test\Doctrine\Enum\Action;
use Acelaya\Test\Doctrine\Enum\Gender;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Filesystem\Filesystem;

class EnumTypeRegistratorTest extends TestCase
{
    /**
     * @var EnumTypeRegistrator
     */
    private $registrator;
    /**
     * @var ObjectProphecy
     */
    private $filesystem;

    public function setUp()
    {
        $this->filesystem = $this->prophesize(Filesystem::class);
        $this->registrator = new EnumTypeRegistrator([], $this->filesystem->reveal());

        // Before every test, clean registered types
        $refProp = new \ReflectionProperty(Type::class, '_typeObjects');
        $refProp->setAccessible(true);
        $refProp->setValue(null, []);
        $refProp = new \ReflectionProperty(Type::class, '_typesMap');
        $refProp->setAccessible(true);
        $refProp->setValue(null, []);
    }

    /**
     * @test
     */
    public function exceptionIsThrownIfTypeDoesNotExtendEnum()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->registrator->registerEnumType(\stdClass::class);
    }

    /**
     * @test
     */
    public function typesAreProperlyRegisteredIfExtendEnum()
    {
        $this->assertFalse(Type::hasType(Action::class));
        $this->registrator->registerEnumType(Action::class);
        $this->assertTrue(Type::hasType(Action::class));
    }

    /**
     * @test
     */
    public function oneFileIsGeneratedForEveryType()
    {
        /** @var MethodProphecy $dumpFile */
        $dumpFile = $this->filesystem->dumpFile(Argument::cetera())->willReturn(null);

        $this->registrator->registerEnumTypes([
            Action::class,
            'gender_type' => Gender::class,
        ]);

        $dumpFile->shouldHaveBeenCalledTimes(2);
    }

    /**
     * @test
     */
    public function oneAutoloaderIsRegisteredOnFirstEnumRegistration()
    {
        $originalCount = count(spl_autoload_functions());

        $this->registrator->registerEnumTypes([
            Action::class,
            Gender::class,
        ]);

        $this->assertCount($originalCount + 1, spl_autoload_functions());
    }
}
