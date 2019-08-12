<?php

namespace KlimovPaul\PhpProjectUpdate\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ConsoleOutputLogger directs log messages to console output.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class ConsoleOutputLogger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface console output.
     */
    protected $output;

    /**
     * Constructor.
     *
     * @param OutputInterface $output console output.
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        if (in_array($level, [LogLevel::ERROR, LogLevel::ALERT, LogLevel::EMERGENCY, LogLevel::CRITICAL], true)) {
            $type = sprintf('[%s] ', strtoupper($level));
            $style = 'fg=white;bg=red';
            $message = $type . $message;
            $message = sprintf('<%s>%s</>', $style, $message);

            $this->output->writeln($message);
            return;
        }

        $this->output->writeln($message);
    }
}
