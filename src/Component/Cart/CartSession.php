<?php

namespace App\Component\Cart;

use App\Entity\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CartSession
{
    private const CART_SESSION_KEY = 'preorder_cart';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SessionInterface
     */
    private $session;

    /*
     * @var Serializer
     */
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SessionInterface $session)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->serializer = new Serializer([new GetSetMethodNormalizer(), new ObjectNormalizer()], [new JsonEncoder()]);
    }

    public function has(): bool
    {
        return $this->session->has(self::CART_SESSION_KEY);
    }

    public function get(): Cart
    {
        $cartJson = $this->session->get(self::CART_SESSION_KEY);
        return (new Cart())->denormalize($this->serializer, $cartJson);
    }

    public function set(Cart $cart): void
    {
        $cartJson = $this->serializer->serialize($cart, 'json');
        $this->session->set(self::CART_SESSION_KEY, $cartJson);
    }

    public function remove(): void
    {
        $this->session->remove(self::CART_SESSION_KEY);
    }

    public function getCurrent(): ?Cart
    {
        if ($this->has()) {
            return $this->get();
        }

        return null;
    }

}