<?php

namespace Tests\ExceptionToSlack\Notification;

use Exception;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\AssertionFailedError;

class HelperTest extends TestCase{

    public function __construct()
    {
        $this->dotenv = new Dotenv(__DIR__.'/../..');
        $this->dotenv->load();
    }

    public function testMakeConfig()
    {
        $endpoint = getenv('SLACK_ENDPOINT');
        $channel = getenv('SLACK_CHANNEL');
        $username = getenv('SLACK_USERNAME');
        $icon = getenv('SLACK_ICON');

        $config = compact('endpoint', 'channel', 'username', 'icon');

        $this->assertNotEmpty($config);
        $this->assertCount(4, $config);

        return $config;
    }

    /**
     * @depends testMakeConfig
     */
    public function testSend($config)
    {
        try {
            throw new Exception("TestException(this is test greened)", 1);
        } catch(AssertionFailedError $e){
            echo "\n".'Error: PHPUnit Exception: '.$e->getMessage()."\n";
        } catch (Exception $e) {
            $notification = e2slack($e, $config);
            $this->assertTrue($notification !== false);
            echo "\n".'Success: Send exception: '.$e->getMessage()."\n";
        }
    }
}
