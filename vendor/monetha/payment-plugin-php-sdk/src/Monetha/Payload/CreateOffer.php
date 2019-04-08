<?php
/**
 * Created by PhpStorm.
 * User: hitrov
 * Date: 2019-03-21
 * Time: 14:52
 */

namespace Monetha\Payload;


use Monetha\Adapter\CallbackUrlInterface;
use Monetha\Adapter\OrderAdapterInterface;

class CreateOffer extends AbstractPayload
{
    /**
     * CreateOffer constructor.
     * @param OrderAdapterInterface $orderAdapter
     * @param $clientId
     */
    public function __construct(OrderAdapterInterface $orderAdapter, $clientId)
    {
        $payload = $this->prepareOfferBody($orderAdapter);

        $payload['deal']['client_id'] = $clientId;

        $this->setPayload($payload);
    }

    /**
     * @param OrderAdapterInterface $orderAdapter
     * @return array
     */
    private function prepareOfferBody(OrderAdapterInterface $orderAdapter)
    {
        $orderId = $orderAdapter->getCartId();
        $items = [];
        $cartItems = $orderAdapter->getItems();

        $itemsPrice = 0;
        foreach ($cartItems as $item) {
            $price = round($item->getPrice(), 2);
            $quantity = $item->getQtyOrdered();
            $li = [
                'name' => $item->getName(),
                'quantity' => (int) $quantity,
                'amount_fiat' => (float) $price,
            ];
            $itemsPrice += $price * $quantity;
            if($price > 0)
            {
                $items[] = $li;
            }
        }

        $itemsPrice = round($itemsPrice, 2);

        $grandTotal = round($orderAdapter->getGrandTotalAmount(), 2);

        // Add shipping and taxes
        $shipping = [
            'name' => 'Shipping and taxes',
            'quantity' => 1,
            'amount_fiat' => round($grandTotal - $itemsPrice, 2),
        ];

        if($shipping['amount_fiat'] > 0)
        {
            $items[] = $shipping;
        }

        $deal = array(
            'deal' => array(
                'amount_fiat' => round($grandTotal, 2),
                'currency_fiat' => $orderAdapter->getCurrencyCode(),
                'line_items' => $items
            ),
            'return_url' => $orderAdapter->getBaseUrl(),
            'callback_url' => $orderAdapter->getBaseUrl(),
            'external_order_id' => (string) $orderId,
        );

        if ($orderAdapter instanceof CallbackUrlInterface) {
            $deal['callback_url'] = $orderAdapter->getCallbackUrl();
        }

        return $deal;
    }
}