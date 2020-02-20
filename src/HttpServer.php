<?php
declare(strict_types = 1);

namespace RaspberryDaysWithoutHotfix;

use Psr\Http\Message\ServerRequestInterface;
use RaspberryDaysWithoutHotfix\GPIO\GPIO;
use React\EventLoop\TimerInterface;
use React\Http\Response;
use React\Http\Server as ReactHttpServer;

/**
 * Class HttpServer
 */
class HttpServer
{
    /** @var string */
    private const EVENT_ERROR = 'error';

    /** @var string */
    private const FORMAT_ERROR_MESSAGE = 'Error: %s%s';

    /** @var string */
    private const FORMAT_SERVER_RUNNING = 'Server running at %s%s';

    /** @var string */
    private const FORMAT_SET_DISPLAY_TO = 'Set display to %s';

    /** @var string */
    private const FUNCTION_REQUEST_HANDLER = 'onRequest';

    /** @var string */
    private const MESSAGE_CANCELED_HOTFIX = 'Canceled Hotfix';

    /** @var string */
    private const MESSAGE_FINISHED_HOTFIX = 'Finished Hotfix';

    /** @var string */
    private const MESSAGE_STARTED_HOTFIX = 'Started Hotfix';

    /** @var string */
    private const PARAMETER_NUMBER = 'number';

    /** @var int */
    private const RESPONSE_ERROR = 500;

    /** @var int */
    private const RESPONSE_NOT_FOUND = 404;

    /** @var int */
    private const RESPONSE_OK = 200;

    /** @var string */
    private const ROUTE_CANCEL_HOTFIX = '/cancelHotfix';

    /** @var string */
    private const ROUTE_CHEAT = '/cheat';

    /** @var string */
    private const ROUTE_FINISH_HOTFIX = '/finishHotfix';

    /** @var string */
    private const ROUTE_PING = '/ping';

    /** @var string */
    private const ROUTE_START_HOTFIX = '/startHotfix';

    /** @var TimerInterface|null */
    private $blinkTimer;

    /** @var GPIO */
    private $gpio;

    /** @var ReactHttpServer */
    private $httpServer;

    /** @var EventLoop */
    private $loop;

    /**
     * HttpServer constructor.
     *
     * @param EventLoop $loop
     * @param GPIO      $gpio
     */
    public function __construct(EventLoop $loop, GPIO $gpio)
    {
        $this->httpServer = new ReactHttpServer([$this, self::FUNCTION_REQUEST_HANDLER]);
        $this->httpServer->on(self::EVENT_ERROR, static function (\Throwable $e): void {
            echo sprintf(self::FORMAT_ERROR_MESSAGE, $e->getMessage(), \PHP_EOL);
        });
        $this->loop = $loop;
        $this->gpio = $gpio;
    }

    /**x
     * @param SocketServer $socketServer
     *
     * @return void
     */
    public function listen(SocketServer $socketServer): void
    {
        $this->httpServer->listen($socketServer->getSocketServer());
        echo sprintf(self::FORMAT_SERVER_RUNNING, $socketServer->getSocketServer()->getAddress(), \PHP_EOL);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return Response
     */
    public function onRequest(ServerRequestInterface $request): Response
    {
        switch ($request->getUri()->getPath()) {
            case self::ROUTE_FINISH_HOTFIX:
                return $this->finishHotfixAction();
            case self::ROUTE_CANCEL_HOTFIX:
                return $this->cancelHotfixAction();
            case self::ROUTE_START_HOTFIX:
                return $this->startHotfixAction();
            case self::ROUTE_CHEAT:
                return $this->cheatAction($request);
            case self::ROUTE_PING:
                return $this->pingAction();
            default:
                return new Response(self::RESPONSE_NOT_FOUND);
        }
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return Response
     */
    private function cheatAction(ServerRequestInterface $request): Response
    {
        $params = $request->getQueryParams();
        if (!array_key_exists(self::PARAMETER_NUMBER, $params)) {
            return new Response(self::RESPONSE_ERROR);
        }

        $number = $params[self::PARAMETER_NUMBER];
        $this->gpio->displayNumber((int) $number);

        return new Response(self::RESPONSE_OK, [], sprintf(self::FORMAT_SET_DISPLAY_TO, $number));
    }

    /**
     * @return Response
     */
    private function cancelHotfixAction(): Response
    {
        if (null !== $this->blinkTimer) {
            $this->loop->cancelBlinkTimer($this->blinkTimer);
            $this->blinkTimer = null;
        }

        return new Response(self::RESPONSE_OK, [], self::MESSAGE_CANCELED_HOTFIX);
    }

    /**
     * @return Response
     */
    private function finishHotfixAction(): Response
    {
        if (null !== $this->blinkTimer) {
            $this->loop->cancelBlinkTimer($this->blinkTimer);
            $this->blinkTimer = null;
        }

        $this->gpio->displayNumber(GPIO::DEFAULT_NUMBER);
        $this->loop->resetHotfixTimer();

        return new Response(self::RESPONSE_OK, [], self::MESSAGE_FINISHED_HOTFIX);
    }

    /**
     * @return Response
     */
    private function pingAction(): Response
    {
        return new Response(self::RESPONSE_OK);
    }

    /**
     * @return Response
     */
    private function startHotfixAction(): Response
    {
        if (null === $this->blinkTimer) {
            $this->blinkTimer = $this->loop->blinkInfinitely();
        }

        return new Response(self::RESPONSE_OK, [], self::MESSAGE_STARTED_HOTFIX);
    }
}
