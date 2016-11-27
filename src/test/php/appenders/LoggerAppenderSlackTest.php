<?php


class LoggerAppenderSlackTest extends PHPUnit_Framework_TestCase
{
    public function testInit()
    {
        // Arrange
        $appender = new LoggerAppenderSlack();

        // Act

        // Assert
        $this->assertTrue(true);

    }

}