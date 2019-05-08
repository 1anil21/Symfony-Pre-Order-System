<?php

namespace App\Controller;

use App\Component\Cart\CartFactory;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CartController extends AbstractController
{
    /**
     * @var CartFactory
     */
    private $cartFactory;

    /*
     * @var Serializer
     */
    private $serializer;

    public function __construct(CartFactory $cartFactory)
    {
        $this->cartFactory = $cartFactory;
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }

    /**
     * @Route("/cart", name="cart", methods={"GET"})
     */
    public function listCart()
    {
        $cart = $this->cartFactory->getCurrent();
        $cartData = $this->serializer->serialize($cart, 'json');

        $response = new Response();
        $response->setContent($cartData);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/cart/add", name="cart.addItem", methods={"POST"})
     */
    public function addItem(Request $request)
    {
        $productId = intval($request->request->get('productId'));
        $quantity = intval($request->request->get('quantity'));

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $entityManager = $this->getDoctrine()->getManager();
        /* @var $product Product */
        $product = $entityManager->getRepository(Product::class)->find($productId);

        if (!$product) {
            $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
            return $response;
        }

        if ($quantity < 0) {
            $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
            return $response;
        }

        $this->cartFactory->addItem($product, $quantity);

        return $this->listCart();
    }

    /**
     * @Route("/cart/remove", name="cart.removeItem", methods={"DELETE"})
     */
    public function removeItem(Request $request)
    {

        $productId = intval($request->request->get('productId'));

        $entityManager = $this->getDoctrine()->getManager();
        $product = $entityManager->getRepository(Product::class)->find($productId);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        if (!$product) {
            $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
            return $response;
        }

        if (!$this->cartFactory->removeItem($productId)) {
            $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
            return $response;
        }

        return $this->listCart();
    }

    /**
     * @Route("/cart/update", name="cart.updateItem", methods={"PUT"})
     */
    public function updateItem(Request $request)
    {
        $productId = intval($request->request->get('productId'));
        $quantity = intval($request->request->get('quantity'));

        $entityManager = $this->getDoctrine()->getManager();
        $product = $entityManager->getRepository(Product::class)->find($productId);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        if (!$product) {
            $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
            return $response;
        }

        if (!is_int($quantity) || $quantity < 0) {
            $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
            return $response;
        }

        if ($quantity > 0) {
            $this->cartFactory->updateItem($productId, $quantity);
        } else {
            $this->cartFactory->removeItem($productId);
        }

        return $this->listCart();
    }

    /**
     * @Route("/cart/clear", name="cart.clear", methods={"POST"})
     */
    public function clear()
    {
        $this->cartFactory->clear();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_NO_CONTENT);

        return $response;
    }
}