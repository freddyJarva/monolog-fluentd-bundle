<?php


namespace HiQ\MonologFluentdBundle\Monolog\Handler;

use Fluent\Logger\Entity;
use Fluent\Logger\FluentLogger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use HiQ\MonologFluentdBundle\Monolog\Exception\HiQMonologFluentdHandlerException;
use Psr\Log\InvalidArgumentException;

class FluentdHandler extends AbstractProcessingHandler
{
    const DEFAULT_TAG_FORMAT = '{{channel}}.{{level_name}}';

    /**
     * Maps Monolog log levels to PSR-3 (syslog) log values.
     *
     * @see https://tools.ietf.org/html/rfc5424
     */
    protected static $psr3Levels = [
        Logger::DEBUG => LOG_DEBUG,
        Logger::INFO => LOG_INFO,
        Logger::NOTICE => LOG_NOTICE,
        Logger::WARNING => LOG_WARNING,
        Logger::ERROR => LOG_ERR,
        Logger::CRITICAL => LOG_CRIT,
        Logger::ALERT => LOG_ALERT,
        Logger::EMERGENCY => LOG_EMERG,
    ];

    /** @var FluentLogger */
    protected $logger;

    /** @var string */
    protected $tagFormat = self::DEFAULT_TAG_FORMAT;

    /** @var bool */
    protected $exceptions = true;

    protected const PATTERN = '/^((?:\d\d)?)(\d{6})([-+]?)(\d{3})(\d)$/';

    /**
     * FluentdHandler constructor.
     *
     * @param FluentLogger $logger An instance of FluentdLogger
     * @param int $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(FluentLogger $logger, $level = Logger::DEBUG, $bubble = true)
    {
        $this->logger = $logger;
        parent::__construct($level, $bubble);
    }

    /**
     * @inheritDoc
     */
    public function handle(array $record):bool
    {
        if ($record['channel'] == 'doctrine' && isset($record['context'][0]) && !is_object($record['context'][0])) {
            //if a username is involved we clean that out
            if (preg_match(self::PATTERN, $record['context'][0], $matches)) {
                unset($record["context"][0]);
            }

            $record["log"] = $record["message"];
            $record["message"] = "Query to database";

            foreach ($record["context"] as $key =>$item) {
                $record["extra"]["parameter"] = $item;
                unset($record["context"][$key]);
            }
        }
        return parent::handle($record);
    }
    /**
     * Get the internal FluentLogger instance.
     *
     * @return FluentLogger
     */
    public function getLogger(): FluentLogger
    {
        return $this->logger;
    }

    /**
     * Sets the tag format
     *
     * @param string $tagFormat
     * @return FluentdHandler
     */
    public function setTagFormat($tagFormat): FluentdHandler
    {
        $this->tagFormat = $tagFormat;
        return $this;
    }

    /**
     * Set exceptions
     * @param bool $exceptions
     *
     * @return FluentdHandler
     */
    public function setExceptions($exceptions): FluentdHandler
    {
        $this->exceptions = (bool)$exceptions;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        $this->logger->close();
    }

    /**
     * Converts Monolog levels to PSR-3 (Syslog) numeric values.
     *
     * @param string|int Level number (monolog)
     * @param mixed $level
     *
     * @return int Psr-3 level number
     * @throws InvalidArgumentException
     *
     */
    public static function toPsr3Level($level)
    {
        if (isset(static::$psr3Levels[$level])) {
            return static::$psr3Levels[$level];
        }

        throw new InvalidArgumentException(sprintf(
            'Level "%s" is not defined, use one among "%s".',
            $level,
            implode('", "', array_keys(static::$psr3Levels))
        ));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    protected function write(array $record): void
    {
        unset($record['formatted']);

        try {
            $this->logger->post2(new Entity(
                $this->buildTag($record),
                $record,
                $record['datetime']->getTimestamp()
            ));
        } catch (\Exception $e) {
            if ($this->exceptions) {
                throw new HiQMonologFluentdHandlerException(
                    sprintf('An error occurred on fluentd side: "%s".', $e->getMessage()),
                    0,
                    $e
                );
            }
        }
    }

    /**
     * @param array $record
     *
     * @return string
     * @throws \LogicException
     *
     */
    protected function buildTag(array $record)
    {
        $tag = $this->tagFormat;
        if (!preg_match_all("/\{\{(.*?)\}\}/", $tag, $matches)) {
            return $tag;
        }

        /** @var array[] $matches */
        foreach ($matches[1] as $match) {
            if (isset($record[$match])) {
                $tag = str_replace("{{{$match}}}", $record[$match], $tag);
                continue;
            }

            throw new \LogicException(sprintf('No such field "%s" in the record', $match));
        }

        return $tag;
    }
}