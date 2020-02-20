<?php
declare(strict_types = 1);

namespace RaspberryDaysWithoutHotfix;

use RaspberryDaysWithoutHotfix\GPIO\GPIO;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

/**
 * Class EventLoop
 */
class EventLoop
{
    /** @var int */
    private const BLINK_TIMER_INTERVAL = 1;

    /** @var string */
    private const FUNCTION_GPIO_BLINK_INFINITELY = 'blinkTimer';

    /** @var string */
    private const FUNCTION_GPIO_HOTFIX_TIMER = 'incrementNumber';

    /** @var int */
    private const HOTFIX_TIMER_INTERVAL = self::SECONDS_PER_MINUTE * self::MINUTES_PER_HOUR * self::HOURS_PER_DAY;

    /** @var int */
    private const HOURS_PER_DAY = 24;

    /** @var int */
    private const MINUTES_PER_HOUR = 60;

    /** @var int */
    private const SECONDS_PER_MINUTE = 60; // seconds

    /** @var GPIO */
    private $gpio;

    /** @var TimerInterface */
    private $hotfixTimer;

    /** @var LoopInterface */
    private $loop;

    /**
     * EventLoop constructor.
     *
     * @param LoopInterface $loop
     * @param GPIO          $gpio
     */
    public function __construct(LoopInterface $loop, GPIO $gpio)
    {
        $this->loop = $loop;
        $this->gpio = $gpio;
        $this->addHotfixTimer();
    }

    /**
     * @return void
     */
    public function addHotfixTimer(): void
    {
        $this->hotfixTimer = $this->loop->addPeriodicTimer(
            self::HOTFIX_TIMER_INTERVAL,
            [$this->gpio, self::FUNCTION_GPIO_HOTFIX_TIMER]
        );
    }

    /**
     * @return TimerInterface
     */
    public function blinkInfinitely(): TimerInterface
    {
        return $this->loop->addPeriodicTimer(
            self::BLINK_TIMER_INTERVAL,
            [$this->gpio, self::FUNCTION_GPIO_BLINK_INFINITELY]
        );
    }

    /**
     * @param TimerInterface $timer
     *
     * @return void
     */
    public function cancelBlinkTimer(TimerInterface $timer): void
    {
        $this->loop->cancelTimer($timer);
    }

    /**
     * @return LoopInterface
     */
    public function getEventLoop(): LoopInterface
    {
        return $this->loop;
    }

    /**
     * @return void
     */
    public function resetHotfixTimer(): void
    {
        $this->cancelHotfixTimer();
        $this->addHotfixTimer();
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $this->loop->run();
    }

    /**
     * @return void
     */
    private function cancelHotfixTimer(): void
    {
        $this->loop->cancelTimer($this->hotfixTimer);
        $this->hotfixTimer = null;
    }
}
