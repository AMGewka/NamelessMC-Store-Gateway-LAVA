<?php
/**
 * LAVA_Gateway class
 *
 * @package Modules\Store
 * @author AMGewka
 * @version 1.8.2
 * @license MIT
 */
class LAVA_Gateway extends GatewayBase
{
	public function __construct()
	{
		$name = 'LAVA';
		$author = '<a href="https://github.com/AMGewka" target="_blank">AMGewka</a>';
		$gateway_version = '1.8.2';
		$store_version = '1.7.1';
		$settings = ROOT_PATH . '/modules/Store/gateways/LAVA/gateway_settings/settings.php';

		parent::__construct($name, $author, $gateway_version, $store_version, $settings);
	}

	public function onCheckoutPageLoad(TemplateBase $template, Customer $customer) : void {}

	public function processOrder(Order $order) : void
	{
		$secret = StoreConfig::get('LAVA.secret1_key');
		$payment_id = $order->data()->id;

		$data = [
			'comment' => 'Buying products: ' . $order->getDescription() . ' on ' . $order->customer()->getUser()->data()->username . ' account',
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

		$sign = hash_hmac('sha256', json_encode($data), StoreConfig::get('LAVA.secret1_key'));
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
		if (isset($decoded['url'])) {
			header("Location: " . $decoded['url']);
			exit;
		}

		return false;
	}

	public function handleListener() : void
	{
		$receivedSignature = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

		$postData = file_get_contents('php://input');
		$receivedData = json_decode($postData, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			die("Error: Invalid JSON data");
		}
		$allowedIps = array('62.122.172.72', '62.122.173.38', '91.227.144.73');

		if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIps)) {
			die("Error: Untrusted IP address");
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
		}
	}
}
$gateway = new LAVA_Gateway();