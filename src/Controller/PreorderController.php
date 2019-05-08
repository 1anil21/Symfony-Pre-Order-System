<?php

namespace App\Controller;

use App\Component\Cart\CartFactory;
use App\Entity\Cart;
use App\Entity\PreOrder;
use App\Entity\PreOrderItem;
use App\Utils\FormValidation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PreorderController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CartFactory
     */
    private $cartFactory;

    /**
     * @var SmsController
     */
    private $smsController;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    public function __construct(CartFactory $cartFactory, EntityManagerInterface $entityManager, SmsController $smsController, ParameterBagInterface $params)
    {
        $this->cartFactory = $cartFactory;
        $this->entityManager = $entityManager;
        $this->smsController = $smsController;
        $this->params = $params;
    }

    /**
     * @Route("/preorder", name="preorder", methods={"POST"})
     */
    public function preorder(Request $request)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $customerName = $request->request->get('name');
        $customerSurname = $request->request->get('surname');
        $customerEmail = $request->request->get('email');
        $customerPhone = $request->request->get('phone');
        $submittedToken = $request->request->get('token');

        // Check whether csrf token is valid
        if (!$this->isCsrfTokenValid('preorder', $submittedToken)) {
            $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
            $response->setContent('Bad request!');
            return $response;
        }

        if (strlen($customerName) == 0 || strlen($customerSurname) == 0 || strlen($customerEmail) == 0 || strlen($customerPhone) == 0){
            $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
            $response->setContent('Please fill the form completely!');
            return $response;
        }

        // Check whether email is valid
        if (!FormValidation::isValidEmail($customerEmail)){
            $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
            $response->setContent('Email is not valid!');
            return $response;
        }

        // Check whether phone is valid
        if (!FormValidation::isValidPhone($customerPhone)){
            $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
            $response->setContent('Phone is not valid!');
            return $response;
        }

        // Get Current Cart
        $cart = $this->cartFactory->getCurrent();

        if (!$this->createPreorder($customerName, $customerSurname, $customerEmail, $customerPhone, $cart)){
            $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
            $response->setContent("An error occured!");
        }

        $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_OK);
        return $response;
    }

    /**
     * Creates preorder with given information from user and cart
     *
     * @param $customerName string
     * @param $customerSurname string
     * @param $customerEmail string
     * @param $customerPhone string
     * @param $cart Cart
     * @return bool
     */
    public function createPreorder($customerName, $customerSurname, $customerEmail, $customerPhone, $cart){

        try {
            // Create Pre Order
            $preorderNumber = substr(sha1(time()+rand()*1000),0,8);
            $preorder = new PreOrder();
            $preorder->setCustomerName($customerName);
            $preorder->setCustomerSurname($customerSurname);
            $preorder->setCustomerEmail($customerEmail);
            $preorder->setCustomerPhone($customerPhone);
            $preorder->setOrderNumber($preorderNumber);
            $preorder->setStatus("waitingApproval");
            $preorder->setTotalPrice($cart->getTotalPrice());

            $this->entityManager->persist($preorder);
            $this->entityManager->flush();

            // Get Pre Order ID
            $preorderId = $preorder->getId();

            // Create Pre Order Items
            foreach($cart->getItems() as $item){
                $preorderItem = new PreOrderItem();
                $preorderItem->setOrderId($preorderId);
                $preorderItem->setProductId($item->getProductId());
                $preorderItem->setName($item->getName());
                $preorderItem->setQuantity($item->getQuantity());
                $preorderItem->setPrice($item->getPrice());

                $this->entityManager->persist($preorderItem);
                $this->entityManager->flush();
            }

            // Send SMS
            $this->smsController->sendSms($customerPhone, "Your pre order is placed successfully.\nOrder Number: ".$preorderNumber . "\nTotal price: ".$cart->getTotalPrice());

        } catch (\Exception $exception){
            return false;
        }

        return $preorderId;
    }

    /**
     * Auto Reject expired Pre Orders (that have not confirmed in 24 hours)
     * @throws \Exception
     */
    public function autoRejectExpiredPreorders(){

        /**
         * @var $expiredPreorders PreOrder[]
         */
        $expiredPreorders = $this->entityManager->createQueryBuilder()
            ->select('po')
            ->from('App\Entity\PreOrder','po')
            ->where('po.status = :status')
            ->andWhere('po.created_at <= :date')
            ->setParameter('date', new \DateTime('-1 days'))
            ->setParameter('status', 'waitingApproval')
            ->getQuery()
            ->execute();

        foreach ($expiredPreorders as $expiredPreorder){
            $expiredPreorder->setStatus('autoRejected');
            $this->entityManager->flush();
            $this->smsController->sendSms($expiredPreorder->getCustomerPhone(), "Your pre order is rejected!\nOrder Number: " . $expiredPreorder->getOrderNumber());
        }
    }
}