<?php

namespace App\Controller;

use App\Entity\PreOrder;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Twilio\TwiML\Voice\Sms;

class AdminController extends EasyAdminController
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var SmsController
     */
    private $smsController;

    /**
     * UserController constructor.
     *
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, SmsController $smsController)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->smsController = $smsController;
    }

    public function persistEntity($entity)
    {
        $this->encodePassword($entity);
        parent::persistEntity($entity);
    }

    public function updateEntity($entity)
    {
        $this->encodePassword($entity);
        parent::updateEntity($entity);
    }

    public function encodePassword($user)
    {
        if (!$user instanceof User) {
            return;
        }

        $user->setPassword(
            $this->passwordEncoder->encodePassword($user, $user->getPassword())
        );
    }

    public function confirmAction()
    {
        // Get Pre Order Id
        $preorderId = $this->request->query->get('id');

        /**
         * @var PreOrder $preorder
         */
        $preorder = $this->em->getRepository(PreOrder::class)->find($preorderId);

        // Set Pre Order Status To Approved
        $preorder->setStatus('approved');

        // Save Pre Order
        $this->em->flush();

        // Send SMS
        $this->smsController->sendSms($preorder->getCustomerPhone(), "Your pre order is approved.\nOrder Number: ".$preorder->getOrderNumber() . "\nTotal price: ".$preorder->getTotalPrice());

        return $this->redirectToRoute('easyadmin', array(
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ));
    }
}