# Doctrine Enum Type

[![Build Status](https://travis-ci.org/acelaya/doctrine-enum-type.svg?branch=master)](https://travis-ci.org/acelaya/doctrine-enum-type)
[![Code Coverage](https://scrutinizer-ci.com/g/acelaya/doctrine-enum-type/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/acelaya/doctrine-enum-type/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/acelaya/doctrine-enum-type/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/acelaya/doctrine-enum-type/?branch=master)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)
[![Latest Stable Version](https://poser.pugx.org/acelaya/doctrine-enum-type/v/stable.png)](https://packagist.org/packages/acelaya/doctrine-enum-type)
[![Total Downloads](https://poser.pugx.org/acelaya/doctrine-enum-type/downloads.png)](https://packagist.org/packages/acelaya/doctrine-enum-type)
[![License](https://poser.pugx.org/acelaya/doctrine-enum-type/license.png)](https://packagist.org/packages/acelaya/doctrine-enum-type)

This package provides a base implementation to define doctrine entity column types that are mapped to `MyCLabs\Enum\Enum` objects. That class is defined in the fantastic [myclabs/php-enum](https://github.com/myclabs/php-enum) package.

### Installation

Install this package using [composer](https://getcomposer.org/) by running `composer require acelaya/doctrine-enum-type`.

### Usage

This package provides two ways to register types that are mapped to enums. One of them is more verbose but more efficient. The other one is faster to implement but instantiates all types on every request.

Let's imagine we have this two enums.

```php
<?php
namespace Acelaya\Enum;

use MyCLabs\Enum\Enum;

class Action extends Enum
{
    const CREATE = 'create';
    const READ = 'read';
    const UPDATE = 'update';
    const DELETE = 'delete';
}
```

```php
<?php
namespace Acelaya\Enum;

use MyCLabs\Enum\Enum;

class Gender extends Enum
{
    const MALE = 'male';
    const FEMALE = 'female';
    const OTHER = 'other';
}
```

And this entity, with a column of each entity type.

```php
<?php
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

The column type of the action property is the FQCN of the `Action` enum, and the gender column type is **php_enum_gender**. To get this working, you have two options.

#### Register enums using PhpEnumType

Register the concrete column types, using the `Acelaya\Doctrine\Type\PhpEnumType::registerEnumType` static method.

```php
<?php
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

That will internally register a customized doctrine type. As you can see, it's possible to just pass the FQCN of the enum, making the type use it as the name, but you can also provide a different name.

Alternatively you can use the `Acelaya\Doctrine\Type\PhpEnumType::registerEnumTypes`, which expects an array of enums to register.

```php
<?php
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

#### Register enums using the EnumTypeRegistrator

The previous approach is very easy, but has one caveat. Types cannot be lazy loaded, as happens with other doctrine types. They all need to be instantiated on every request.

When you don't have many enum types, that's acceptable, but it could be problematic if your application grows and you start using them in many entities.

That's why this package provides a service, the `Acelaya\Doctrine\Registrator\EnumTypeRegistrator`, which is responsible of creating and dumping type classes, and registering each enum type using those classes. This way, there's a different type for every enum, and they don't need to be instantiated to do the magic.

```php
<?php
// in bootstrapping code

// ...

use Acelaya\Doctrine\Registrator\EnumTypeRegistrator;
use Acelaya\Enum\Action;
use Acelaya\Enum\Gender;

// ...

$registrator = new EnumTypeRegistrator([
    'type_files_dir' => 'data/my_types', // Defaults to sys_get_temp_dir()
]);
// Similar to the PhpEnumType, this service has the methods registerEnumType and registerEnumTypes
$registrator->registerEnumTypes([
    Action::class,
    'php_enum_gender' => Gender::class,
]);
```

This will create two type classes in `data/my_types` dir, register an autoloader, and let doctrine lazy load them when the type is needed.

However, this has another performance issue. We are dumping one file per enum on every request.

In order to prevent that, you can disable the file generation in the `EnumTypeRegistrator` by passing the **auto_generate_type_files** option with value `false`.

```php
<?php
use Acelaya\Doctrine\Registrator\EnumTypeRegistrator;

$registrator = new EnumTypeRegistrator([
    'type_files_dir' => 'data/my_types', // Defaults to sys_get_temp_dir()
    'auto_generate_type_files' => false, // Defaults to true
]);
```

And then, use the included console tool to generate all types just once, and reduce the overload at runtime.

```bash
vendor/bin/det det:dump-type-files
```

This command will require you to create a `det-config.php` file, which provides the list of enums to map, as well as the `EnumTypeRegistrator` configuration.

If you have worked with the doctrine console tool, this probably feels familiar. Indeed I got inspiration from doctrine's implementation to build this one.

### Customize SQL declaration

By default, all types define enums as a VARCHAR(255) like this:

```php
public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
{
    return $platform->getVarcharTypeDeclarationSQL([]);
}
```

If you want something more specific, like a MySQL enum, you have two options, depending on which one of the two previous approach you decided to use.

#### While using PhpEnumType

Just extend `PhpEnumType` and overwrite the `getSQLDeclaration()` method with something like this

```php
namespace App\Type;

use Acelaya\Doctrine\Type\PhpEnumType;

class MyPhpEnumType extends PhpEnumType
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $values = call_user_func([$this->enumType, 'toArray']);
        return sprintf(
            'ENUM("%s") COMMENT "%s"',
            implode('", "', $values),
            $this->getName()
        );
    }
}
```

Then remember to register the enums with your own type class.

```php
<?php
// ...

use Acelaya\Enum\Action;
use Acelaya\Enum\Gender;
use App\Type\MyPhpEnumType;

MyPhpEnumType::registerEnumTypes([
    Action::class,
    'php_enum_gender' => Gender::class,
]);
```

#### While using the EnumTypeRegistrator

Define your type that extends `AbstractPhpEnumType` and overwrite the `getSQLDeclaration()`.

```php
namespace App\Type;

use Acelaya\Doctrine\Type\AbstractPhpEnumType;

class MyPhpEnumType extends AbstractPhpEnumType
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $values = call_user_func([$this->enumType, 'toArray']);
        return sprintf(
            'ENUM("%s") COMMENT "%s"',
            implode('", "', $values),
            $this->getName()
        );
    }
}
```

Then configure the `EnumTypeRegistrator` to use your own type.

```php
<?php
use Acelaya\Doctrine\Registrator\EnumTypeRegistrator;
use App\Type\MyPhpEnumType;

$registrator = new EnumTypeRegistrator([
    'base_type_class' => MyPhpEnumType::class, // This defaults to AbstractPhpEnumType
    'type_files_dir' => 'data/my_types', // Defaults to sys_get_temp_dir()
]);
```

And that's it.

If you need more information on custom doctrine column types, read this http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/custom-mapping-types.html
