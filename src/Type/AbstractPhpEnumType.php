<?php
namespace Acelaya\Doctrine\Type;

use Acelaya\Doctrine\Exception\InvalidArgumentException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use MyCLabs\Enum\Enum;

/**
 * Class AbstractPhpEnumType
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
abstract class AbstractPhpEnumType extends Type
{
    const NAME_PATTERN = 'php_enum_%s';

    protected $enumType = Enum::class;

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        return sprintf(self::NAME_PATTERN, $this->getSpecificName());
    }

    /**
     * @return string
     */
    abstract protected function getSpecificName();

    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array $fieldDeclaration The field declaration.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getVarcharTypeDeclarationSQL([]);
    }

    /**
     * @param string $value
     * @param AbstractPlatform $platform
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        $isValid = call_user_func([$this->enumType, 'isValid'], $value);
        if (! $isValid) {
            throw new InvalidArgumentException(sprintf(
                'The value "%s" is not valid for the enum "%s". Expected one of ["%s"]',
                $value,
                $this->enumType,
                implode('", "', call_user_func([$this->enumType, 'toArray']))
            ));
        }

        return new $this->enumType($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return (string) $value;
    }
}
