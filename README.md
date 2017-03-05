# Doctrine Enum Type

[![Build Status](https://travis-ci.org/acelaya/doctrine-enum-type.svg?branch=master)](https://travis-ci.org/acelaya/doctrine-enum-type)
[![Code Coverage](https://scrutinizer-ci.com/g/acelaya/doctrine-enum-type/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/acelaya/doctrine-enum-type/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/acelaya/doctrine-enum-type/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/acelaya/doctrine-enum-type/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/acelaya/doctrine-enum-type/v/stable.png)](https://packagist.org/packages/acelaya/doctrine-enum-type)
[![Total Downloads](https://poser.pugx.org/acelaya/doctrine-enum-type/downloads.png)](https://packagist.org/packages/acelaya/doctrine-enum-type)
[![License](https://poser.pugx.org/acelaya/doctrine-enum-type/license.png)](https://packagist.org/packages/acelaya/doctrine-enum-type)

This package provides a base abstract implementation to define doctrine entity column types that are mapped to `MyCLabs\Enum\Enum` objects. That class is defined in the fantastic [myclabs/php-enum](https://github.com/myclabs/php-enum) package.

### Installation

Install this package using [composer](https://getcomposer.org/) by running `composer require acelaya/doctrine-enum-type`.

### Usage

Since each type will be mapped to a different Enum class, you have to define your concrete implementation of that type by extending the `Acelaya\Doctrine\Type\AbstractPhpEnumType`.

Let's imagine we have this enum.

```php
<?php
namespace Acelaya\Enum;

use MyCLabs\Enum\Enum;

class Action extends Enum
{
    const CREATE    = 'create';
    const READ      = 'read';
    const UPDATE    = 'update';
    const DELETE    = 'delete';
}
```

And this entity, with a column of type `Acelaya\Enum\Action`.

```php
<?php
namespace Acelaya\Entity;

use Acelaya\Enum\Action;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="my_entities")
 */
class MyEntity
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
     * @ORM\Column(type="php_enum_action")
     */
    protected $action;

    // Getters and setters...
}
```

The column type of the action property is **php_enum_action**. To get this working, you have to define and register the concrete column type.

Start by creating the type class.

```php
<?php
namespace Acelaya\Type;

use Acelaya\Doctrine\Type\AbstractPhpEnumType;
use Acelaya\Enum\Action;

class ActionEnumType extends AbstractPhpEnumType
{
    /**
     * You have to define this so that type mapping is properly performed
     */
    protected $enumType = Action::class;

    /**
     * @return string
     */
    protected function getSpecificName()
    {
        return 'action';
    }
}
```

The type just need to have two things:

* The method `getSpecificName()`, which returns the specific part of the type. For example, if that method returns the string 'action', you will have to use the type name 'php_enum_action'. The type name is created by simply running `sprintf('php_enum_%s', $this->getSpecificName())`.
* The property `$enumType` with the fully qualified name of the enum class to map.

Finally, you just need to register your custom doctrine types:

```php
<?php
// in bootstrapping code

// ...

use Doctrine\DBAL\Types\Type;
use Acelaya\Type\ActionEnumType;
use Acelaya\Type\AnotherEnumType;
use Acelaya\Type\FooEnumType;

// ...

// Register my types
Type::addType('php_enum_action', ActionEnumType::class);
Type::addType('php_enum_another', AnotherEnumType::class);
Type::addType('php_enum_foo', FooEnumType::class);
```

Do the same for each concrete enum you want to register.

If you need more information on custom doctrine column types, read this http://doctrine-orm.readthedocs.org/en/latest/cookbook/custom-mapping-types.html

### Customize SQL declaration

All the doctrine types must define the SQL declaration of the column. By default, the `Acelaya\Doctrine\Type\AbstractPhpEnumType` class defines it as a VARCHAR(255) like this:

```php
public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
{
    return $platform->getVarcharTypeDeclarationSQL([]);
}
```

If you want something more specific, like a MySQL enum, just overwrite the `getSQLDeclaration()` method with something like this.

```php
public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
{
    $values = call_user_func([$this->enumType, 'toArray']);
    return sprintf(
        'ENUM("%s") COMMENT "%s"',
        implode('", "', $values),
        $this->getName()
    );
}
```
