<?php
declare(strict_types=1);

namespace Acelaya\Test\Doctrine\Type;

use Acelaya\Doctrine\Exception\InvalidArgumentException;
use Acelaya\Doctrine\Type\PhpEnumType;
use Acelaya\Test\Doctrine\Enum\Action;
use Acelaya\Test\Doctrine\Enum\Gender;
use Acelaya\Test\Doctrine\Enum\WithCastingMethods;
use Acelaya\Test\Doctrine\Enum\WithIntegerValues;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use MyCLabs\Enum\Enum;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionProperty;
use stdClass;
use function implode;
use function sprintf;

class PhpEnumTypeTest extends TestCase
{
    /** @var ObjectProphecy */
    protected $platform;

    public function setUp(): void
    {
        $this->platform = $this->prophesize(AbstractPlatform::class);

        // Before every test, clean registered types
        $refProp = new ReflectionProperty(Type::class, '_typeObjects');
        $refProp->setAccessible(true);
        $refProp->setValue(null, []);
        $refProp = new ReflectionProperty(Type::class, '_typesMap');
        $refProp->setAccessible(true);
        $refProp->setValue(null, []);
    }

    /**
     * @test
     */
    public function enumTypesAreProperlyRegistered(): void
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
    public function enumTypesAreProperlyCustomizedWhenRegistered(): void
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
    public function registerInvalidEnumThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Provided enum class "%s" is not valid. Enums must extend "%s"',
            stdClass::class,
            Enum::class
        ));
        PhpEnumType::registerEnumType(stdClass::class);
    }

    /**
     * @test
     */
    public function getSQLDeclarationReturnsValueFromPlatform(): void
    {
        $this->platform->getVarcharTypeDeclarationSQL(Argument::cetera())->willReturn('declaration');

        $type = $this->getType(Gender::class);

        $this->assertEquals('declaration', $type->getSQLDeclaration([], $this->platform->reveal()));
    }

    /**
     * @test
     * @dataProvider provideValues
     * @param string $typeName
     * @param $phpValue
     * @param string $expectedValue
     */
    public function convertToDatabaseValueParsesEnum(string $typeName, $phpValue, string $expectedValue): void
    {
        $type = $this->getType($typeName);

        $actualValue = $type->convertToDatabaseValue($phpValue, $this->platform->reveal());

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function provideValues(): array
    {
        return [
            [Action::class, Action::CREATE(), Action::CREATE],
            [Action::class, Action::READ(), Action::READ],
            [Action::class, Action::UPDATE(), Action::UPDATE],
            [Action::class, Action::DELETE(), Action::DELETE],
            [Gender::class, Gender::FEMALE(), Gender::FEMALE],
            [Gender::class, Gender::MALE(), Gender::MALE],
        ];
    }

    /**
     * @test
     */
    public function convertToDatabaseValueReturnsNullWhenNullIsProvided(): void
    {
        $type = $this->getType(Action::class);

        $this->assertNull($type->convertToDatabaseValue(null, $this->platform->reveal()));
    }

    /**
     * @test
     */
    public function convertToPHPValueWithValidValueReturnsParsedData(): void
    {
        $type = $this->getType(Action::class);

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
    public function convertToPHPValueWithNullReturnsNull(): void
    {
        $type = $this->getType(Action::class);

        $value = $type->convertToPHPValue(null, $this->platform->reveal());
        $this->assertNull($value);
    }

    /**
     * @test
     */
    public function convertToPHPValueWithInvalidValueThrowsException(): void
    {
        $type = $this->getType(Action::class);

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
    public function convertToPHPValueWithCastingMethodProperlyCastsIt(): void
    {
        $type = $this->getType(WithCastingMethods::class);

        $value = $type->convertToPHPValue('foo VALUE', $this->platform->reveal());
        $this->assertInstanceOf(WithCastingMethods::class, $value);
        $this->assertEquals(WithCastingMethods::FOO, $value->getValue());

        $intType = $this->getType(WithIntegerValues::class);

        $value = $intType->convertToPHPValue('1', $this->platform->reveal());
        $this->assertInstanceOf(WithIntegerValues::class, $value);
        $this->assertEquals(1, $value->getValue());
    }

    /**
     * @test
     */
    public function convertToDatabaseValueWithCastingMethodProperlyCastsIt(): void
    {
        $type = $this->getType(WithCastingMethods::class);

        $value = $type->convertToDatabaseValue(WithCastingMethods::FOO(), $this->platform->reveal());
        $this->assertEquals('FOO VALUE', $value);
    }

    /**
     * @test
     */
    public function usingChildCustomEnumTypeRegisteredValueIsCorrect(): void
    {
        MyCustomEnumType::registerEnumType(Action::class);
        $type = MyCustomEnumType::getType(Action::class);

        $this->assertInstanceOf(MyCustomEnumType::class, $type);
        $this->assertEquals(
            'ENUM("create", "read", "update", "delete") COMMENT "Acelaya\Test\Doctrine\Enum\Action"',
            $type->getSQLDeclaration([], $this->platform->reveal())
        );
    }

    /**
     * @test
     */
    public function SQLCommentHintIsAlwaysRequired(): void
    {
        $type = $this->getType(Gender::class);

        $this->assertTrue($type->requiresSQLCommentHint($this->platform->reveal()));
    }

    private function getType(string $typeName): PhpEnumType
    {
        PhpEnumType::registerEnumType($typeName);
        return Type::getType($typeName);
    }
}
