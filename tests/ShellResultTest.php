<?php

namespace KlimovPaul\PhpProjectUpdate\Test;

use KlimovPaul\PhpProjectUpdate\ShellResult;

class ShellResultTest extends TestCase
{
    public function testGetOutput()
    {
        $shellResult = new ShellResult();
        $shellResult->outputLines = [
            'line1',
            'line2',
        ];
        $this->assertEquals("line1\nline2", $shellResult->getOutput());
    }

    public function testIsOk()
    {
        $shellResult = new ShellResult();

        $shellResult->exitCode = 0;
        $this->assertTrue($shellResult->isOk());

        $shellResult->exitCode = 1;
        $this->assertFalse($shellResult->isOk());
    }

    public function testIsOutputEmpty()
    {
        $shellResult = new ShellResult();

        $this->assertTrue($shellResult->isOutputEmpty());

        $shellResult->outputLines = ['line1'];
        $this->assertFalse($shellResult->isOutputEmpty());
    }

    /**
     * @depends testGetOutput
     */
    public function testIsOutputContains()
    {
        $shellResult = new ShellResult();
        $shellResult->outputLines = [
            'line1',
            'line2',
            'line3',
        ];
        $this->assertTrue($shellResult->isOutputContains('line2'));
        $this->assertFalse($shellResult->isOutputContains('line4'));
    }

    /**
     * @depends testGetOutput
     */
    public function testIsOutputMatches()
    {
        $shellResult = new ShellResult();
        $shellResult->outputLines = [
            'line1',
            'line2',
            'line3',
        ];
        $this->assertTrue($shellResult->isOutputMatches('/line2/'));
        $this->assertFalse($shellResult->isOutputMatches('/line4/'));
    }

    /**
     * @depends testGetOutput
     */
    public function testToString()
    {
        $shellResult = new ShellResult();
        $shellResult->command = 'some-command';
        $shellResult->outputLines = [
            'line1',
            'line2',
        ];
        $shellResult->exitCode = 99;

        $string = $shellResult->toString();
        $this->assertStringContainsString($shellResult->command, $string);
        $this->assertStringContainsString((string) $shellResult->exitCode, $string);
        $this->assertStringContainsString($shellResult->outputLines[0], $string);
        $this->assertStringContainsString($shellResult->outputLines[1], $string);

        $this->assertSame($string, (string) $shellResult);
    }
}
