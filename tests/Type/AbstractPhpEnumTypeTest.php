<?php
namespace Acelaya\Test\Doctrine\Type;

use Acelaya\Doctrine\Exception\InvalidArgumentException;
use Acelaya\Test\Doctrine\Enum\Action;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Class AbstractPhpEnumTypeTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class AbstractPhpEnumTypeTest extends TestCase
{
    const EXPECTED_NAME = 'php_enum_action';

    /**
     * @var ActionEnumType
     */
    protected $type;
    /**
     * @var ObjectProphecy
     */
    protected $platform;

    public static function setUpBeforeClass()
    {
        Type::addType(self::EXPECTED_NAME, ActionEnumType::class);
    }

    public function setUp()
    {
        $this->type = Type::getType(self::EXPECTED_NAME);
        $this->platform = $this->prophesize(AbstractPlatform::class);
    }

    /**
     * @test
     */
    public function getNameReturnsCorrectValue()
    {
        $this->assertEquals(self::EXPECTED_NAME, $this->type->getName());
    }

    /**
     * @test
     */
    public function getSQLDeclaration()
    {
        $this->platform->getVarcharTypeDeclarationSQL(Argument::cetera())->willReturn('declaration');

        $this->assertEquals(
            'declaration',
            $this->type->getSQLDeclaration([], $this->platform->reveal())
        );
    }

    /**
     * @test
     */
    public function convertToDatabaseValue()
    {
        $value = Action::CREATE();
        $this->assertEquals(Action::CREATE, $this->type->convertToDatabaseValue($value, $this->platform->reveal()));

        $value = Action::READ();
        $this->assertEquals(Action::READ, $this->type->convertToDatabaseValue($value, $this->platform->reveal()));

        $value = Action::UPDATE();
        $this->assertEquals(Action::UPDATE, $this->type->convertToDatabaseValue($value, $this->platform->reveal()));

        $value = Action::DELETE();
        $this->assertEquals(Action::DELETE, $this->type->convertToDatabaseValue($value, $this->platform->reveal()));
    }

    /**
     * @test
     */
    public function convertToPHPValueWithValidValue()
    {
        /** @var Action $value */
        $value = $this->type->convertToPHPValue(Action::CREATE, $this->platform->reveal());
        $this->assertInstanceOf(Action::class, $value);
        $this->assertEquals(Action::CREATE, $value->getValue());

        $value = $this->type->convertToPHPValue(Action::DELETE, $this->platform->reveal());
        $this->assertInstanceOf(Action::class, $value);
        $this->assertEquals(Action::DELETE, $value->getValue());
    }

    /**
     * @test
     */
    public function convertToPHPValueWithNull()
    {
        $value = $this->type->convertToPHPValue(null, $this->platform->reveal());
        $this->assertEquals(null, $value);
    }

    /**
     * @test
     */
    public function convertToPHPValueWithInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The value "invalid" is not valid for the enum "%s". Expected one of ["%s"]',
            Action::class,
            implode('", "', Action::toArray())
        ));
        $this->type->convertToPHPValue('invalid', $this->platform->reveal());
    }
}
