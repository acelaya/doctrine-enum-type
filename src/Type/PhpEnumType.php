<?php
namespace Acelaya\Doctrine\Type;

use Acelaya\Doctrine\Exception\InvalidArgumentException;
use Doctrine\DBAL\DBALException;
use MyCLabs\Enum\Enum;

/**
 * This class instantiates all types as soon as registered, and will be removed in v3.
 * Use the EnumTypeRegistrator service instead
 *
 * @deprecated
 */
class PhpEnumType extends AbstractPhpEnumType
{
    /**
     * @param $typeNameOrEnumClass
     * @param null $enumClass
     * @throws InvalidArgumentException
     * @throws DBALException
     */
    public static function registerEnumType($typeNameOrEnumClass, $enumClass = null)
    {
        $typeName = $typeNameOrEnumClass;
        $enumClass = $enumClass ?: $typeNameOrEnumClass;

        if (! is_subclass_of($enumClass, Enum::class)) {
            throw new InvalidArgumentException(sprintf(
                'Provided enum class "%s" is not valid. Enums must extend "%s"',
                $enumClass,
                Enum::class
            ));
        }

        // Register and customize the type
        self::addType($typeName, static::class);
        /** @var PhpEnumType $type */
        $type = self::getType($typeName);
        $type->name = $typeName;
        $type->enumClass = $enumClass;
    }

    /**
     * @param array $types
     * @throws InvalidArgumentException
     * @throws DBALException
     */
    public static function registerEnumTypes(array $types)
    {
        foreach ($types as $typeName => $enumClass) {
            $typeName = is_string($typeName) ? $typeName : $enumClass;
            static::registerEnumType($typeName, $enumClass);
        }
    }
}
