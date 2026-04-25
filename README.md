# Renttek Log Formatters

`Renttek_LogFormatters` is a small Magento 2 utility module that provides reusable Monolog formatters, decorators, and file handlers for structured logging.

All formatters in this module format the Monolog record `context`. This keeps the log output predictable and makes it easy to log structured payloads from Magento code.

## Included classes

### Formatters

- `Renttek\LogFormatters\Logger\Formatter\JsonLineFormatter`
- `Renttek\LogFormatters\Logger\Formatter\CsvFormatter`
- `Renttek\LogFormatters\Logger\Formatter\LogfmtFormatter`

### Decorators

- `Renttek\LogFormatters\Logger\Formatter\Decorator\MaskContextDecorator`
- `Renttek\LogFormatters\Logger\Formatter\Decorator\DateTimeFormatterDecorator`

### Helper formatter for decorator chains

- `Renttek\LogFormatters\Logger\Formatter\Builder\DecoratorChainFormatter`

### File handlers

- `Renttek\LogFormatters\Logger\Handler\JsonLineFileHandler`
- `Renttek\LogFormatters\Logger\Handler\CsvFileHandler`
- `Renttek\LogFormatters\Logger\Handler\LogfmtFileHandler`

## JsonLineFormatter

Writes one JSON object per line.

### Example `di.xml`

```xml
<virtualType name="Vendor\Module\Logger\Handler\ApiLogHandler" type="Renttek\LogFormatters\Logger\Handler\JsonLineFileHandler">
    <arguments>
        <argument name="fileName" xsi:type="string">/var/log/api.log</argument>
    </arguments>
</virtualType>

<type name="Vendor\Module\Logger\ApiLogger">
    <arguments>
        <argument name="name" xsi:type="string">api</argument>
        <argument name="handlers" xsi:type="array">
            <item name="default" xsi:type="object">Vendor\Module\Logger\Handler\ApiLogHandler</item>
        </argument>
    </arguments>
</type>
```

### Example output

```text
{"request_id":"abc123","status":200,"path":"/rest/V1/orders"}
{"request_id":"def456","status":500,"path":"/rest/V1/orders","error":"Upstream timeout"}
```

## CsvFormatter

Writes the context values as a single CSV row. Arrays and nested structures are JSON-encoded into one cell. The `separator`, `enclosure`, and `escape` characters are configurable through the constructor and default to `,`, `"`, and `\`.

### Example `di.xml`

```xml
<virtualType name="Vendor\Module\Logger\Handler\ExportLogHandler" type="Renttek\LogFormatters\Logger\Handler\CsvFileHandler">
    <arguments>
        <argument name="fileName" xsi:type="string">/var/log/export.csv</argument>
        <argument name="separator" xsi:type="string">;</argument>
        <argument name="enclosure" xsi:type="string">"</argument>
        <argument name="escape" xsi:type="string">\</argument>
    </arguments>
</virtualType>

<type name="Vendor\Module\Logger\ExportLogger">
    <arguments>
        <argument name="name" xsi:type="string">export</argument>
        <argument name="handlers" xsi:type="array">
            <item name="default" xsi:type="object">Vendor\Module\Logger\Handler\ExportLogHandler</item>
        </argument>
    </arguments>
</type>
```

### Example output

```text
2026-04-25T13:45:00+00:00;orders;150;2
2026-04-25T13:50:00+00:00;customers;80;0
```

## LogfmtFormatter

Writes the context as `key=value` pairs in logfmt format. Values with spaces or special characters are quoted.

### Example `di.xml`

```xml
<virtualType name="Vendor\Module\Logger\Handler\SyncLogHandler" type="Renttek\LogFormatters\Logger\Handler\LogfmtFileHandler">
    <arguments>
        <argument name="fileName" xsi:type="string">/var/log/sync.logfmt</argument>
    </arguments>
</virtualType>

<type name="Vendor\Module\Logger\SyncLogger">
    <arguments>
        <argument name="name" xsi:type="string">sync</argument>
        <argument name="handlers" xsi:type="array">
            <item name="default" xsi:type="object">Vendor\Module\Logger\Handler\SyncLogHandler</item>
        </argument>
    </arguments>
</type>
```

### Example output

```text
job=inventory_sync status=ok duration_ms=231 store=default
job=inventory_sync status=failed duration_ms=912 error="Connection reset by peer"
```

## MaskContextDecorator

Decorates another formatter and replaces configured context fields with a fixed mask value before the wrapped formatter formats the record.

### Example `di.xml`

```xml
<virtualType name="Vendor\Module\Logger\Formatter\MaskedJsonFormatter"
             type="Renttek\LogFormatters\Logger\Formatter\Decorator\MaskContextDecorator">
    <arguments>
        <argument name="formatter" xsi:type="object">Renttek\LogFormatters\Logger\Formatter\JsonLineFormatter</argument>
        <argument name="fieldsToMask" xsi:type="array">
            <item name="authorization" xsi:type="string">authorization</item>
            <item name="token" xsi:type="string">token</item>
            <item name="password" xsi:type="string">password</item>
        </argument>
        <argument name="maskValue" xsi:type="string">[redacted]</argument>
    </arguments>
</virtualType>
```

### Example output

```text
{"endpoint":"/rest/V1/customers","authorization":"[redacted]","token":"[redacted]","customer_id":42}
{"endpoint":"/rest/V1/customers","password":"[redacted]","email":"customer@example.com"}
```

## DateTimeFormatterDecorator

Decorates another formatter and converts any `DateTimeInterface` values in the context to strings before the wrapped formatter formats the record. The output format is configurable and defaults to `DATE_ATOM`.

### Example `di.xml`

```xml
<virtualType name="Vendor\Module\Logger\Formatter\DateTimeJsonFormatter"
             type="Renttek\LogFormatters\Logger\Formatter\Decorator\DateTimeFormatterDecorator">
    <arguments>
        <argument name="formatter" xsi:type="object">Renttek\LogFormatters\Logger\Formatter\JsonLineFormatter</argument>
        <argument name="format" xsi:type="string">Y-m-d H:i:s</argument>
    </arguments>
</virtualType>
```

### Example output

```text
{"job":"catalog_sync","started_at":"2026-04-25 14:30:00","finished_at":"2026-04-25 14:31:15"}
```

## DecoratorChainFormatter

`DecoratorChainFormatter` helps keep `di.xml` flatter when you want to apply multiple decorators to one formatter. You provide one base formatter and a list of decorator configurations. Decorators are applied in the same order as they are listed in `di.xml`.

### Example `di.xml`

```xml
<virtualType name="Vendor\Module\Logger\Formatter\ApiFormatter"
             type="Renttek\LogFormatters\Logger\Formatter\Builder\DecoratorChainFormatter">
    <arguments>
        <argument name="formatter" xsi:type="object">Renttek\LogFormatters\Logger\Formatter\JsonLineFormatter</argument>
        <argument name="decorators" xsi:type="array">
            <!-- decorator can be a simple FQDN -->
            <item name="datetime" xsi:type="object">Renttek\LogFormatters\Logger\Formatter\Decorator\DateTimeFormatterDecorator</item>
            <!-- Or a configuration with a class and it's parameters (to save on virtual types) -->
            <item name="mask" xsi:type="array">
                <item name="class" xsi:type="string">Renttek\LogFormatters\Logger\Formatter\Decorator\MaskContextDecorator</item>
                <item name="fieldsToMask" xsi:type="array">
                    <item name="token" xsi:type="string">token</item>
                    <item name="authorization" xsi:type="string">authorization</item>
                </item>
                <item name="maskValue" xsi:type="string">[redacted]</item>
            </item>
        </argument>
    </arguments>
</virtualType>

<virtualType name="Vendor\Module\Logger\Handler\ApiLogHandler"
             type="Renttek\LogFormatters\Logger\Handler\JsonLineFileHandler">
    <arguments>
        <argument name="fileName" xsi:type="string">/var/log/api.log</argument>
        <argument name="formatter" xsi:type="object">Vendor\Module\Logger\Formatter\ApiFormatter</argument>
    </arguments>
</virtualType>
```

### Example output

```text
{"endpoint":"/rest/V1/orders","requested_at":"2026-04-25T14:30:00+00:00","authorization":"[redacted]","token":"[redacted]"}
```

## Notes

- The file handlers write to Magento's `var/log` directory when you pass a path such as `/var/log/example.log` as `fileName`.
- The JSON, CSV, and logfmt handlers accept an optional `formatter` constructor argument, so decorated formatters can be injected through `di.xml`.
- `CsvFileHandler` also accepts `separator`, `enclosure`, and `escape` constructor arguments.
