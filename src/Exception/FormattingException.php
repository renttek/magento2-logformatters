<?php

declare(strict_types=1);

namespace Renttek\LogFormatters\Exception;

use RuntimeException;
use Throwable;

class FormattingException extends RuntimeException
{
    public static function unableToEncodeJson(?Throwable $previous = null): self
    {
        return new self('Unable to encode log entry as JSON.', 0, $previous);
    }

    public static function unableToOpenTemporaryStream(string $format, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('Unable to open temporary stream for %s formatting.', $format),
            0,
            $previous,
        );
    }

    public static function unableToFormatAsCsv(?Throwable $previous = null): self
    {
        return new self('Unable to format log entry as CSV.', 0, $previous);
    }

    public static function unableToReadFormattedCsv(?Throwable $previous = null): self
    {
        return new self('Unable to read formatted CSV log entry.', 0, $previous);
    }

    public static function unableToNormalizeValue(string $format, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('Unable to normalize %s log value.', $format),
            0,
            $previous,
        );
    }

    public static function unableToNormalizeLogfmtKey(?Throwable $previous = null): self
    {
        return new self('Unable to normalize logfmt key.', 0, $previous);
    }
}
