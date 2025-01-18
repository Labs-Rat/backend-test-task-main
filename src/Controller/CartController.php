<?php

namespace Raketa\BackendTestTask\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Raketa\BackendTestTask\DTO\CartItem;
use Raketa\BackendTestTask\Repository\CartManager;
use Raketa\BackendTestTask\Repository\ProductRepository;
use Raketa\BackendTestTask\View\CartView;
use Ramsey\Uuid\Uuid;

readonly class CartController
{
    public function __construct(
        private ProductRepository $productRepository,
        private CartView          $cartView,
        private CartManager       $cartManager,
    )
    {
    }

    public function add(RequestInterface $request): ResponseInterface
    {
        $rawRequest = json_decode($request->getBody()->getContents(), true);
        $product = $this->productRepository->getByUuid($rawRequest['productUuid']);

        $response = new JsonResponse();

        if (!$product) {
            $response->getBody()->write(
                json_encode(
                    ['message' => 'Product not found.'],
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                )
            );

            return $response
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withStatus(404);
        }

        $cart = $this->cartManager->getCart();

        if (!$cart) {
            $response->getBody()->write(
                json_encode(
                    ['message' => 'Cart not found.'],
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                )
            );

            return $response
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withStatus(404);
        }

        $cart->addItem(new CartItem(
            Uuid::uuid4()->toString(),
            $product->getUuid(),
            $product->getPrice(),
            $rawRequest['quantity'],
        ));

        $response->getBody()->write(
            json_encode(
                [
                    'status' => 'success',
                    'cart'   => $this->cartView->toArray($cart)
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus(200);
    }

    public function get(RequestInterface $request): ResponseInterface
    {
        $response = new JsonResponse();
        $cart = $this->cartManager->getCart();

        if (!$cart) {
            $response->getBody()->write(
                json_encode(
                    ['message' => 'Cart not found'],
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                )
            );

            return $response
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withStatus(404);
        }

        $response->getBody()->write(
            json_encode(
                $this->cartView->toArray($cart),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus(200);
    }
}