<?php

declare(strict_types=1);

namespace Renttek\LogFormatters\Logger\Formatter;

use JsonException;
use DateTimeInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;
use Renttek\LogFormatters\Exception\FormattingException;
use Renttek\LogFormatters\Exception\InvalidCsvConfigurationException;
use Throwable;

/**
 * @phpstan-type CsvInputValue scalar|DateTimeInterface|array<array-key, mixed>|object|null
 */
class CsvFormatter implements FormatterInterface
{
    private readonly string $separator;
    private readonly string $enclosure;
    private readonly string $escape;

    public function __construct(
        string $separator = ',',
        string $enclosure = '"',
        string $escape = '\\',
    ) {
        $this->separator = $this->normalizeControlCharacter($separator, 'separator');
        $this->enclosure = $this->normalizeControlCharacter($enclosure, 'enclosure');
        $this->escape = $this->normalizeControlCharacter($escape, 'escape');
    }

    public function format(LogRecord $record): string
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            throw FormattingException::unableToOpenTemporaryStream('CSV');
        }

        try {
            $result = fputcsv(
                $stream,
                $this->normalizeValues($record->context),
                $this->separator,
                $this->enclosure,
                $this->escape,
            );
        } catch (Throwable $exception) {
            fclose($stream);
            throw FormattingException::unableToFormatAsCsv($exception);
        }

        if ($result === false) {
            fclose($stream);
            throw FormattingException::unableToFormatAsCsv();
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        if ($csv === false) {
            throw FormattingException::unableToReadFormattedCsv();
        }

        return $csv;
    }

    public function formatBatch(array $records): string
    {
        $formattedRecords = array_map($this->format(...), $records);

        return implode('', $formattedRecords);
    }

    /**
     * @phpstan-param array<array-key, CsvInputValue> $values
     *
     * @phpstan-return list<string>
     */
    private function normalizeValues(array $values): array
    {
        $normalizedValues = [];

        foreach ($values as $value) {
            $normalizedValues[] = $this->normalizeValue($value);
        }

        return $normalizedValues;
    }

    private function normalizeValue(mixed $value): string
    {
        if (is_scalar($value) || $value === null) {
            return (string) $value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        try {
            $json = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (JsonException $jsonException) {
            throw FormattingException::unableToNormalizeValue('CSV', $jsonException);
        }

        return $json;
    }

    private function normalizeControlCharacter(string $value, string $name): string
    {
        if ($value === '') {
            throw InvalidCsvConfigurationException::emptyControlCharacter($name);
        }

        return $value[0];
    }
}
