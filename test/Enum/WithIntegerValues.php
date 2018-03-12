<?php
declare(strict_types=1);

namespace Acelaya\Test\Doctrine\Enum;

use MyCLabs\Enum\Enum;

/**
 * Class WithCastingMethods
 * @author
 * @link
 *
 * @method static WithIntegerValues FOO()
 * @method static WithIntegerValues BAR()
 */
class WithIntegerValues extends Enum
{
    public const ONE = 1;
    public const TWO = 2;

    public static function castFromDatabase($value)
    {
        return (int) $value;
    }
}
