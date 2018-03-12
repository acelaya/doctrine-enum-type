<?php
declare(strict_types=1);

namespace Acelaya\Test\Doctrine\Enum;

use MyCLabs\Enum\Enum;

/**
 * Class WithCastingMethods
 * @author
 * @link
 *
 * @method static WithCastingMethods FOO()
 * @method static WithCastingMethods BAR()
 */
class WithCastingMethods extends Enum
{
    public const FOO = 'foo_value';
    public const BAR = 'bar_value';

    public static function castFromDatabase($value)
    {
        return \strtolower(str_replace(' ', '_', $value));
    }

    public static function castToDatabase($value)
    {
        return \strtoupper(str_replace('_', ' ', (string) $value));
    }
}
