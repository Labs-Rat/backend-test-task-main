<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\View;

use Raketa\BackendTestTask\DTO\Cart;
use Raketa\BackendTestTask\Repository\ProductRepository;

readonly class CartView
{
    public function __construct(
        private ProductRepository $productRepository
    )
    {
    }

    public function toArray(Cart $cart): array
    {
        $customer = $cart->getCustomer();
        $data = [
            'uuid'           => $cart->getUuid(),
            'customer'       => [
                'id'    => $customer->getId(),
                'name'  => implode(' ', [
                    $customer->getLastName(),
                    $customer->getFirstName(),
                    $customer->getMiddleName(),
                ]),
                'email' => $customer->getEmail(),
            ],
            'payment_method' => $cart->getPaymentMethod(),
            'items'          => [],
            'total'          => 0,
        ];

        $totalCost = 0;
        foreach ($cart->getItems() as $item) {
            $itemCost = $item->getPrice() * $item->getQuantity();
            $totalCost += $itemCost;
            $product = $this->productRepository->getByUuid($item->getUuid());

            $data['items'][] = [
                'uuid'     => $item->getUuid(),
                'price'    => $item->getPrice(),
                'total'    => $itemCost,
                'quantity' => $item->getQuantity(),
                'product'  => [
                    'id'        => $product->getId(),
                    'uuid'      => $product->getUuid(),
                    'name'      => $product->getName(),
                    'thumbnail' => $product->getThumbnail(),
                    'price'     => $product->getPrice(),
                ],
            ];
        }

        $data['total'] = $totalCost;

        return $data;
    }
}
