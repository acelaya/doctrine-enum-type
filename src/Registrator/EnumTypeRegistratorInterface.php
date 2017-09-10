<?php
namespace Acelaya\Doctrine\Registrator;

use Acelaya\Doctrine\Exception\InvalidArgumentException;
use Doctrine\DBAL\DBALException;

interface EnumTypeRegistratorInterface
{
    /**
     * @param string $typeNameOrEnumClass
     * @param string|null $enumClass
     * @throws InvalidArgumentException
     * @throws DBALException
     */
    public function registerEnumType($typeNameOrEnumClass, $enumClass = null);

    /**
     * @param string[] $types
     * @throws InvalidArgumentException
     * @throws DBALException
     */
    public function registerEnumTypes(array $types);
}
