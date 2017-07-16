<?php
/**
 * LoggerAppenderSlack appends log events to a Slack channel.
 *
 *
 * @since      2.4.0
 *
 * @author     Benjamin Fahl <ben@webproject.xyz>
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License, v2.0
 *
 * @link       http://logging.apache.org/log4php
 */
class LoggerAppenderSlack extends LoggerAppender
{
    const ENDPOINT_VALIDATION_STRING = 'https://hooks.slack.com';

    /**
     * @var Maknz\Slack\Client
     */
    protected $_slackClient;

    /**
     * @var string
     */
    protected $_username;

    /**
     * Endpoint (slack hook url 'https://hooks.slack.com/...').
     *
     * @var string
     */
    protected $_endpoint;

    /**
     * @var string
     */
    protected $_channel;

    /**
     * @var string
     */
    protected $_icon;

    /**
     * @var string
     */
    protected $_text;

    /**
     * Get Text.
     *
     * @return string
     */
    protected function _getText()
    {
        return $this->_text;
    }

    /**
     * Overwrite layout with LoggerLayoutSimple.
     *
     * @return LoggerLayoutSimple
     */
    public function getDefaultLayout()
    {
        return new LoggerLayoutSimple();
    }

    /**
     * Forwards the logging event to the destination.
     *
     * Derived appenders should implement this method to perform actual logging.
     *
     * @param LoggerLoggingEvent $event
     */
    protected function append(LoggerLoggingEvent $event)
    {
        // format text with layout
        $this->_formatEventToText($event);
        // get slack client
        $slackClient = $this->_getSlackClient();
        // create message
        $message = new \Maknz\Slack\Message($slackClient);
        // set username
        $message->setUsername($this->_getUsername());
        // set icon
        $message->setIcon($this->_getIcon());
        // set channel
        $message->setChannel($this->_getChannel());
        // inject formatted message from event
        $message->setText($this->_getText());
        // send message
        $message->send();
    }

    /**
     * Wrapper for layout format.
     *
     * @param LoggerLoggingEvent $event
     *
     * @return $this
     */
    protected function _formatEventToText(LoggerLoggingEvent $event)
    {
        $this->_text = trim($this->layout->format($event));

        return $this;
    }

    /**
     * Get SlackClient.
     *
     * @return Maknz\Slack\Client
     */
    protected function _getSlackClient()
    {
        if (null === $this->_slackClient) {
            $this->_initSlackClient();
        }

        return $this->_slackClient;
    }

    /**
     * Set SlackClient.
     *
     * @param \Maknz\Slack\Client $client
     *
     * @return LoggerAppenderSlack
     */
    public function setSlackClient(\Maknz\Slack\Client $client = null)
    {
        $this->_slackClient = $client;

        return $this;
    }

    /**
     * Init php slack client.
     *
     * @return $this
     */
    protected function _initSlackClient()
    {
        $slackClient = new Maknz\Slack\Client($this->_getEndpoint());

        $this->_slackClient = $slackClient;

        return $this;
    }

    /**
     * Get Endpoint.
     *
     * @return string
     */
    protected function _getEndpoint()
    {
        return $this->_endpoint;
    }

    /**
     * Set Endpoint.
     *
     * @param string $endpoint
     *
     * @throws \InvalidArgumentException
     *
     * @return LoggerAppenderSlack
     */
    public function setEndpoint($endpoint)
    {
        if (true === is_string($endpoint)
            && 0 === strpos($endpoint, self::ENDPOINT_VALIDATION_STRING)
        ) {
            $this->_endpoint = $endpoint;
        } else {
            throw new \InvalidArgumentException('invalid endpoint');
        }

        return $this;
    }

    /**
     * Get Username.
     *
     * @return string
     */
    protected function _getUsername()
    {
        return $this->_username;
    }

    /**
     * Set Username.
     *
     * @param string $username
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setUsername($username)
    {
        if (!empty($username) && is_string($username)) {
            $this->_username = (string) $username;
        } else {
            throw new \InvalidArgumentException('username invalid');
        }

        return $this;
    }

    /**
     * Get Icon.
     *
     * @return string
     */
    protected function _getIcon()
    {
        return $this->_icon;
    }

    /**
     * Set Icon.
     *
     * @param string $icon
     *
     * @return LoggerAppenderSlack
     */
    public function setIcon($icon)
    {
        $this->_icon = $icon;

        return $this;
    }

    /**
     * Get Channel.
     *
     * @return string
     */
    protected function _getChannel()
    {
        return $this->_channel;
    }

    /**
     * Set Channel.
     *
     * @param string $channel
     *
     * @return LoggerAppenderSlack
     */
    public function setChannel($channel)
    {
        $this->_channel = $channel;

        return $this;
    }
}
