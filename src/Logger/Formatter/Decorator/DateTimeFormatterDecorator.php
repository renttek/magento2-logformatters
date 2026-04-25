<?php

declare(strict_types=1);

namespace Renttek\LogFormatters\Logger\Formatter\Decorator;

use DateTimeInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;

/**
 * @phpstan-type ContextArray array<array-key, scalar|DateTimeInterface|array<array-key, mixed>|object|null>
 */
class DateTimeFormatterDecorator implements FormatterInterface
{
    public function __construct(
        private readonly FormatterInterface $formatter,
        private readonly string $format = DATE_ATOM,
    ) {}

    public function format(LogRecord $record): string
    {
        return $this->formatter->format(
            $record->with(context: $this->formatValues($record->context)),
        );
    }

    public function formatBatch(array $records): string
    {
        $formattedRecords = array_map(
            fn(LogRecord $record): LogRecord => $record->with(context: $this->formatValues($record->context)),
            $records,
        );

        return $this->formatter->formatBatch($formattedRecords);
    }

    /**
     * @phpstan-param ContextArray $values
     *
     * @phpstan-return ContextArray
     */
    private function formatValues(array $values): array
    {
        return array_map($this->formatValue(...), $values);
    }

    private function formatValue(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format($this->format);
        }

        if (is_array($value)) {
            return $this->formatValues($value);
        }

        return $value;
    }
}
