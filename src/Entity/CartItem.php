<?php

namespace App\Entity;

class CartItem
{
    /*
     * @var int
     */
    private $productId;

    /*
     * @var string
     */
    private $name;

    /*
     * @var int
     */
    private $quantity;

    /*
     * @var float
     */
    private $price;

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(?int $productId)
    {
        $this->productId = $productId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name)
    {
        $this->name = $name;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity)
    {
        $this->quantity = $quantity;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price)
    {
        $this->price = $price;
    }
}
