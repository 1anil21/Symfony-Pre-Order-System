<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twilio\Rest\Client;

class SmsController extends AbstractController
{
    /**
     * @var Client
     */
    private $twilio;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    public function __construct(Client $twilio, ParameterBagInterface $params)
    {
        $this->twilio = $twilio;
        $this->params = $params;
    }

    // Send SMS
    public function sendSms($toNumber, $body)
    {
        try {
            $sender = $this->params->get('twilio_number');
//            $toNumber = $this->params->get('twilio_to_number');

            $message = $this->twilio->messages->create(
                $toNumber, // To Number
                array(
                    'from' => $sender, // From Number
                    'body' => $body
                )
            );

            return $message;
        } catch (\Exception $exception) {
            return false;
        }
    }
}