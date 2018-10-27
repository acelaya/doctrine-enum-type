<?php
declare(strict_types=1);

namespace Acelaya\Doctrine\Exception;

use InvalidArgumentException as SplInvalidArgumentException;

class InvalidArgumentException extends SplInvalidArgumentException implements ExceptionInterface
{
}
