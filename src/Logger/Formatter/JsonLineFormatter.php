<?php

declare(strict_types=1);

namespace Renttek\LogFormatters\Logger\Formatter;

use JsonException;
use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;
use Renttek\LogFormatters\Exception\FormattingException;

class JsonLineFormatter implements FormatterInterface
{
    public function format(LogRecord $record): string
    {
        try {
            $json = json_encode($record->context, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (JsonException $jsonException) {
            throw FormattingException::unableToEncodeJson($jsonException);
        }

        return $json . "\n";
    }

    public function formatBatch(array $records): string
    {
        $formattedRecords = array_map($this->format(...), $records);

        return implode('', $formattedRecords);
    }
}
