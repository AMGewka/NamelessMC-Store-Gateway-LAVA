<?php
/**
 * LAVA_Gateway class
 *
 * @package Modules\Store
 * @author AMGewka
 * @version 1.8.4
 * @license MIT
 */
class LAVA_Gateway extends GatewayBase
{
	public function __construct()
	{
		$name = 'LAVA';
		$author = '<a href="https://github.com/AMGewka" target="_blank">AMGewka</a>';
		$gateway_version = '1.8.4';
		$store_version = '1.8.3';
		$settings = ROOT_PATH . '/modules/Store/gateways/LAVA/gateway_settings/settings.php';

		parent::__construct($name, $author, $gateway_version, $store_version, $settings);
	}

	public function onCheckoutPageLoad(TemplateBase $template, Customer $customer): void {
            $LAVA_language = new Language(ROOT_PATH . '/modules/Store/gateways/LAVA/language', LANGUAGE);
            $this->setDisplayname($LAVA_language->get('general.gateway_displayname'));
        }

	public function processOrder(Order $order) : void
	{
		$LAVA_language = new Language(ROOT_PATH . '/modules/Store/gateways/LAVA/language', LANGUAGE);
		$secret = StoreConfig::get('LAVA.secret1_key');
		$description = StoreConfig::get('LAVA.desc') ?? $LAVA_language->get('general.default_description');
                if (empty($description)) {
                    $description = 'Purchase of products: {order} for account {username}';
                }
                $replacements = [
                    '{username}' => $order->customer()->getUser()->data()->username ?? '',
                    '{email}' => $order->customer()->getUser()->data()->email ?? '',
                    '{products}' => $order->getDescription() ?? '',
                    '{order_id}' => $order->data()->id ?? '',
                    '{amount}' => $order->getAmount()->getTotalCents() / 100 ?? '',
                    '{currency}' => Store::getCurrency() ?? '',
                    '{ip}' => $order->data()->ip ?? '',
                    '{date}' => date('d-m-Y H:i', $order->data()->created ?? time()),
                ];
        $desc = str_replace(array_keys($replacements), array_values($replacements), $description);

        /*
         ========================== Запрос ссылки на страницу оплаты ==========================
        */

		$data = [
			'comment' => $desc,
			'orderId' => $order->data()->id,
			'shopId' => StoreConfig::get('LAVA.shopid_key'),
			'sum' => $order->getAmount()->getTotalCents() / 100,
			'hookUrl'    => rtrim(URL::getSelfURL(), '/') . URL::build('/store/listener', 'gateway=LAVA'),
			'successUrl' => rtrim(URL::getSelfURL(), '/') . URL::build('/store/checkout', 'do=complete'),
			'failUrl'    => rtrim(URL::getSelfURL(), '/') . URL::build('/store/checkout'),
		];
		$sign = hash_hmac('sha256', json_encode($data), $secret);
		$headers = [
			"accept: application/json",
			"content-type: application/json",
			"Signature: {$sign}"
		];

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://api.lava.ru/business/invoice/create');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

		$response = curl_exec($ch);
		curl_close($ch);

		$responseData = json_decode($response, true);

		if ($responseData['status'] == 200) {
			header('Location: ' . $responseData['data']['url']);
		}
	}

	public function handleReturn() : bool
{
		$LAVA_language = new Language(ROOT_PATH . '/modules/Store/gateways/LAVA/language', LANGUAGE);
		$debug_mode = StoreConfig::get('LAVA.DebugMode_value') === true;

		if (isset($decoded['url'])) {
		if ($debug_mode) {
            ErrorHandler::logWarning($LAVA_language->get('logs.webhook_header'));
            ErrorHandler::logWarning($LAVA_language->get('logs_pay.new_link_created') . $decoded['url']);
            ErrorHandler::logWarning($LAVA_language->get('logs.webhook_footer'));
        }
			header("Location: " . $decoded['url']);
			exit;
		}

		return false;
	}

	public function handleListener() : void
	{
		$LAVA_language = new Language(ROOT_PATH . '/modules/Store/gateways/LAVA/language', LANGUAGE);
        $debug_mode = StoreConfig::get('LAVA.DebugMode_value') === true;
        $secret = StoreConfig::get('LAVA.secret1_key');

        $receivedSignature = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

		$postData = file_get_contents('php://input');

		/*
        ========================== Проверка вебхука и регистрация платежа ==========================
        */

        $receivedData = json_decode($postData, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
		    if ($debug_mode) {
                ErrorHandler::logWarning($LAVA_language->get('logs.webhook_header'));
                ErrorHandler::logWarning($LAVA_language->get('logs_pay_webhook_checker.wrong_json_format') . $postData);
                ErrorHandler::logWarning($LAVA_language->get('logs.webhook_footer'));
                }
			die($LAVA_language->get('logs.wrong_json_format'));
		}

		$LAVA_IP_list = array('62.122.173.38', '91.227.144.73', '31.133.222.20');
		if (!in_array($_SERVER['REMOTE_ADDR'], $LAVA_IP_list)) {
		    if ($debug_mode) {
            ErrorHandler::logWarning($LAVA_language->get('logs.webhook_header'));
            ErrorHandler::logWarning($LAVA_language->get('logs.service_ip') . $LAVA_IP_list);
            ErrorHandler::logWarning($LAVA_language->get('logs_pay_webhook_checker.ip_address_is_not_trusted') . $_SERVER['REMOTE_ADDR']);
            ErrorHandler::logWarning($LAVA_language->get('logs.webhook_footer'));
            }
			die($LAVA_language->get('logs.ip_address_is_not_trusted'));
		}

        $expectedSignature = hash_hmac('sha256', $postData, $secret);
        if (!hash_equals($expectedSignature, $receivedSignature)) {
            if ($debug_mode) {
                ErrorHandler::logWarning($LAVA_language->get('logs.webhook_header'));
                ErrorHandler::logWarning($LAVA_language->get('logs_pay_webhook_checker.wrong_sign'));
                ErrorHandler::logWarning($LAVA_language->get('logs.webhook_footer'));
                }
            die($LAVA_language->get('logs.wrong_sign'));
        }

		if ($receivedData['status'] === 'success') {
			$payment = new Payment($paymentId, 'transaction');
			$paymentData = [
				'order_id' => $receivedData['order_id'],
				'gateway_id' => $this->getId(),
				'transaction' => $receivedData['order_id'],
				'amount_cents' => Store::toCents($receivedData['amount']),
				'currency' => 'RUB',
				'fee_cents' => '0'
			];
			$payment->handlePaymentEvent(Payment::COMPLETED, $paymentData);
			 if ($debug_mode) {
                ErrorHandler::logWarning($LAVA_language->get('logs.webhook_header'));
                ErrorHandler::logWarning($LAVA_language->get('logs_pay_webhook_checker.payment_successful') . "#$PaymentID, $$OrderAmount");
                ErrorHandler::logWarning($LAVA_language->get('logs.webhook_footer'));
             }
		}
	}
}
$gateway = new LAVA_Gateway();