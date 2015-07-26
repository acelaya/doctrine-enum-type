<?php
namespace Acelaya\Test\Doctrine\Type;

use Acelaya\Test\Doctrine\Enum\Action;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use PHPUnit_Framework_TestCase as TestCase;

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
     * @var AbstractPlatform
     */
    protected $platform;

    public static function setUpBeforeClass()
    {
        Type::addType(self::EXPECTED_NAME, 'Acelaya\Test\Doctrine\Type\ActionEnumType');
    }

    public function setUp()
    {
        $this->type = Type::getType(self::EXPECTED_NAME);
        $this->platform = $this->getMock('Doctrine\DBAL\Platforms\AbstractPlatform');
    }

    public function testGetName()
    {
        $this->assertEquals(self::EXPECTED_NAME, $this->type->getName());
    }

    public function testGetSQLDeclaration()
    {
        $this->assertEquals(
            'VARCHAR(256) COMMENT "php_enum"',
            $this->type->getSQLDeclaration([], $this->platform)
        );
    }

    public function testConvertToDatabaseValue()
    {
        $value = Action::CREATE();
        $this->assertEquals(Action::CREATE, $this->type->convertToDatabaseValue($value, $this->platform));

        $value = Action::READ();
        $this->assertEquals(Action::READ, $this->type->convertToDatabaseValue($value, $this->platform));

        $value = Action::UPDATE();
        $this->assertEquals(Action::UPDATE, $this->type->convertToDatabaseValue($value, $this->platform));

        $value = Action::DELETE();
        $this->assertEquals(Action::DELETE, $this->type->convertToDatabaseValue($value, $this->platform));
    }

    public function testConvertToPHPValueWithValidValue()
    {
        /** @var Action $value */
        $value = $this->type->convertToPHPValue(Action::CREATE, $this->platform);
        $this->assertInstanceOf('Acelaya\Test\Doctrine\Enum\Action', $value);
        $this->assertEquals(Action::CREATE, $value->getValue());

        $value = $this->type->convertToPHPValue(Action::DELETE, $this->platform);
        $this->assertInstanceOf('Acelaya\Test\Doctrine\Enum\Action', $value);
        $this->assertEquals(Action::DELETE, $value->getValue());
    }

    /**
     * @expectedException \Acelaya\Doctrine\Exception\InvalidArgumentException
     */
    public function testConvertToPHPValueWithInvalidValue()
    {
        $this->type->convertToPHPValue('invalid', $this->platform);
    }
}
