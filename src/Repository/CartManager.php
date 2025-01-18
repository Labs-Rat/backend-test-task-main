<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Repository;

use Exception;
use Psr\Log\LoggerInterface;
use Raketa\BackendTestTask\DTO\Cart;
use Raketa\BackendTestTask\Infrastructure\ConnectorFacade;

class CartManager extends ConnectorFacade
{
    public LoggerInterface $logger;

    public function __construct($host, $port, $password)
    {
        parent::__construct($host, $port, $password, 1);
        parent::build();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function saveCart(Cart $cart): void
    {
        try {
            $this->connector->set(session_id(), $cart);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @return ?Cart
     */
    public function getCart(): ?Cart
    {
        try {
            return $this->connector->get(session_id());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return new Cart(session_id(), []);
    }
}
