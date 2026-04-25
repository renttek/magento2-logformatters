<?php

declare(strict_types=1);

namespace Renttek\LogFormatters\Logger\Formatter\Builder;

use Magento\Framework\ObjectManagerInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;
use Renttek\LogFormatters\Exception\InvalidDecoratorConfigurationException;
use Throwable;

/**
 * @phpstan-type DecoratorDefinition array{class: class-string, ...<string, mixed>}
 * @phpstan-type DecoratorList array<int, class-string|DecoratorDefinition>
 */
class DecoratorChainFormatter implements FormatterInterface
{
    private readonly FormatterInterface $formatter;

    /**
     * @phpstan-param DecoratorList $decorators
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        FormatterInterface $formatter,
        array $decorators = [],
    ) {
        $this->formatter = $this->buildFormatter($objectManager, $formatter, $decorators);
    }

    public function format(LogRecord $record): string
    {
        return $this->formatter->format($record);
    }

    public function formatBatch(array $records): string
    {
        return $this->formatter->formatBatch($records);
    }

    /**
     * @phpstan-param DecoratorList $decorators
     */
    private function buildFormatter(
        ObjectManagerInterface $objectManager,
        FormatterInterface $formatter,
        array $decorators,
    ): FormatterInterface {
        foreach (array_reverse($decorators) as $decoratorConfiguration) {
            $formatter = $this->createDecorator($objectManager, $formatter, $decoratorConfiguration);
        }

        return $formatter;
    }

    private function createDecorator(
        ObjectManagerInterface $objectManager,
        FormatterInterface $formatter,
        mixed $decoratorConfiguration,
    ): FormatterInterface {
        if (is_string($decoratorConfiguration)) {
            $decoratorClass = $decoratorConfiguration;
            $arguments = [];
        } elseif (is_array($decoratorConfiguration) && isset($decoratorConfiguration['class'])) {
            $decoratorClass = (string) $decoratorConfiguration['class'];
            $arguments = $decoratorConfiguration;
            unset($arguments['class']);
        } else {
            throw InvalidDecoratorConfigurationException::unsupportedConfiguration();
        }

        try {
            $decorator = $objectManager->create($decoratorClass, ['formatter' => $formatter] + $arguments);
        } catch (Throwable $exception) {
            throw InvalidDecoratorConfigurationException::unableToCreateDecorator($decoratorClass, $exception);
        }

        if (!$decorator instanceof FormatterInterface) {
            throw InvalidDecoratorConfigurationException::decoratorMustImplementFormatter($decoratorClass);
        }

        return $decorator;
    }
}
