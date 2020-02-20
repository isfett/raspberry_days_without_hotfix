<?php
require_once './vendor/autoload.php';

use PiPHP\GPIO\GPIO as PiPHPGPIO;
use PiPHP\GPIO\Pin\PinInterface;
use RaspberryDaysWithoutHotfix\GPIO\PinConfiguration;
use RaspberryDaysWithoutHotfix\GPIO\SegmentConfiguration;
use RaspberryDaysWithoutHotfix\GPIO\GPIO;
use RaspberryDaysWithoutHotfix\EventLoop;
use RaspberryDaysWithoutHotfix\HttpServer;
use RaspberryDaysWithoutHotfix\SocketServer;

$piphpgpio = new PiPHPGPIO();
$single = new PinConfiguration(PinConfiguration::STEP_SINGLE);
$single->setPinForSegment(PinConfiguration::SEGMENT_A, $piphpgpio->getOutputPin(14));
$single->setPinForSegment(PinConfiguration::SEGMENT_B, $piphpgpio->getOutputPin(15));
$single->setPinForSegment(PinConfiguration::SEGMENT_C, $piphpgpio->getOutputPin(18));
$single->setPinForSegment(PinConfiguration::SEGMENT_D, $piphpgpio->getOutputPin(23));
$single->setPinForSegment(PinConfiguration::SEGMENT_E, $piphpgpio->getOutputPin(24));
$single->setPinForSegment(PinConfiguration::SEGMENT_F, $piphpgpio->getOutputPin(25));
$single->setPinForSegment(PinConfiguration::SEGMENT_G, $piphpgpio->getOutputPin(8));

$decimal = new PinConfiguration(PinConfiguration::STEP_DECIMAL);
$decimal->setPinForSegment(PinConfiguration::SEGMENT_A, $piphpgpio->getOutputPin(2));
$decimal->setPinForSegment(PinConfiguration::SEGMENT_B, $piphpgpio->getOutputPin(3));
$decimal->setPinForSegment(PinConfiguration::SEGMENT_C, $piphpgpio->getOutputPin(4));
$decimal->setPinForSegment(PinConfiguration::SEGMENT_D, $piphpgpio->getOutputPin(17));
$decimal->setPinForSegment(PinConfiguration::SEGMENT_E, $piphpgpio->getOutputPin(27));
$decimal->setPinForSegment(PinConfiguration::SEGMENT_F, $piphpgpio->getOutputPin(22));
$decimal->setPinForSegment(PinConfiguration::SEGMENT_G, $piphpgpio->getOutputPin(10));

$configuration = new SegmentConfiguration();
$configuration->addDisplay('single', $single, SegmentConfiguration::MODE_ANODE);
$configuration->addDisplay('decimal', $decimal, SegmentConfiguration::MODE_ANODE);

$gpio = new GPIO($configuration);

echo 'Functional Test' . \PHP_EOL;
sleep(1);
for ($i = 0; $i <= 99; $i++) {
    $gpio->displayNumber($i);
    usleep(50000);
}

sleep(1);
$gpio->blinkNumber(13, 3);
sleep(1);
$gpio->displayNumber(0);
sleep(1);
echo 'Functional Test finished' . \PHP_EOL;

$loop = new EventLoop(React\EventLoop\Factory::create(), $gpio);
$server = new HttpServer($loop, $gpio);

$socket = new SocketServer('0.0.0.0:80', $loop);
$server->listen($socket);
$loop->run();