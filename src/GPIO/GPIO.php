<?php
declare(strict_types = 1);

namespace RaspberryDaysWithoutHotfix\GPIO;

use PiPHP\GPIO\Pin\OutputPinInterface;
use PiPHP\GPIO\Pin\PinInterface;

/**
 * Class GPIO
 */
class GPIO
{
    /** @var int */
    private const ARRAY_INDEX_DECIMAL = 1;

    /** @var int */
    private const ARRAY_INDEX_HUNDRED = 2;

    /** @var int */
    private const ARRAY_INDEX_SINGLE = 0;

    /** @var int */
    private const ARRAY_INDEX_THOUSAND = 3;

    /** @var int */
    private const DEFAULT_BLINK_TIMES = 10;

    /** @var int */
    public const DEFAULT_NUMBER = 0;

    /** @var array<int, array<string>> */
    private const NUMBER_CONFIGURATION = [
        self::NUMBER_ZERO => [
            PinConfiguration::SEGMENT_A,
            PinConfiguration::SEGMENT_B,
            PinConfiguration::SEGMENT_C,
            PinConfiguration::SEGMENT_D,
            PinConfiguration::SEGMENT_E,
            PinConfiguration::SEGMENT_F,
        ],
        self::NUMBER_ONE => [
            PinConfiguration::SEGMENT_B,
            PinConfiguration::SEGMENT_C,
        ],
        self::NUMBER_TWO => [
            PinConfiguration::SEGMENT_A,
            PinConfiguration::SEGMENT_B,
            PinConfiguration::SEGMENT_D,
            PinConfiguration::SEGMENT_E,
            PinConfiguration::SEGMENT_G,
        ],
        self::NUMBER_THREE => [
            PinConfiguration::SEGMENT_A,
            PinConfiguration::SEGMENT_B,
            PinConfiguration::SEGMENT_C,
            PinConfiguration::SEGMENT_D,
            PinConfiguration::SEGMENT_G,
        ],
        self::NUMBER_FOUR => [
            PinConfiguration::SEGMENT_B,
            PinConfiguration::SEGMENT_C,
            PinConfiguration::SEGMENT_F,
            PinConfiguration::SEGMENT_G,
        ],
        self::NUMBER_FIVE => [
            PinConfiguration::SEGMENT_A,
            PinConfiguration::SEGMENT_C,
            PinConfiguration::SEGMENT_D,
            PinConfiguration::SEGMENT_F,
            PinConfiguration::SEGMENT_G,
        ],
        self::NUMBER_SIX => [
            PinConfiguration::SEGMENT_A,
            PinConfiguration::SEGMENT_C,
            PinConfiguration::SEGMENT_D,
            PinConfiguration::SEGMENT_E,
            PinConfiguration::SEGMENT_F,
            PinConfiguration::SEGMENT_G,
        ],
        self::NUMBER_SEVEN => [
            PinConfiguration::SEGMENT_A,
            PinConfiguration::SEGMENT_B,
            PinConfiguration::SEGMENT_C,
        ],
        self::NUMBER_EIGHT => [
            PinConfiguration::SEGMENT_A,
            PinConfiguration::SEGMENT_B,
            PinConfiguration::SEGMENT_C,
            PinConfiguration::SEGMENT_D,
            PinConfiguration::SEGMENT_E,
            PinConfiguration::SEGMENT_F,
            PinConfiguration::SEGMENT_G,
        ],
        self::NUMBER_NINE => [
            PinConfiguration::SEGMENT_A,
            PinConfiguration::SEGMENT_B,
            PinConfiguration::SEGMENT_C,
            PinConfiguration::SEGMENT_D,
            PinConfiguration::SEGMENT_F,
            PinConfiguration::SEGMENT_G,
        ],
    ];

    /** @var int */
    private const NUMBER_EIGHT = 8;

    /** @var int */
    private const NUMBER_FIVE = 5;

    /** @var int */
    private const NUMBER_FOUR = 4;

    /** @var int */
    private const NUMBER_NINE = 9;

    /** @var int */
    private const NUMBER_ONE = 1;

    /** @var int */
    private const NUMBER_SEVEN = 7;

    /** @var int */
    private const NUMBER_SIX = 6;

    /** @var int */
    private const NUMBER_THREE = 3;

    /** @var int */
    private const NUMBER_TWO = 2;

    /** @var int */
    private const NUMBER_ZERO = 0;

    /** @var int */
    private const USLEEP_MICROSECONDS = 500000; // 0.5 second

    /** @var bool */
    private $isBlinking = false;

    /** @var int */
    private $number = self::DEFAULT_NUMBER;

    /** @var SegmentConfiguration */
    private $segmentConfiguration;

    /**
     * GPIO constructor.
     *
     * @param SegmentConfiguration $segmentConfiguration
     */
    public function __construct(SegmentConfiguration $segmentConfiguration)
    {
        $this->segmentConfiguration = $segmentConfiguration;

        $this->reset();
        $this->displayNumber();
    }

    /**
     * @param int|null $number
     * @param int|null $times
     *
     * @return void
     */
    public function blinkNumber(?int $number = null, ?int $times = self::DEFAULT_BLINK_TIMES): void
    {
        $this->reset();
        if (null === $number) {
            $number = $this->number;
        }

        $this->number = $number;
        $pinsOverAllDisplays = [];
        /** @var PinConfiguration $display */
        foreach ($this->segmentConfiguration->getDisplays() as $display) {
            $segmentNumber = $this->getNumberForConfiguration($number, $display);
            $pinsForSegmentNumber = $this->getPinsForNumber($segmentNumber, $display);
            foreach ($pinsForSegmentNumber as $pin) {
                $pinsOverAllDisplays[] = $pin;
            }
        }

        $this->blinkBlocking($pinsOverAllDisplays, $times);
    }

    /**
     * @return void
     */
    public function blinkTimer(): void
    {
        $this->reset();
        $number = $this->number;
        $pinsOverAllDisplays = [];
        /** @var PinConfiguration $display */
        foreach ($this->segmentConfiguration->getDisplays() as $display) {
            $segmentNumber = $this->getNumberForConfiguration($number, $display);
            $pinsForSegmentNumber = $this->getPinsForNumber($segmentNumber, $display);
            foreach ($pinsForSegmentNumber as $pin) {
                $pinsOverAllDisplays[] = $pin;
            }
        }

        foreach ($pinsOverAllDisplays as $pin) {
            $pin->setValue($this->getLowSignal());
        }

        usleep(self::USLEEP_MICROSECONDS);
        foreach ($pinsOverAllDisplays as $pin) {
            $pin->setValue($this->getHighSignal());
        }
    }

    /**
     * @param int|null $number
     *
     * @return void
     */
    public function displayNumber(?int $number = null): void
    {
        $this->reset();
        if (null === $number) {
            $number = $this->number;
        }

        $this->number = $number;
        /** @var PinConfiguration $display */
        foreach ($this->segmentConfiguration->getDisplays() as $display) {
            $segmentNumber = $this->getNumberForConfiguration($number, $display);
            $pinsForSegmentNumber = $this->getPinsForNumber($segmentNumber, $display);
            /** @var OutputPinInterface $pin */
            foreach ($pinsForSegmentNumber as $pin) {
                $pin->setValue($this->getHighSignal());
            }
        }
    }

    /**
     * @return void
     */
    public function incrementNumber(): void
    {
        $this->displayNumber(++$this->number);
    }

    /**
     * @return void
     */
    public function interruptBlink(): void
    {
        $this->isBlinking = false;
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        /** @var PinConfiguration $display */
        foreach ($this->segmentConfiguration->getDisplays() as $display) {
            /** @var OutputPinInterface $pin */
            foreach ($display->getPins() as $pin) {
                $pin->setValue($this->getLowSignal());
            }
        }
    }

    /**
     * @param array $pinsOverAllDisplays
     * @param int   $times
     *
     * @return void
     */
    private function blinkBlocking(array $pinsOverAllDisplays, int $times): void
    {
        $this->isBlinking = true;
        while ($this->isBlinking) {
            /** @var OutputPinInterface $pin */
            foreach ($pinsOverAllDisplays as $pin) {
                $pin->setValue($this->getLowSignal());
            }

            usleep(self::USLEEP_MICROSECONDS);
            foreach ($pinsOverAllDisplays as $pin) {
                $pin->setValue($this->getHighSignal());
            }

            usleep(self::USLEEP_MICROSECONDS);
            $times--;
            // phpcs:ignore SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
            if (!$times) {
                $this->interruptBlink();
            }
        }
    }

    /**
     * @return int
     */
    private function getHighSignal(): int
    {
        if (SegmentConfiguration::MODE_ANODE === $this->segmentConfiguration->getMode()) {
            return PinInterface::VALUE_LOW;
        }

        return PinInterface::VALUE_HIGH;
    }

    /**
     * @return int
     */
    private function getLowSignal(): int
    {
        if (SegmentConfiguration::MODE_ANODE === $this->segmentConfiguration->getMode()) {
            return PinInterface::VALUE_HIGH;
        }

        return PinInterface::VALUE_LOW;
    }

    /**
     * @param int              $number
     * @param PinConfiguration $pinConfiguration
     *
     * @return int
     */
    private function getNumberForConfiguration(int $number, PinConfiguration $pinConfiguration): int
    {
        $step = $pinConfiguration->getStep();
        $splitted = array_reverse(str_split((string) $number));
        $numberStep = self::DEFAULT_NUMBER;
        if (PinConfiguration::STEP_SINGLE === $step) {
            $numberStep = $splitted[self::ARRAY_INDEX_SINGLE] ?? self::DEFAULT_NUMBER;
        }

        if (PinConfiguration::STEP_DECIMAL === $step) {
            $numberStep = $splitted[self::ARRAY_INDEX_DECIMAL] ?? self::DEFAULT_NUMBER;
        }

        if (PinConfiguration::STEP_HUNDRED === $step) {
            $numberStep = $splitted[self::ARRAY_INDEX_HUNDRED] ?? self::DEFAULT_NUMBER;
        }

        if (PinConfiguration::STEP_THOUSAND === $step) {
            $numberStep = $splitted[self::ARRAY_INDEX_THOUSAND] ?? self::DEFAULT_NUMBER;
        }

        return (int) $numberStep;
    }

    /**
     * @param int              $number
     * @param PinConfiguration $pinConfiguration
     *
     * @return array
     */
    private function getPinsForNumber(int $number, PinConfiguration $pinConfiguration): array
    {
        $pinsForNumber = [];
        $neededSegments = self::NUMBER_CONFIGURATION[$number];
        $pins = $pinConfiguration->getPins();
        foreach ($neededSegments as $segment) {
            $pinsForNumber[] = $pins[$segment];
        }

        return $pinsForNumber;
    }
}
