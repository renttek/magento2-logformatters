<?php

declare(strict_types=1);

namespace Renttek\LogFormatters\Logger\Handler;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Monolog\Formatter\FormatterInterface;
use Renttek\LogFormatters\Logger\Formatter\JsonLineFormatter;

class JsonLineFileHandler extends Base
{
    public function __construct(
        DriverInterface $filesystem,
        string $fileName,
        ?string $filePath = null,
        ?FormatterInterface $formatter = null,
    ) {
        parent::__construct($filesystem, $filePath, $fileName);
        $this->setFormatter($formatter ?? new JsonLineFormatter());
    }
}
