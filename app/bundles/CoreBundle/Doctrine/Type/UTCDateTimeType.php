<?php

namespace Mautic\CoreBundle\Doctrine\Type;

use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;
use Mautic\CoreBundle\Helper\DateTimeHelper;

/**
 * Class UTCDateTimeType.
 */
class UTCDateTimeType extends DateTimeType
{
    /**
     * @var \DateTimeZone
     */
    private static $utc;

    /**
     * @inheritdoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if (isset($column['version']) && $column['version'] === true) {
            return 'TIMESTAMP';
        }

        if (isset($column['length']) && $column['length'] > 0) {
            return 'DATETIME('.(int) $column['length'].')';
        }

        return 'DATETIME';
    }

    /**
     * @param mixed $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        if (!self::$utc) {
            self::$utc = new \DateTimeZone('UTC');
        }

        if (!is_object($value)) {
            $dateHelper = new DateTimeHelper($value);
            $value      = $dateHelper->getDateTime();
        } else {
            $value = clone $value;
        }

        $value->setTimezone(self::$utc);

        if ($value instanceof DateTimeInterface) {
            $dateTimeFormat = $platform->getDateTimeFormatString();

            return $value->format("{$dateTimeFormat}.u");
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    /**
     * @param mixed $value
     *
     * @return \DateTime|null
     *
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        if (!self::$utc) {
            self::$utc = new \DateTimeZone('UTC');
        }

        // Set to UTC before converting to DateTime
        $timezone = date_default_timezone_get();
        date_default_timezone_set('UTC');

        $value = parent::convertToPHPValue($value, $platform);

        // Set to local timezone
        date_default_timezone_set($timezone);
        $value->setTimezone(new \DateTimeZone($timezone));

        return $value;
    }
}
