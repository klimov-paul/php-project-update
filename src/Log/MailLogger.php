<?php

namespace KlimovPaul\PhpProjectUpdate\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

/**
 * MailLogger
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class MailLogger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var string
     */
    private $from;
    /**
     * @var string[]
     */
    private $to = [];
    /**
     * @var string
     */
    private $hostName;
    /**
     * @var array[] log messages
     */
    protected $messages = [];

    /**
     * Destructor.
     * Ensures log is flushed.
     */
    public function __destruct()
    {
        $this->flush();
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        if ($this->from === null) {
            $userName = @exec('whoami');
            if (empty($userName)) {
                $userName = 'unknown.user';
            }
            $this->from = $userName . '@' . $this->getHostName();
        }

        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom(string $from)
    {
        $this->from = $from;
    }

    /**
     * @return string[]
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * @param string[] $to
     */
    public function setTo(array $to)
    {
        $this->to = $to;
    }

    /**
     * @return string
     */
    public function getHostName(): string
    {
        if ($this->hostName === null) {
            $hostName = @exec('hostname');
            if (empty($hostName)) {
                $hostName = 'unknown.host';
            }
            $this->hostName = $hostName;
        }

        return $this->hostName;
    }

    /**
     * @param string $hostName
     */
    public function setHostName(string $hostName)
    {
        $this->hostName = $hostName;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        $this->messages[] = [
            'level' => $level,
            'message' => $message,
        ];
    }

    /**
     * Flushes log messages, sending them to log receivers.
     */
    public function flush()
    {
        if (empty($this->messages)) {
            return;
        }

        $receivers = $this->getTo();
        if (empty($receivers)) {
            $this->messages = [];

            return;
        }

        $body = '';
        $hasError = false;
        foreach ($this->messages as $message) {
            $hasError = $hasError || in_array($message['level'], [LogLevel::ERROR, LogLevel::ALERT, LogLevel::EMERGENCY, LogLevel::CRITICAL], true);
            $body .= $message . "\n";
        }

        $this->messages = [];

        $subjectPrefix = $hasError ? 'Update success' : 'UPDATE FAILED';

        $subject = $subjectPrefix . ': ' . $this->getHostName() . ' at ' . date('Y-m-d H:i:s');

        foreach ($receivers as $to) {
            $this->sendMail($this->getFrom(), $to, $subject, $body);
        }
    }

    /**
     * Sends an email via plain PHP `mail()` function.
     *
     * @param string $from sender email address
     * @param string $to single email address
     * @param string $subject email subject
     * @param string $message email content
     * @return bool success.
     */
    protected function sendMail($from, $to, $subject, $message)
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
        ];
        $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        $matches = [];
        preg_match_all('/([^<]*)<([^>]*)>/iu', $from, $matches);
        if (isset($matches[1][0],$matches[2][0])) {
            $name = '=?UTF-8?B?' . base64_encode(trim($matches[1][0])) . '?=';
            $from = trim($matches[2][0]);
            $headers[] = "From: {$name} <{$from}>";
        } else {
            $headers[] = "From: {$from}";
        }
        $headers[] = "Reply-To: {$from}";

        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
}
