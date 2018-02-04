<?php
declare(strict_types=1);

namespace Acelaya\Test\Doctrine\Type;

use Acelaya\Doctrine\Type\PhpEnumType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class MyType extends PhpEnumType
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'FOO BAR';
    }
}
