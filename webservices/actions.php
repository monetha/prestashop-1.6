
<?php

require_once(__DIR__ . './../Services/GatewayService.php');
require_once(__DIR__ . './../Consts/Resource.php');
require_once(__DIR__ . './../Consts/EventType.php');

use Monetha\Services\GatewayService;
use Monetha\Consts\Resource;
use Monetha\Consts\EventType;

$currentDirectory = str_replace(
    'modules/monethagateway/webservices/',
    '',
    dirname($_SERVER['SCRIPT_FILENAME']) . "/"
);
$sep = DIRECTORY_SEPARATOR;
require_once $currentDirectory . 'config' . $sep . 'config.inc.php';
require_once $currentDirectory . 'init.php';
header('Content-type:application/json;charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Get body from post and signature from headers
        $body = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_MTH_SIGNATURE']; #getallheaders()['mth-signature'];
        if (json_decode($body)->event == EventType::PING) {
            handleResponse(200, 'Shop healthy');
        }

        $conf = json_decode(\Configuration::get('monethagateway'));

        // Validate plugin configuration
        $gatewayService = new GatewayService($conf->merchantSecret, $conf->monethaApiKey, $conf->testMode);
        if (!$gatewayService->configurationIsValid()) {
            handleResponse(404, 'Plugin configuration not valid');
        }

        // Validate body with merchant key
        if ($gatewayService->validateSignature($signature, $body)) {

            // Check if order exist
            $order = null;
            $orderId = Order::getOrderByCartId((int)(json_decode($body)->payload->external_order_id));

            if (!empty($orderId)) {
                $order = new Order($orderId);
            }

            if (empty($order->id_cart)) {
                handleResponse('401', 'Order not found');
            }

            // Process finalized or cancelled action
            $gatewayService->processAction($order, json_decode($body));
        } else {
            handleResponse(401, 'Bad signature');
        }
    } catch (\Exception $ex) {
        handleResponse(400, $ex->getMessage());
    }
} else {
    handleResponse(405, 'Request not supported');
}

function handleResponse($status, $message)
{
    http_response_code($status);
    echo json_encode([
        'status' => $status,
        'message' => $message
    ]);
    exit;
}
