<?php
namespace Acelaya\Doctrine\Type;

use Acelaya\Doctrine\Exception\InvalidArgumentException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use MyCLabs\Enum\Enum;

abstract class AbstractPhpEnumType extends Type
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $enumClass = Enum::class;

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name ?: $this->enumClass;
    }

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

        $isValid = call_user_func([$this->enumClass, 'isValid'], $value);
        if (! $isValid) {
            throw new InvalidArgumentException(sprintf(
                'The value "%s" is not valid for the enum "%s". Expected one of ["%s"]',
                $value,
                $this->enumClass,
                implode('", "', call_user_func([$this->enumClass, 'toArray']))
            ));
        }

        return new $this->enumClass($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value === null ? null : (string) $value;
    }
}
