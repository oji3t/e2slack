<?php

namespace ExceptionToSlack;

use Exception;
use Maknz\Slack\Client as Slack;

class Notification
{
    /**
     * PHP Exception Instance
     * @var Exception $exception
     */
    protected $exception = null;

    /**
     * default endpoint
     * @var string $endpoint
     */
    protected $endpoint = null;

    /**
     * default channel
     * @var string $channel
     */
    protected $channel = '#general';

    /**
     * default username
     * @var string $username
     */
    protected $username = 'Notification';

    /**
     * default icon
     * @var string $icon
     */
    protected $icon = ':shit:';

    /**
     * Instance new Notification
     *
     * @method __construct
     * @param  Exception   $e
     * @param  array       $config
     */
    public function __construct(Exception $e, $config)
    {
        $this->setException($e);
        $this->setConfig($config);
        $this->slack = new Slack($this->getEndpoint());
    }

    /**
     * get Instance of Exception
     * @method getException
     * @return Exception    $e
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * set Exception
     * @method setException
     * @param  Exception    $e
     * @return Notification
     */
    public function setException(Exception $e)
    {
        $this->exception = $e;
        return $this;
    }

    /**
     * Get configuration array
     * @method getConfig
     * @return array
     */
    public function getConfig()
    {
        return [
            'endpoint' => $this->endpoint,
            'channel' => $this->channel,
            'username' => $this->username,
            'icon' => $this->icon,
        ];
    }

    /**
     * set configuration
     * @method setConfig
     * @param  array    $config
     */
    public function setConfig($config)
    {
        if (isset($config['endpoint']) && is_string($config['endpoint'])) $this->setEndpoint($config['endpoint']);
        if (isset($config['channel']) && is_string($config['channel'])) $this->setChannel($config['channel']);
        if (isset($config['username']) && is_string($config['username'])) $this->setUsername($config['username']);
        if (isset($config['icon']) && is_string($config['icon'])) $this->setIcon($config['icon']);

        return $this;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Sending message to slack
     *
     * @method send
     * @return Client
     */
    public function send()
    {
        $message = $this->parseException();
        $endpoint = $this->getEndpoint();
        $channel = $this->getChannel();
        $username = $this->getUsername();
        $icon = $this->getIcon();
        $attach = [
            'fallback' => $message,
            'text' => $message,
            'color' => 'danger',
            'mrkdwn_in' => ['text']
        ];

        try {
            $this->slack
                ->to($channel)
                ->from($username)
                ->withIcon($icon)
                ->enableMarkdown()
                ->attach($attach)
                ->send();

        } catch (Exception $e) {
            // TODO: Exception Handling
            return false;
        }

        return $this;
    }

    /**
     * Sending message to slack staticly
     *
     * @method sendTo
     * @param  Exception        $e
     * @param  array            $config
     * @return boolean
     */
    public static function sendTo(Exception $e, $config)
    {
        try {
            $notification = new Notification($e, $config);
            $notification->send();
            return $notification;
        } catch (Exception $e) {
            return false;
        }
    }

    public function parseException()
    {
        $exception = $this->exception;
        $message = 'Error Happened!'."\n";
        $parser = '* * * * * * * * * * * * * * * * *';

        if ($exception instanceof Exception) {
            $message .= $parser."\n";
            $message .= '*Message*: '.$exception->getMessage()."\n";

            if ($this->isHttpRequest()) {
                $message .= $parser."\n";
                $message .= '*Request*: '.$this->getRequest()."\n";
                $message .= $parser."\n";
                $message .= '*Client IP*: '.$_SERVER['REMOTE_ADDR']."\n";
                $message .= $parser."\n";
                $message .= '*Client User Agent*: '.$_SERVER['HTTP_USER_AGENT']."\n";
                $message .= $parser."\n";
                $message .= '*File*: '.$exception->getFile().' at '.$exception->getLine()."\n";
                $message .= $parser."\n";
            }
        }

        return $message;
    }

    public function getRequest()
    {
        return $_SERVER['REQUEST_METHOD'].' '.(empty($_SERVER["HTTPS"]) ? "http://" : "https://").$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
    }

    public function isHttpRequest() {
        return isset($_SERVER['REQUEST_METHOD'])
            && isset($_SERVER['HTTP_HOST'])
            && isset($_SERVER['REQUEST_URI'])
            && isset($_SERVER['REMOTE_ADDR'])
            && isset($_SERVER['HTTP_USER_AGENT']);
    }

}
