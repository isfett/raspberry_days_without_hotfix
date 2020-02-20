<?php
declare(strict_types = 1);

namespace RaspberryDaysWithoutHotfix;

use React\Socket\Server as ReactSocketServer;

/**
 * Class SocketServer
 */
class SocketServer
{
    /** @var ReactSocketServer */
    private $socketServer;

    /**
     * SocketServer constructor.
     *
     * @param string    $uri
     * @param EventLoop $eventLoop
     */
    public function __construct(string $uri, EventLoop $eventLoop)
    {
        $this->socketServer = new ReactSocketServer($uri, $eventLoop->getEventLoop());
    }

    /**
     * @return ReactSocketServer
     */
    public function getSocketServer(): ReactSocketServer
    {
        return $this->socketServer;
    }
}
