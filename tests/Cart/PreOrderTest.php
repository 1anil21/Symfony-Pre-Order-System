<?php

namespace App\Tests\Cart;

use App\Component\Cart\CartFactory;
use App\Controller\PreorderController;
use App\Entity\PreOrder;
use App\Entity\PreOrderItem;
use App\Entity\Product;
use App\Repository\PreOrderRepository;
use App\Repository\PreOrderItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CartFunctionalityTest extends KernelTestCase
{
    /**
     * @var PreorderController
     */
    private $preorderController;

    /**
     * @var PreOrderRepository
     */
    private $preorderRepository;

    /**
     * @var PreOrderItemRepository
     */
    private $preorderItemRepository;

    /**
     * @var CartFactory
     */
    private $cartFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    protected function setUp()
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->preorderRepository = $this->entityManager->getRepository(PreOrder::class);
        $this->preorderItemRepository = $this->entityManager->getRepository(PreOrderItem::class);
        $this->preorderController = self::$container->get('App\Controller\PreorderController');
        $this->cartFactory = self::$container->get('App\Component\Cart\CartFactory');
    }

    public function testCreatePreOrder()
    {
        // Create Order and Order Item
        $customerName = "Name";
        $customerSurname = "Surname";
        $customerEmail = "customer@customer.com";
        $customerPhone = "05551112233";
        $productId = 1;
        $productName = "Product 1";
        $productPrice = 100;
        $productQuantity = 2;

        $product = new Product();
        $product->setId($productId);
        $product->setName($productName);
        $product->setPrice($productPrice);

        $this->cartFactory->addItem($product, $productQuantity);
        $cart = $this->cartFactory->getCurrent();

        $preorderId = $this->preorderController->createPreorder($customerName, $customerSurname, $customerEmail, $customerPhone, $cart);

        // Generate fake object of pre order
        $time = new \DateTime();
        $fakePreorder = new PreOrder();
        $fakePreorder->setId($preorderId);
        $fakePreorder->setStatus("waitingApproval");
        $fakePreorder->setCustomerName($customerName);
        $fakePreorder->setCustomerSurname($customerSurname);
        $fakePreorder->setCustomerEmail($customerEmail);
        $fakePreorder->setCustomerPhone($customerPhone);
        $fakePreorder->setTotalPrice($productPrice * $productQuantity);
        $fakePreorder->setCreatedAt($time);
        $fakePreorder->setOrderNumber("");

        // Get real object of pre order
        $preorder = $this->preorderRepository->find($preorderId);
        $preorder->setCreatedAt($time);
        $preorder->setOrderNumber("");

        // Generate fake object of pre order
        $preorderItemId = 5;
        $fakePreorderItem = new PreOrderItem();
        $fakePreorderItem->setId($preorderItemId);
        $fakePreorderItem->setProductId($productId);
        $fakePreorderItem->setName($productName);
        $fakePreorderItem->setQuantity($productQuantity);
        $fakePreorderItem->setPrice($productPrice);
        $fakePreorderItem->setOrderId($preorderId);
        $fakePreorderItem->setCreatedAt($time);

        // Get real object of pre order item
        $preorderItem = $this->preorderItemRepository->findBy(['orderId' => $preorderId])[0];
        $preorderItem->setId($preorderItemId);
        $preorderItem->setCreatedAt($time);

        $this->assertEquals($fakePreorder, $preorder);
        $this->assertEquals($fakePreorderItem, $preorderItem);
    }

    public function testAutoRejectExpiredPreorders(){

        // Create Order
        $customerName = "Name";
        $customerSurname = "Surname";
        $customerEmail = "customer@customer.com";
        $customerPhone = "05551112233";
        $productId = 1;
        $productName = "Product 1";
        $productPrice = 100;
        $productQuantity = 2;

        $product = new Product();
        $product->setId($productId);
        $product->setName($productName);
        $product->setPrice($productPrice);

        $this->cartFactory->addItem($product, $productQuantity);
        $cart = $this->cartFactory->getCurrent();

        // Get created pre order id
        $preorderId = $this->preorderController->createPreorder($customerName, $customerSurname, $customerEmail, $customerPhone, $cart);

        // Get datetime of one day and one hour before from now
        $oneDayAndOneHourBeforeDateTime = (new \DateTime())->modify('-1 day')->modify('-1 hour');

        // Get real object of pre order and modify its creation date to one day and one hour before from now
        $preorder = $this->preorderRepository->find($preorderId);
        $preorder->setCreatedAt($oneDayAndOneHourBeforeDateTime);

        // Save its creation date to database
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Call auto rejection function
        try {
            $this->preorderController->autoRejectExpiredPreorders();
        } catch (\Exception $e) {
        }

        // Get pre order from database with new status
        $preorder = $this->preorderRepository->find($preorderId);

        $this->assertEquals($preorder->getStatus(), 'autoRejected');
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}