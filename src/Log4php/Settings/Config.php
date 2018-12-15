<?php
declare(strict_types=1);

namespace WebProject\Log4php\Settings;

/**
 * Class Config
 */
class Config implements \JsonSerializable
{
    public const ENDPOINT_VALIDATION_STRING = 'https://hooks.slack.com';

    public const COLOR_DEBUG = '#BDBDBD';
    public const COLOR_INFO = '#64B5F6';
    public const COLOR_WARN = '#FFA726';
    public const COLOR_ERROR = '#EF6C00';
    public const COLOR_FATAL = '#D84315';
    public const COLOR_DEFAULT = 'good';
    public const COLORS = [
        \LoggerLevel::TRACE => self::COLOR_DEBUG,
        \LoggerLevel::DEBUG => self::COLOR_DEBUG,
        \LoggerLevel::INFO  => self::COLOR_INFO,
        \LoggerLevel::WARN  => self::COLOR_WARN,
        \LoggerLevel::ERROR => self::COLOR_ERROR,
        \LoggerLevel::FATAL => self::COLOR_FATAL
    ];
    public const KEY_ENDPOINT = 'endpoint';
    public const KEY_CHANNEL = 'channel';
    public const KEY_USERNAME = 'username';
    public const KEY_ICON = 'icon';
    public const KEY_LINK_NAMES = 'linkNames';
    public const KEY_UNFURL_LINKS = 'unfurlLinks';
    public const KEY_UNFURL_MEDIA = 'unfurlMedia';
    public const KEY_ALLOW_MARKDOWN = 'allowMarkdown';
    public const KEY_MARKDOWN_IN_ATTACHMENTS_FIELDS = 'markdownInAttachmentsFields';
    public const VALUE_MARKDOWN_IN_ATTACHMENTS_PRETEXT = 'pretext';
    public const VALUE_MARKDOWN_IN_ATTACHMENTS_TEXT = 'text';
    public const VALUE_MARKDOWN_IN_ATTACHMENTS_TITLE = 'title';
    public const VALUE_MARKDOWN_IN_ATTACHMENTS_FIELDS = 'fields';
    public const VALUE_MARKDOWN_IN_ATTACHMENTS_FALLBACK = 'fallback';
    public const KEY_AS_ATTACHMENT = 'asAttachment';
    public const KEY_MAX_MESSAGE_LENGTH = 'maxMessageLength';
    public const KEY_SET_ICON_BY_LOG_LEVEL = 'iconByLevel';
    public const KEY_ADD_LOGGER_TO_MESSAGE = 'addLoggerNameToMessage';

    /**
     * @var array
     */
    protected static $_defaultConfig = [

        /**
         *-------------------------------------------------------------
         * Incoming webhook endpoint
         *-------------------------------------------------------------
         *
         * The endpoint which Slack generates when creating a
         * new incoming webhook. It will look something like
         * https://hooks.slack.com/services/XXXXXXXX/XXXXXXXX/XXXXXXXXXXXXXX
         */
        self::KEY_ENDPOINT => '',

        /**
         *-------------------------------------------------------------
         * Default channel
         *-------------------------------------------------------------
         *
         * The default channel we should post to. The channel can either be a
         * channel like #general, a private #group, or a @username. Set to
         * null to use the default set on the Slack webhook
         */
        self::KEY_CHANNEL => '#general',

        /**
         *-------------------------------------------------------------
         * Default username
         *-------------------------------------------------------------
         *
         * The default username we should post as. Set to null to use
         * the default set on the Slack webhook
         */
        self::KEY_USERNAME => 'Robot',

        /**
         *-------------------------------------------------------------
         * Default icon
         *-------------------------------------------------------------
         *
         * The default icon to use. This can either be a URL to
         * an image or Slack emoji like :ghost: or :heart_eyes:.
         * Set to null to use the default
         * set on the Slack webhook
         */
        self::KEY_ICON => null,

        /**
         *-------------------------------------------------------------
         * Link names
         *-------------------------------------------------------------
         *
         * Whether names like @regan should be converted into links
         * by Slack
         */
        self::KEY_LINK_NAMES => false,

        /**
         *-------------------------------------------------------------
         * Unfurl links
         *-------------------------------------------------------------
         *
         * Whether Slack should unfurl links to text-based content
         */
        self::KEY_UNFURL_LINKS => false,

        /**
         *-------------------------------------------------------------
         * Unfurl media
         *-------------------------------------------------------------
         *
         * Whether Slack should unfurl links to media content such
         * as images and YouTube videos
         */
        self::KEY_UNFURL_MEDIA => true,

        /**
         *-------------------------------------------------------------
         * Markdown in message text
         *-------------------------------------------------------------
         *
         * Whether message text should be interpreted in Slack's Markdown-like
         * language. For formatting options, see Slack's help article:
         *
         * @link http://goo.gl/r4fsdO
         */
        self::KEY_ALLOW_MARKDOWN => true,

        /**
         *-------------------------------------------------------------
         * Markdown in attachments
         *-------------------------------------------------------------
         *
         * Which attachment fields should be interpreted in Slack's
         * Markdown-like language. By default, Slack assumes that
         * no fields in an attachment
         * should be formatted as Markdown.
         *
         * Allow Markdown in just the text and title fields
         * self::KEY_MARKDOWN_IN_ATTACHMENTS_FIELDS => [
         *    self::VALUE_MARKDOWN_IN_ATTACHMENTS_TEXT,
         *    self::VALUE_MARKDOWN_IN_ATTACHMENTS_TITLE
         *   ]
         *
         *  Allow Markdown in all fields
         *  'markdown_in_attachments' =>
         *    [
         *      self::VALUE_MARKDOWN_IN_ATTACHMENTS_PRETEXT,
         *      self::VALUE_MARKDOWN_IN_ATTACHMENTS_TEXT,
         *      self::VALUE_MARKDOWN_IN_ATTACHMENTS_TITLE,
         *      self::VALUE_MARKDOWN_IN_ATTACHMENTS_FIELDS,
         *      self::VALUE_MARKDOWN_IN_ATTACHMENTS_FALLBACK
         *    ]
         */
        self::KEY_MARKDOWN_IN_ATTACHMENTS_FIELDS => [],

        /**
         *-------------------------------------------------------------
         * Send Message as attachment
         *-------------------------------------------------------------
         */
        self::KEY_AS_ATTACHMENT => true,
        /**
         *-------------------------------------------------------------
         * Max Send Message field length (full message will be added
         *-------------------------------------------------------------
         */
        self::KEY_MAX_MESSAGE_LENGTH => 170,

        /**
         *-------------------------------------------------------------
         * Change Icon by log level
         *-------------------------------------------------------------
         */
        self::KEY_SET_ICON_BY_LOG_LEVEL => true,

        /**
         *-------------------------------------------------------------
         * Change Icon by log level
         *-------------------------------------------------------------
         */
        self::KEY_ADD_LOGGER_TO_MESSAGE => true
    ];

    /**
     * @var array
     */
    protected static $_config;

    /**
     * Config constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        self::$_config = \array_merge(self::$_defaultConfig, $config);
    }

    /**
     * Get setting
     *
     * @param string $setting
     *
     * @return array|float|int|string
     */
    public function get(string $setting)
    {
        if (\array_key_exists($setting, self::$_config)) {
            return self::$_config[$setting];
        }

        throw new \InvalidArgumentException(
            'invalid setting key: ('.$setting.')'
        );
    }

    /**
     * Set
     *
     * @param string $setting
     * @param mixed  $value
     *
     * @return static
     */
    public function set(string $setting, $value): self
    {
        self::$_config[$setting] = $value;

        return $this;
    }

    /**
     * To Array
     *
     * @return array
     */
    public function toArray(): array
    {
        return self::$_config;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource.
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return (object) $this->toArray();
    }

    /**
     * Get Icon by log event
     *
     * @param \LoggerLoggingEvent $event
     *
     * @return string
     */
    public function getIconByLogEvent(\LoggerLoggingEvent $event): string
    {
        switch ($event->getLevel()->toInt()) {
            case \LoggerLevel::WARN:
                $icon = ':feelsgood:';

                break;
            case \LoggerLevel::ERROR:
                $icon = ':goberserk:';

                break;
            case \LoggerLevel::FATAL:
                $icon = ':rage4:';

                break;
            default:
                $icon = ':squirrel:';
        }

        return $icon;
    }
}
