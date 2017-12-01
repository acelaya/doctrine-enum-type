<?php
namespace Acelaya\Test\Doctrine\Type;

use Acelaya\Doctrine\Type\PhpEnumType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class MyCustomEnumType extends PhpEnumType
{
    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $values = call_user_func([$this->enumClass, 'toArray']);

        return sprintf(
            'ENUM("%s") COMMENT "%s"',
            implode('", "', $values),
            $this->getName()
        );
    }
}
