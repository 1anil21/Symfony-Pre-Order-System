<?php

namespace App\Tests\Sms;

use App\Controller\SmsController;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twilio\Rest\Api\V2010\Account\MessageInstance;

class SmsTest extends KernelTestCase
{
    /**
     * @var SmsController
     */
    private $smsController;

    protected function setUp()
    {
        self::bootKernel();
        $this->smsController = self::$container->get('App\Controller\SmsController');
    }

    public function testSmsSender()
    {
        /**
         * @var $message MessageInstance
         */
        $message = $this->smsController->sendSms("+905558173540", "Test SMS");

        $messageResponse = $message->toArray();

        $this->assertEquals($messageResponse["errorCode"], null);
    }
}