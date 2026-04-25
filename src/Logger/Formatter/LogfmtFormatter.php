<?php

declare(strict_types=1);

namespace Renttek\LogFormatters\Logger\Formatter;

use JsonException;
use DateTimeInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;
use Renttek\LogFormatters\Exception\FormattingException;

class LogfmtFormatter implements FormatterInterface
{
    public function format(LogRecord $record): string
    {
        $parts = [];

        foreach ($record->context as $key => $value) {
            $parts[] = $this->normalizeKey((string) $key) . '=' . $this->formatValue($value);
        }

        return implode(' ', $parts) . "\n";
    }

    public function formatBatch(array $records): string
    {
        $formattedRecords = array_map($this->format(...), $records);

        return implode('', $formattedRecords);
    }

    private function normalizeKey(string $key): string
    {
        $normalizedKey = preg_replace('/[^A-Za-z0-9_.-]/', '_', $key);

        if ($normalizedKey === null || $normalizedKey === '') {
            throw FormattingException::unableToNormalizeLogfmtKey();
        }

        return $normalizedKey;
    }

    private function formatValue(mixed $value): string
    {
        $normalizedValue = $this->normalizeValue($value);

        if ($normalizedValue === '') {
            return '""';
        }

        if (preg_match('/^[^\s="]+$/', $normalizedValue) === 1) {
            return $normalizedValue;
        }

        return '"' . addcslashes($normalizedValue, "\\\"") . '"';
    }

    private function normalizeValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value) || $value === null) {
            return (string) $value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        try {
            $json = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (JsonException $jsonException) {
            throw FormattingException::unableToNormalizeValue('logfmt', $jsonException);
        }

        return $json;
    }
}
