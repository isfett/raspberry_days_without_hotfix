<?php
declare(strict_types = 1);

namespace RaspberryDaysWithoutHotfix\GPIO;

use PiPHP\GPIO\Pin\OutputPinInterface;

/**
 * Class PinConfiguration
 */
class PinConfiguration
{
    /** @var string */
    private const ERROR_MESSAGE_SEGMENT_DOES_NOT_EXIST = 'Segment does not exist';

    /** @var string */
    public const SEGMENT_A = 'a';

    /** @var string */
    public const SEGMENT_B = 'b';

    /** @var string */
    public const SEGMENT_C = 'c';

    /** @var string */
    public const SEGMENT_D = 'd';

    /** @var string */
    public const SEGMENT_DECIMAL_POINT = 'dp';

    /** @var string */
    public const SEGMENT_E = 'e';

    /** @var string */
    public const SEGMENT_F = 'f';

    /** @var string */
    public const SEGMENT_G = 'g';

    /** @var string */
    public const STEP_DECIMAL = 'ten';

    /** @var string */
    public const STEP_HUNDRED = 'hundred';

    /** @var string */
    public const STEP_SINGLE = 'single';

    /** @var string */
    public const STEP_THOUSAND = 'thousand';

    /** @var array<string, OutputPinInterface|null> */
    private $pins = [
        self::SEGMENT_A => null,
        self::SEGMENT_B => null,
        self::SEGMENT_C => null,
        self::SEGMENT_D => null,
        self::SEGMENT_E => null,
        self::SEGMENT_F => null,
        self::SEGMENT_G => null,
        self::SEGMENT_DECIMAL_POINT => null,
    ];

    /** @var string */
    private $step = self::STEP_SINGLE;

    /**
     * PinConfiguration constructor.
     *
     * @param string $step
     */
    public function __construct(?string $step = null)
    {
        // phpcs:ignore SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
        if (null !== $step) {
            $this->step = $step;
        }
    }

    /**
     * @return array<string, OutputPinInterface>
     */
    public function getPins(): array
    {
        return array_filter($this->pins);
    }

    /**
     * @return string|null
     */
    public function getStep(): ?string
    {
        return $this->step;
    }

    /**
     * @param string|null $step
     */
    public function setStep(?string $step): void
    {
        $this->step = $step;
    }

    /**
     * @param string             $segment
     * @param OutputPinInterface $pin
     *
     * @return void
     */
    public function setPinForSegment(string $segment, OutputPinInterface $pin): void
    {
        if (!array_key_exists($segment, $this->pins)) {
            throw new \OutOfBoundsException(self::ERROR_MESSAGE_SEGMENT_DOES_NOT_EXIST);
        }

        $this->pins[$segment] = $pin;
    }
}
