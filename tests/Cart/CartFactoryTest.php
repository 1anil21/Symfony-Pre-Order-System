<?php

namespace App\Tests\Cart;

use App\Component\Cart\CartFactory;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CartFactoryTest extends KernelTestCase
{
    /**
     * @var CartFactory
     */
    private $cartFactory;

    protected function setUp()
    {
        self::bootKernel();
        $this->cartFactory = self::$container->get('App\Component\Cart\CartFactory');
    }

    public function testAddToCart()
    {
        $product = new Product();
        $product->setId(1);
        $product->setName("Product 1");
        $product->setPrice(100);

        $this->cartFactory->addItem($product, 1);

        $cartItems = $this->cartFactory->getCurrent()->getItems();
        $cartItem = array_pop($cartItems);

        $this->assertEquals($cartItem->getProductId(), $product->getId());
        $this->assertEquals($cartItem->getName(), $product->getName());
        $this->assertEquals($cartItem->getPrice(), $product->getPrice());
        $this->assertEquals($cartItem->getQuantity(), 1);
    }

    public function testRemoveFromCart()
    {
        $product = new Product();
        $product->setId(1);
        $product->setName("Product 1");
        $product->setPrice(100);

        $this->cartFactory->addItem($product, 1);

        $this->cartFactory->removeItem(1);
        $cartItems = $this->cartFactory->getCurrent()->getItems();
        $this->assertEquals(count($cartItems), 0);
    }

    public function testUpdateQuantity()
    {
        $product = new Product();
        $product->setId(1);
        $product->setName("Product 1");
        $product->setPrice(100);

        $this->cartFactory->addItem($product, 1);

        // Set quantity to 3
        $this->cartFactory->updateItem(1, 3);

        $cartItems = $this->cartFactory->getCurrent()->getItems();
        $cartItem = array_pop($cartItems);

        $this->assertEquals($cartItem->getQuantity(), 3);
    }

    public function testClearCart(){
        $product = new Product();
        $product->setId(1);
        $product->setName("Product 1");
        $product->setPrice(100);

        $product2 = new Product();
        $product2->setId(2);
        $product2->setName("Product 2");
        $product2->setPrice(200);

        $this->cartFactory->addItem($product, 1);
        $this->cartFactory->addItem($product2, 2);

        // Clear Cart
        $this->cartFactory->clear();

        $cartItems = $this->cartFactory->getCurrent()->getItems();
        $this->assertEquals(count($cartItems), 0);
    }

    public function testCartIsEmpty(){
        $product = new Product();
        $product->setId(1);
        $product->setName("Product 1");
        $product->setPrice(100);

        $this->cartFactory->addItem($product, 1);

        $this->assertEquals($this->cartFactory->isEmpty(), false);
    }
}