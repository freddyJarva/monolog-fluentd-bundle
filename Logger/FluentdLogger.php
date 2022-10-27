<?php

namespace HiQ\MonologFluentdBundle\Logger;

use Fluent\Logger\FluentLogger;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\InvalidArgumentException;
use Symfony\Component\HttpFoundation\RequestStack;

class FluentdLogger extends FluentLogger
{
    /**
     * @var FluentdLogger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var array
     */
    protected $channels;

    /**
     * @var string
     */
    protected $sourceSystem;

    const CHANNEL_STATISTICS = "CHANNEL_STATISTICS";
    const CHANNEL_PLANIT = "CHANNEL_PLANIT";
    const CHANNEL_PHP = "CHANNEL_PHP";
    const CONTAINER = "CONTAINER";

    /**
     * FluentdLogger constructor.
     *
     * @param ContainerInterface $container
     * @param RequestStack $requestStack
     *
     */
    public function __construct(ContainerInterface $container, RequestStack $requestStack)
    {
        $configuration = $this->getParameters($container);
        $this->setConfiguration($configuration);
        $host = current($this->getConfiguration("host"));
        $port = current($this->getConfiguration("port"));
        $options = current($this->getConfiguration("options"));
        $this->setLogger(parent::__construct($host, $port, $options));

        $this->requestStack = $requestStack;
        $this->channels = current($this->getConfiguration("channels"));
    }

    /**
     * Returns the configuration array
     *
     * @param ContainerInterface $container
     *
     * @return array
     */
    protected function getParameters(ContainerInterface $container): array
    {
        return [
            "host" => $container->getParameter('monolog_fluentd.host'),
            "port" => $container->getParameter('monolog_fluentd.port'),
            "options" => $container->getParameter('monolog_fluentd.options'),
            "level" => $container->getParameter('monolog_fluentd.level'),
            "tag" => $container->getParameter('monolog_fluentd.tag_fmt'),
            "enable_exceptions" => $container->getParameter('monolog_fluentd.enable_exceptions'),
            "channels" => $container->getParameter('monolog_fluentd.channels'),
        ];
    }

    /**
     * Sets the current source system
     *
     * @param string $sourceSystem
     *
     * @return FluentdLogger
     */
    public function setSourceSystem(string $sourceSystem): FluentdLogger
    {
        $this->sourceSystem = $sourceSystem;
        return $this;
    }

    /**
     * Gets the current source system
     *
     * @return string
     */
    public function getSourceSystem(): string
    {
        return (string) $this->sourceSystem;
    }

    /**
     * General post function
     *
     * @param string $tag
     * @param int $level
     * @param array $info
     *
     * @return bool
     */
    public function postData(string $tag, int $level, array $info): bool
    {
        $data = $this->createData($level);
        $data["message"] = $info["message"];
        return parent::post($this->channels[self::CHANNEL_STATISTICS] . "." . $tag, $data);
    }

    /**
     * Posting statistics for a booking
     *
     * @param string $tag
     * @param string $message
     * @param int $level
     * @param array $bookingData
     *
     * @return bool
     */
    public function postStatsBooking(string $tag, string $message, int $level, array $bookingData): bool
    {
        $data = $this->createData($level);
        $data["context"] = array_merge($data["context"], $bookingData);
        $data["channel"] = $this->channels[self::CHANNEL_STATISTICS];
        $data["message"] = $message;

        return parent::post($this->channels[self::CHANNEL_STATISTICS] . "." . $tag, $data);
    }

    /**
     * Posting our own exceptions
     *
     * @param string $message
     * @param int $level
     * @param array $info
     *
     * @return bool
     */
    public function postRattenException(string $message, int $level, array $info): bool
    {
        $data = $this->createData($level);
        $data["channel"] = $this->channels[self::CHANNEL_PHP];
        $data["message"] = $message;
        $data["context"]["error"] = $info;
        try {
            $tag = Logger::getLevelName($level);
        } catch (InvalidArgumentException $ex) {
            $tag = "INFO";
        }

        return parent::post($this->channels[self::CHANNEL_PHP] . "." . $tag, $data);
    }

    /**
     * To post general exceptions
     *
     * @param string $message
     * @param int $level
     * @param array $info
     *
     * @return bool
     */
    public function postException(string $message, int $level, array $info): bool
    {
        $data = $this->createData($level);
        $data["channel"] = $this->channels[self::CHANNEL_PHP];
        $data["message"] = $message;
        $data["context"]["error"] = $info;
        try {
            $tag = Logger::getLevelName($level);
        } catch (InvalidArgumentException $ex) {
            $tag = "INFO";
        }

        return parent::post($this->channels[self::CHANNEL_PHP] . "." . $tag, $data);
    }

    /**
     * Logging messages from planit
     *
     * @param string $message
     * @param int $level
     * @param array $info
     *
     * @return bool
     */
    public function postPlanit(string $message, int $level, array $info): bool
    {
        //The data out to client is named message and don't want to change that
        $info["error"]["messages"] = $info["error"]["message"];
        unset($info["error"]["message"]);
        $data = $this->createData($level);
        $data["channel"] = $this->channels[self::CHANNEL_PLANIT];
        $data["message"] = $message;
        $data["planit"]["error"] = $info["error"];
        $data["exception"] = $info["exception"];
        try {
            $tag = Logger::getLevelName($level);
        } catch (InvalidArgumentException $ex) {
            $tag = "INFO";
        }

        return parent::post($this->channels[self::CHANNEL_PLANIT] . "." . $tag, $data);

    }

    /**
     * Get the logger
     *
     * @return FluentdLogger
     */
    public function getLogger(): FluentdLogger
    {
        return $this->logger;
    }

    /**
     * Sets the logger
     *
     * @param $logger
     *
     * @return FluentdLogger
     */
    public function setLogger($logger): FluentdLogger
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Gets the configuration
     *
     * @param string $key
     *
     * @return array
     */
    public function getConfiguration(string $key = ""): array
    {
        if (!empty($key)) {
            return [$this->configuration[$key]];
        }
        return $this->configuration;
    }

    /**
     * Set the configuration
     *
     * @param $configuration
     *
     * @return FluentdLogger
     */
    public function setConfiguration($configuration): FluentdLogger
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * Creates the base info array that all logs should have
     *
     * @param int $level The error level
     *
     * @return array
     *
     */
    protected function createData(int $level): array
    {
        $data = [];
        $request = $this->requestStack->getCurrentRequest();
        $data["context"] = [
            "route" => $request->get("_route"),
            "route_parameters" => [
                "_route" => $request->get("_route"),
                "_controller" => $request->get("_controller"),
            ],
            "request_uri" => $request->getRequestUri(),
            "method" => $request->getMethod(),
            "device" => (string) $this->getSourceSystem(),
        ];

        $data["level"] = $level;
        try {
            $data["level_name"] = Logger::getLevelName($level);
        } catch (InvalidArgumentException $ex) {
            $data["level_name"] = "INFO";
        }
        $data["container_name"] = $this->channels[self::CONTAINER];
        $data["container_id"] = $request->server->get("HOSTNAME");

        return $data;
    }
}

