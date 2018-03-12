# Doctrine Enum Type

[![Build Status](https://img.shields.io/travis/acelaya/doctrine-enum-type/master.svg?style=flat-square)](https://travis-ci.org/acelaya/doctrine-enum-type)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/acelaya/doctrine-enum-type.svg?style=flat-square)](https://scrutinizer-ci.com/g/acelaya/doctrine-enum-type/?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/acelaya/doctrine-enum-type.svg?style=flat-square)](https://scrutinizer-ci.com/g/acelaya/doctrine-enum-type/?branch=master)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat-square)](https://github.com/phpstan/phpstan)
[![Latest Stable Version](https://poser.pugx.org/acelaya/doctrine-enum-type/v/stable?format=flat-square)](https://github.com/acelaya/doctrine-enum-type/releases/latest)
[![Total Downloads](https://poser.pugx.org/acelaya/doctrine-enum-type/downloads?format=flat-square)](https://packagist.org/packages/acelaya/doctrine-enum-type)
[![License](https://poser.pugx.org/acelaya/doctrine-enum-type/license?format=flat-square)](https://github.com/acelaya/doctrine-enum-type/blob/master/LICENSE)

This package provides a base implementation to define doctrine entity column types that are mapped to `MyCLabs\Enum\Enum` objects. That class is defined in the fantastic [myclabs/php-enum](https://github.com/myclabs/php-enum) package.

### Installation

The recommended installation method is by using [composer](https://getcomposer.org/)

    composer require acelaya/doctrine-enum-type.

### Usage

This package provides a `Acelaya\Doctrine\Type\PhpEnumType` class that extends `Doctrine\DBAL\Types\Type`. You can use it to easily map type names to concrete Enums.

The `PhpEnumType` class will be used as the doctrine type for every property that is an enumeration.

Let's imagine we have this two enums.

```php
<?php
declare(strict_types=1);

namespace Acelaya\Enum;

use MyCLabs\Enum\Enum;

class Action extends Enum
{
    public const CREATE = 'create';
    public const READ = 'read';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
}
```

```php
<?php
declare(strict_types=1);

namespace Acelaya\Enum;

use MyCLabs\Enum\Enum;

class Gender extends Enum
{
    public const MALE = 'male';
    public const FEMALE = 'female';
}
```

And this entity, with a column of each entity type.

```php
<?php
declare(strict_types=1);

namespace Acelaya\Entity;

use Acelaya\Enum\Action;
use Acelaya\Enum\Gender;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="users")
 */
class User
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var string
     *
     * @ORM\Column()
     */
    protected $name;
    /**
     * @var Action
     *
     * @ORM\Column(type=Action::class)
     */
    protected $action;
    /**
     * @var Gender
     *
     * @ORM\Column(type="php_enum_gender")
     */
    protected $gender;

    // Getters and setters...
}
```

The column type of the action property is the FQCN of the `Action` enum, and the gender column type is **php_enum_gender**. To get this working, you have to register the concrete column types, using the `Acelaya\Doctrine\Type\PhpEnumType::registerEnumType` static method.

```php
<?php
declare(strict_types=1);

// in bootstrapping code

// ...

use Acelaya\Doctrine\Type\PhpEnumType;
use Acelaya\Enum\Action;
use Acelaya\Enum\Gender;

// ...

// Register my types
PhpEnumType::registerEnumType(Action::class);
PhpEnumType::registerEnumType('php_enum_gender', Gender::class);
```

That will internally register a customized doctrine type. As you can see, it its possible to just pass the FQCN of the enum, making the type use it as the name, but you can also provide a different name.

Alternatively you can use the `Acelaya\Doctrine\Type\PhpEnumType::registerEnumTypes`, which expects an array of enums to register.

```php
<?php
declare(strict_types=1);

// ...

use Acelaya\Doctrine\Type\PhpEnumType;
use Acelaya\Enum\Action;
use Acelaya\Enum\Gender;

PhpEnumType::registerEnumTypes([
    Action::class,
    'php_enum_gender' => Gender::class,
]);
```

With this method, elements with a string key will be registered with that name, and elements with integer key will use the value as the type name.

Do the same for each concrete enum you want to register.

If you need more information on custom doctrine column types, read this http://doctrine-orm.readthedocs.io/en/latest/cookbook/custom-mapping-types.html

### Customize SQL declaration

By default, the `Acelaya\Doctrine\Type\PhpEnumType` class defines all enums as as a VARCHAR(255) like this:

```php
public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
{
    return $platform->getVarcharTypeDeclarationSQL([]);
}
```

If you want something more specific, like a MySQL enum, just extend `PhpEnumType` and overwrite the `getSQLDeclaration()` method with something like this.

```php
<?php
declare(strict_types=1);

namespace App\Type;

use Acelaya\Doctrine\Type\PhpEnumType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class MyPhpEnumType extends PhpEnumType
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        $values = \call_user_func([$this->enumClass, 'toArray']);
        return \sprintf(
            'ENUM("%s") COMMENT "%s"',
            \implode('", "', $values),
            $this->getName()
        );
    }
}
```

Then remember to register the enums with your own class.

```php
<?php
declare(strict_types=1);

// ...

use Acelaya\Enum\Action;
use Acelaya\Enum\Gender;
use App\Type\MyPhpEnumType;

MyPhpEnumType::registerEnumTypes([
    Action::class,
    'php_enum_gender' => Gender::class,
]);
```

### Customize value casting

The library assumes all the values of the enumeration are strings, but that might not always be the case.

Since v2.2.0, the library provides a way to define hooks which customize how values are cast **from** the database and **to** the database, by using the `castValueIn` and `castValueOut` static methods in the enumeration.

For example, let's imagine we have this enum:

```php
<?php
declare(strict_types=1);

namespace Acelaya\Enum;

use MyCLabs\Enum\Enum;

class Status extends Enum
{
    public const SUCCESS = 1;
    public const ERROR = 2;
}
```

Using the generic `PhpEnumType` would cause an error when casting the value from the database, since it will be deserialized as a string.

In order to get it working, you have to add the `castValueIn` static method in the enum:

```php
<?php
declare(strict_types=1);

namespace Acelaya\Enum;

use MyCLabs\Enum\Enum;

class Status extends Enum
{
    public const SUCCESS = 1;
    public const ERROR = 2;

    public static function castValueIn($value)
    {
        return (int) $value;
    }
}
```

This method is automatically invoked before checking if the value is valid, and creating the enum instance.

The same way, a `castValueOut` method can be defined in order to modify the value before getting it persisted into the database.

```php
<?php
declare(strict_types=1);

namespace Acelaya\Enum;

use MyCLabs\Enum\Enum;

class Gender extends Enum
{
    public const MALE = 'male';
    public const FEMALE = 'female';

    public static function castValueOut(self $value)
    {
        return \strtolower((string) $value);
    }
}
```

These methods can also be used to customize the values. For example, if you used to have values with underscores and want to replace them by dashes, you could define these methods in your enum, in order to keep backward compatibility.

```php
<?php

// ...
public static function castValueIn($value)
{
    return \str_replace('_', '-', $value);
}

public static function castValueOut(self $value)
{
    return \str_replace('-', '_', (string) $value);
}
```
