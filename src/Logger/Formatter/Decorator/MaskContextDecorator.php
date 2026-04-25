<?php

declare(strict_types=1);

namespace Renttek\LogFormatters\Logger\Formatter\Decorator;

use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;

/**
 * @phpstan-type ContextArray array<array-key, scalar|array<array-key, mixed>|object|null>
 * @phpstan-type MaskField list<int|string>
 */
class MaskContextDecorator implements FormatterInterface
{
    /**
     * @phpstan-param MaskField $fieldsToMask
     */
    public function __construct(
        private readonly FormatterInterface $formatter,
        private readonly array $fieldsToMask = [],
        private readonly string $maskValue = '***',
    ) {}

    public function format(LogRecord $record): string
    {
        return $this->formatter->format(
            $record->with(context: $this->maskFields($record->context)),
        );
    }

    public function formatBatch(array $records): string
    {
        $maskedRecords = array_map(
            fn(LogRecord $record): LogRecord => $record->with(context: $this->maskFields($record->context)),
            $records,
        );

        return $this->formatter->formatBatch($maskedRecords);
    }

    /**
     * @phpstan-param ContextArray $values
     *
     * @phpstan-return ContextArray
     */
    private function maskFields(array $values): array
    {
        $maskedValues = [];

        foreach ($values as $key => $value) {
            if (in_array($key, $this->fieldsToMask, true)) {
                $maskedValues[$key] = $this->maskValue;
                continue;
            }

            if (is_array($value)) {
                $maskedValues[$key] = $this->maskFields($value);
                continue;
            }

            $maskedValues[$key] = $value;
        }

        return $maskedValues;
    }
}
