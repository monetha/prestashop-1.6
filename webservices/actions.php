<?php

require '../vendor/autoload.php';

use Monetha\Response\Exception\ValidationException;
use Monetha\PS16\Adapter\WebHookAdapter;
use Monetha\PS16\Adapter\ConfigAdapter;

$currentDirectory = str_replace(
    'modules/monethagateway/webservices/',
    '',
    dirname($_SERVER['SCRIPT_FILENAME']) . "/"
);

require_once $currentDirectory . 'config' . DIRECTORY_SEPARATOR . 'config.inc.php';
require_once $currentDirectory . 'init.php';
header('Content-type:application/json;charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new ValidationException('Request method not supported', 405);
    }

    // Get body from post and signature from headers
    $bodyString = file_get_contents('php://input');
    $body = json_decode($bodyString);

    // Check if order exist
    $order = null;

    // Monetha external_order_id is actually cart id due to Prestashop itself
    $orderId = Order::getOrderByCartId((int)($body->payload->external_order_id));

    if (!empty($orderId)) {
        $order = new Order($orderId);
    }

    if (!$order || empty($order->id_cart)) {
        throw new ValidationException('Order not found', 404);
    }

    $webhookAdapter = new WebHookAdapter($order);

    $configAdapter = new ConfigAdapter(true);
    $signature = !empty($_SERVER['HTTP_MTH_SIGNATURE']) ? $_SERVER['HTTP_MTH_SIGNATURE'] : ''; #getallheaders()['mth-signature'];

    $result = $webhookAdapter->processWebHook($configAdapter, $bodyString, $signature);

    if (!$result) {
        throw new ValidationException('Bad request', 400);
    }

} catch (\Exception $e) {
    http_response_code($e->getCode());

    echo json_encode([
        'status' => $e->getCode(),
        'message' => $e->getMessage(),
    ]);

    return;
}

echo json_encode($result);