<?php

namespace KlimovPaul\PhpProjectUpdate\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * LoggerAwareTrait provides basic implementation of {@see \Psr\Log\LoggerAwareInterface}.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait LoggerAwareTrait
{
    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Sets a logger.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Returns a logger.
     *
     * @return LoggerInterface logger.
     */
    public function getLogger(): LoggerInterface
    {
        if ($this->logger === null) {
            $this->logger = $this->defaultLogger();
        }

        return $this->logger;
    }

    /**
     * @return LoggerInterface default logger.
     */
    protected function defaultLogger(): LoggerInterface
    {
        return new NullLogger();
    }
}
