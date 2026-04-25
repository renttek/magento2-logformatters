<?php

declare(strict_types=1);

namespace Renttek\LogFormatters\Exception;

use InvalidArgumentException;
use Monolog\Formatter\FormatterInterface;
use Throwable;

class InvalidDecoratorConfigurationException extends InvalidArgumentException
{
    public static function unsupportedConfiguration(?Throwable $previous = null): self
    {
        return new self(
            'Decorator configuration must be a class name string or an array containing a "class" key.',
            0,
            $previous,
        );
    }

    public static function unableToCreateDecorator(string $decoratorClass, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('Unable to create decorator "%s".', $decoratorClass),
            0,
            $previous,
        );
    }

    public static function decoratorMustImplementFormatter(string $decoratorClass, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('Decorator "%s" must implement %s.', $decoratorClass, FormatterInterface::class),
            0,
            $previous,
        );
    }
}
