<?php

namespace KlimovPaul\PhpProjectUpdate\Helpers;

/**
 * ShellResult represents shell command execution result.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class ShellResult
{
    /**
     * @var string command being executed.
     */
    public $command;
    /**
     * @var int shell command execution exit code
     */
    public $exitCode;
    /**
     * @var array shell command output lines.
     */
    public $outputLines = [];

    /**
     * @param string $glue lines glue.
     * @return string shell command output.
     */
    public function getOutput($glue = "\n"): string
    {
        return implode($glue, $this->outputLines);
    }

    /**
     * @return bool whether exit code is OK.
     */
    public function isOk(): bool
    {
        return $this->exitCode === 0;
    }

    /**
     * @return bool whether command execution produced empty output.
     */
    public function isOutputEmpty(): bool
    {
        return empty($this->outputLines);
    }

    /**
     * Checks if output contains given string
     * @param string $string needle string.
     * @return bool whether output contains given string.
     */
    public function isOutputContains($string): bool
    {
        return stripos($this->getOutput(), $string) !== false;
    }

    /**
     * Checks if output matches give regular expression.
     * @param string $pattern regular expression
     * @return bool whether output matches given regular expression.
     */
    public function isOutputMatches($pattern): bool
    {
        return preg_match($pattern, $this->getOutput()) > 0;
    }

    /**
     * @return string string representation of this object.
     */
    public function toString(): string
    {
        return $this->command . "\n" . $this->getOutput() . "\n" . 'Exit code: ' . $this->exitCode;
    }

    /**
     * PHP magic method that returns the string representation of this object.
     * @return string the string representation of this object.
     */
    public function __toString()
    {
        // __toString cannot throw exception
        // use trigger_error to bypass this limitation
        try {
            return $this->toString();
        } catch (\Throwable $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
            return '';
        }
    }
}
