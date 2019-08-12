<?php

namespace KlimovPaul\PhpProjectUpdate\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * LoggerAggregator aggregates several loggers under the same facade.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class LoggerAggregator implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var \Psr\Log\LoggerInterface[] aggregated loggers.
     */
    private $loggers = [];

    /**
     * @return LoggerInterface[] aggregated loggers.
     */
    public function getLoggers(): array
    {
        return $this->loggers;
    }

    /**
     * @param LoggerInterface[] $loggers loggers to be aggregated.
     */
    public function setLoggers(array $loggers)
    {
        $this->loggers = $loggers;
    }

    /**
     * @param LoggerInterface $logger logger to be aggregated.
     * @return static self reference.
     */
    public function addLogger(LoggerInterface $logger): self
    {
        $this->loggers[] = $logger;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        foreach ($this->getLoggers() as $logger) {
            $logger->log($level, $message, $context);
        }
    }
}
