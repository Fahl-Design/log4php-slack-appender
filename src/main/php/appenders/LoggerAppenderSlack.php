<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements. See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at.
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * LoggerAppenderSlack appends log events to a Slack channel.
 *
 *
 * @since      2.4.0
 *
 * @author     Benjamin Fahl <ben@webproject.xyz>
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 *
 * @link       http://graylog2.org/ Graylog2 website
 */
class LoggerAppenderSlack extends LoggerAppender
{
    const ENDPOINT_VALIDATION_STRING = 'https://hooks.slack.com/';

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
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setUsername($username)
    {
        if (!empty($username)) {
            $this->_username = (string) $username;
        } else {
            throw new \InvalidArgumentException('username missing');
        }

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
     * @return LoggerAppenderSlack
     *
     * @throws \InvalidArgumentException
     */
    public function setEndpoint($endpoint)
    {
        if (true === is_string($endpoint) && 0 === strpos($endpoint, self::ENDPOINT_VALIDATION_STRING, 0)) {
            $this->_endpoint = $endpoint;
        } else {
            throw new \InvalidArgumentException('invalid endpoint');
        }

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
        // get slack client
        $slackClient = $this->_getSlackClient();

        // create message
        $message = $slackClient->createMessage();
        // set username
        $message->setUsername($this->_getUsername());
        // set icon
        $message->setIcon($this->_getIcon());
        // set channel
        $message->setChannel($this->_getChannel());
        // inject formatted message from event
        $message->setText(trim($this->layout->format($event)));

        // send message
        $message->send();
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
     * Get Client.
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
}
