<?php

namespace App\Component\Cart;

use App\Component\Cart\CartSession;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class CartFactory
{
    /**
     * @var CartSession
     */
    private $cartSession;

    /**
     * @var Cart
     */
    private $cart;

    public function __construct(CartSession $cartSession)
    {
        $this->cartSession = $cartSession;
        $this->cart = $this->getCurrent();
    }

    public function getCurrent(): Cart
    {
        if ($this->cartSession->has()) {
            return $this->cartSession->get();
        }

        return new Cart();
    }

    /**
     * Add a product to the cart
     * If the product exists, increase its quantity
     *
     * @param Product $product
     * @param integer $quantity
     * @return void
     */
    public function addItem(Product $product, int $quantity): void
    {
        $indexOfProduct = $this->indexOfProduct($product->getId());

        if ($indexOfProduct === null) {
            $cartItem = new CartItem();
            $cartItem->setProductId($product->getId());
            $cartItem->setName($product->getName());
            $cartItem->setQuantity($quantity);
            $cartItem->setPrice($product->getPrice());

            $this->cart->addItem($cartItem);
        } else {
            $item = $this->cart->getItems()[$indexOfProduct];
            $item->setQuantity($item->getQuantity() + $quantity);
        }

        $this->updateTotalPrice();
        $this->cartSession->set($this->cart);
    }

    /**
     * Remove an item from cart
     *
     * @param int $productId
     * @return bool
     */
    public function removeItem(int $productId): ?bool
    {
        $indexOfProduct = $this->indexOfProduct($productId);

        if ($indexOfProduct === null){
            return false;
        }

        $this->cart->removeItem($indexOfProduct);
        $this->updateTotalPrice();
        $this->cartSession->set($this->cart);

        return true;
    }

    /**
     * Update the quantity of an item in cart.
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function updateItem(int $productId, int $quantity): ?bool
    {
        $indexOfProduct = $this->indexOfProduct($productId);

        if ($indexOfProduct === null){
            return false;
        }

        $this->cart->updateItem($indexOfProduct, $quantity);
        $this->updateTotalPrice();
        $this->cartSession->set($this->cart);

        return true;
    }

    /**
     * Get all products along with information needed on the cart listing
     *
     * @return array
     */
    public function items(): array
    {
        return $this->cart->getItems();
    }

    /**
     * Return key number of cartItem has product
     *
     * @param int $productId
     * @return int|null
     */
    public function indexOfProduct(int $productId): ?int
    {
        if ($this->cart){
            foreach ($this->cart->getItems() AS $key => $item) {
                if ($item->getProductId() === $productId) {
                    return $key;
                }
            }
        }

        return null;
    }

    /**
     * Remove all items from the basket
     */
    public function clear(): void
    {
        $this->cartSession->remove();
    }

    /**
     * Checking whether the basket is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return !$this->cart->getItems();
    }

    /**
     * Update Total Price of cart
     *
     * @return void
     */
    private function updateTotalPrice()
    {
        $totalPrice = 0;
        foreach ($this->cart->getItems() AS $item) {
            $totalPrice += $item->getPrice() * $item->getQuantity();
        }

        $this->cart->setTotalPrice($totalPrice);
    }
}