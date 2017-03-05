<?php
namespace Acelaya\Test\Doctrine\Type;

use Acelaya\Doctrine\Exception\InvalidArgumentException;
use Acelaya\Doctrine\Type\PhpEnumType;
use Acelaya\Test\Doctrine\Enum\Action;
use Acelaya\Test\Doctrine\Enum\Gender;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use MyCLabs\Enum\Enum;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class PhpEnumTypeTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    protected $platform;

    public function setUp()
    {
        $this->platform = $this->prophesize(AbstractPlatform::class);

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
    public function enumTypesAreProperlyRegistered()
    {
        $this->assertFalse(Type::hasType(Action::class));
        $this->assertFalse(Type::hasType('gender'));

        PhpEnumType::registerEnumType(Action::class);
        PhpEnumType::registerEnumTypes([
            'gender' => Gender::class,
        ]);

        $this->assertTrue(Type::hasType(Action::class));
        $this->assertTrue(Type::hasType('gender'));
    }

    /**
     * @test
     */
    public function enumTypesAreProperlyCustomizedWhenRegistered()
    {
        $this->assertFalse(Type::hasType(Action::class));
        $this->assertFalse(Type::hasType(Gender::class));

        PhpEnumType::registerEnumTypes([
            'gender' => Gender::class,
            Action::class,
        ]);

        /** @var Type $actionType */
        $actionType = Type::getType(Action::class);
        $this->assertInstanceOf(PhpEnumType::class, $actionType);
        $this->assertEquals(Action::class, $actionType->getName());

        /** @var Type $actionType */
        $genderType = Type::getType('gender');
        $this->assertInstanceOf(PhpEnumType::class, $genderType);
        $this->assertEquals('gender', $genderType->getName());
    }

    /**
     * @test
     */
    public function registerInvalidEnumThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Provided enum class "%s" is not valid. Enums must extend "%s"',
            \stdClass::class,
            Enum::class
        ));
        PhpEnumType::registerEnumType(\stdClass::class);
    }

    /**
     * @test
     */
    public function getSQLDeclarationReturnsValueFromPlatform()
    {
        $this->platform->getVarcharTypeDeclarationSQL(Argument::cetera())->willReturn('declaration');

        PhpEnumType::registerEnumType(Gender::class);
        $type = Type::getType(Gender::class);

        $this->assertEquals('declaration', $type->getSQLDeclaration([], $this->platform->reveal()));
    }

    /**
     * @test
     */
    public function convertToDatabaseValueParsesEnum()
    {
        PhpEnumType::registerEnumType(Action::class);
        $type = Type::getType(Action::class);

        $value = Action::CREATE();
        $this->assertEquals(Action::CREATE, $type->convertToDatabaseValue($value, $this->platform->reveal()));

        $value = Action::READ();
        $this->assertEquals(Action::READ, $type->convertToDatabaseValue($value, $this->platform->reveal()));

        $value = Action::UPDATE();
        $this->assertEquals(Action::UPDATE, $type->convertToDatabaseValue($value, $this->platform->reveal()));

        $value = Action::DELETE();
        $this->assertEquals(Action::DELETE, $type->convertToDatabaseValue($value, $this->platform->reveal()));
    }

    /**
     * @test
     */
    public function convertToPHPValueWithValidValueReturnsParsedData()
    {
        PhpEnumType::registerEnumType(Action::class);
        $type = Type::getType(Action::class);

        /** @var Action $value */
        $value = $type->convertToPHPValue(Action::CREATE, $this->platform->reveal());
        $this->assertInstanceOf(Action::class, $value);
        $this->assertEquals(Action::CREATE, $value->getValue());

        $value = $type->convertToPHPValue(Action::DELETE, $this->platform->reveal());
        $this->assertInstanceOf(Action::class, $value);
        $this->assertEquals(Action::DELETE, $value->getValue());
    }

    /**
     * @test
     */
    public function convertToPHPValueWithNullReturnsNull()
    {
        PhpEnumType::registerEnumType(Action::class);
        $type = Type::getType(Action::class);

        $value = $type->convertToPHPValue(null, $this->platform->reveal());
        $this->assertNull($value);
    }

    /**
     * @test
     */
    public function convertToPHPValueWithInvalidValueThrowsException()
    {
        PhpEnumType::registerEnumType(Action::class);
        $type = Type::getType(Action::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The value "invalid" is not valid for the enum "%s". Expected one of ["%s"]',
            Action::class,
            implode('", "', Action::toArray())
        ));
        $type->convertToPHPValue('invalid', $this->platform->reveal());
    }

    /**
     * @test
     */
    public function usingChildEnumTypeRegisteredValueIsCorrect()
    {
        MyType::registerEnumType(Action::class);
        $type = Type::getType(Action::class);

        $this->assertInstanceOf(MyType::class, $type);
        $this->assertEquals('FOO BAR', $type->getSQLDeclaration([], $this->platform->reveal()));
    }
}
