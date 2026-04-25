<?php

declare(strict_types=1);

namespace Renttek\LogFormatters\Exception;

use InvalidArgumentException;
use Throwable;

class InvalidCsvConfigurationException extends InvalidArgumentException
{
    public static function emptyControlCharacter(string $name, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('CSV %s must not be empty.', $name),
            0,
            $previous,
        );
    }
}
