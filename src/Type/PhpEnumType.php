<?php

declare(strict_types=1);

namespace Acelaya\Doctrine\Type;

use Acelaya\Doctrine\Exception\InvalidArgumentException;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use MyCLabs\Enum\Enum;

use function implode;
use function is_string;
use function is_subclass_of;
use function method_exists;
use function sprintf;

class PhpEnumType extends Type
{
    /** @var string */
    private $name;
    /** @var string */
    protected $enumClass = Enum::class;

    public function getName(): string
    {
        return $this->name ?: 'enum';
    }

    /**
     * @param mixed[] $fieldDeclaration
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * @param string|null $value
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        // If the enumeration provides a casting method, apply it
        if (method_exists($this->enumClass, 'castValueIn')) {
            /** @var callable $castValueIn */
            $castValueIn = [$this->enumClass, 'castValueIn'];
            $value = $castValueIn($value);
        }

        // Check if the value is valid for this enumeration
        /** @var callable $isValidCallable */
        $isValidCallable = [$this->enumClass, 'isValid'];
        $isValid = $isValidCallable($value);
        if (! $isValid) {
            /** @var callable $toArray */
            $toArray = [$this->enumClass, 'toArray'];
            throw new InvalidArgumentException(sprintf(
                'The value "%s" is not valid for the enum "%s". Expected one of ["%s"]',
                $value,
                $this->enumClass,
                implode('", "', $toArray())
            ));
        }

        return new $this->enumClass($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        // If the enumeration provides a casting method, apply it
        if (method_exists($this->enumClass, 'castValueOut')) {
            /** @var callable $castValueOut */
            $castValueOut = [$this->enumClass, 'castValueOut'];
            return $castValueOut($value);
        }

        // Otherwise, cast to string
        return (string) $value;
    }

    /**
     * @throws InvalidArgumentException
     * @throws DBALException
     */
    public static function registerEnumType(string $typeNameOrEnumClass, ?string $enumClass = null): void
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
     * @param array<string|int, string> $types
     * @throws InvalidArgumentException
     * @throws DBALException
     */
    public static function registerEnumTypes(array $types): void
    {
        foreach ($types as $typeName => $enumClass) {
            $typeName = is_string($typeName) ? $typeName : $enumClass;
            static::registerEnumType($typeName, $enumClass);
        }
    }

    /**
     * @param AbstractPlatform $platform
     * @return boolean
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
