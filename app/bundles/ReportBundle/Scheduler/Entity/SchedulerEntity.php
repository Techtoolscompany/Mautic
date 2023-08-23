<?php

namespace Mautic\ReportBundle\Scheduler\Entity;

use Mautic\ReportBundle\Scheduler\Enum\SchedulerEnum;
use Mautic\ReportBundle\Scheduler\SchedulerInterface;

class SchedulerEntity implements SchedulerInterface
{
    /**
     * @var bool
     */
    private $isScheduled = false;

    /**
     * @var string|null
     */
    private $scheduleUnit;

    /**
     * @var string|null
     */
    private $scheduleDay;

    /**
     * @var string|null
     */
    private $scheduleMonthFrequency;

    /**
     * @var string|null
     */
    private $scheduleTimezone;

    /**
     * @var string|null
     */
    private $scheduleTime;

    public function __construct($isScheduled, $scheduleUnit, $scheduleDay, $scheduleMonthFrequency, $scheduleTimezone = 'UTC', $scheduleTime = '00:00')
    {
        $this->isScheduled            = $isScheduled;
        $this->scheduleUnit           = $scheduleUnit;
        $this->scheduleDay            = $scheduleDay;
        $this->scheduleMonthFrequency = $scheduleMonthFrequency;
        $this->scheduleTimezone       = $scheduleTimezone;
        $this->scheduleTime           = $scheduleTime;
    }

    /**
     * @return bool
     */
    public function isScheduled()
    {
        return $this->isScheduled;
    }

    /**
     * @return string|null
     */
    public function getScheduleUnit()
    {
        return $this->scheduleUnit;
    }

    /**
     * @return string|null
     */
    public function getScheduleDay()
    {
        return $this->scheduleDay;
    }

    /**
     * @return string|null
     */
    public function getScheduleMonthFrequency()
    {
        return $this->scheduleMonthFrequency;
    }

    public function isScheduledNow(): bool
    {
        return SchedulerEnum::UNIT_NOW === $this->getScheduleUnit();
    }

    public function isScheduledDaily()
    {
        return SchedulerEnum::UNIT_DAILY === $this->getScheduleUnit();
    }

    public function isScheduledWeekly()
    {
        return SchedulerEnum::UNIT_WEEKLY === $this->getScheduleUnit();
    }

    public function isScheduledMonthly()
    {
        return SchedulerEnum::UNIT_MONTHLY === $this->getScheduleUnit();
    }

    public function isScheduledWeekDays()
    {
        return SchedulerEnum::DAY_WEEK_DAYS === $this->getScheduleDay();
    }

    public function getScheduleTimezone()
    {
        return $this->scheduleTimezone;
    }

    public function getScheduleTime()
    {
        return $this->scheduleTime;
    }
}
