<?php

namespace App\Entity;

use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class Cart implements DenormalizableInterface
{
    /**
     * @var $items CartItem[]
     */
    private $items;

    /*
     * @var float
     */
    private $totalPrice;

    public function __construct()
    {
        $this->items = [];
        $this->totalPrice = 0;
    }

    /*
     * @var $items CartItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    public function setItems(array $items)
    {
        $this->items = $items;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(?float $totalPrice)
    {
        $this->totalPrice = $totalPrice;
    }

    public function addItem(CartItem $cartItem){
        array_push($this->items, $cartItem);
    }

    public function removeItem(int $index){
        array_splice($this->items, $index, 1);
    }

    public function updateItem(int $index, $quantity){
        $this->items[$index]->setQuantity($quantity);
    }

    /**
     * Denormalizes the object back from an array of scalars|arrays.
     *
     * It is important to understand that the denormalize() call should denormalize
     * recursively all child objects of the implementor.
     *
     * @param DenormalizerInterface $denormalizer The denormalizer is given so that you
     *                                                  can use it to denormalize objects contained within this object
     * @param array|string|int|float|bool $data The data from which to re-create the object
     * @param string|null $format The format is optionally given to be able to denormalize
     *                                                  differently based on different input formats
     * @param array $context Options for denormalizing
     *
     * @return object
     */
    public function denormalize(DenormalizerInterface $denormalizer, $data, $format = null, array $context = [])
    {
        $data = json_decode($data);

        $obj = new static();
        if (isset($data->totalPrice)) {
            $obj->setTotalPrice($data->totalPrice);
        }

        if (isset($data->items) && is_array($data->items)) {
            $items = array();
            foreach ($data->items as $item) {
                $items[] = $denormalizer->denormalize($item, CartItem::class, $format, $context);
            }

            $obj->setItems($items);
        }

        return $obj;
    }
}
