<?php
declare(strict_types = 1);

namespace RaspberryDaysWithoutHotfix\GPIO;

/**
 * Class SegmentConfiguration
 */
class SegmentConfiguration
{
    /** @var string */
    public const MODE_ANODE = 'anode';

    /** @var string */
    public const MODE_CATHODE = 'cathode';

    /** @var array<string, PinConfiguration> */
    private $displays = [];

    /** @var string */
    private $mode = self::MODE_ANODE;

    /**
     * @param string           $identifier
     * @param PinConfiguration $pinConfiguration
     * @param string|null      $mode
     *
     * @return void
     */
    public function addDisplay(string $identifier, PinConfiguration $pinConfiguration, ?string $mode = null): void
    {
        $this->displays[$identifier] = $pinConfiguration;
        // phpcs:ignore SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
        if (null !== $mode) {
            $this->mode = $mode;
        }
    }

    /**
     * @return array<string, PinConfiguration>
     */
    public function getDisplays(): array
    {
        return $this->displays;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode(string $mode): void
    {
        $this->mode = $mode;
    }
}
