<?php

/*
 *  Made by AMGewka
 *  https://github.com/AMGewka
 *
 *  License: MIT
 *
 *  Store module
 */
require_once(ROOT_PATH . '/modules/Store/classes/StoreConfig.php');
$lava_language = new Language(ROOT_PATH . '/modules/Store/gateways/LAVA/language', LANGUAGE);
$page_title = $lava_language->get('gateways', 'lava');

if (Input::exists()) {
	if (Token::check()) {
		if (isset($_POST['shopid_key']) && isset($_POST['secret1_key']) && isset($_POST['secret2_key']) && strlen($_POST['shopid_key']) && strlen($_POST['secret1_key']) && strlen($_POST['secret2_key'])) {
			StoreConfig::set('LAVA.shopid_key', $_POST['shopid_key']);
			StoreConfig::set('LAVA.secret1_key', $_POST['secret1_key']);
			StoreConfig::set('LAVA.secret2_key', $_POST['secret2_key']);
		}

        // Is this gateway enabled
		if (isset($_POST['enable']) && $_POST['enable'] == 'on') $enabled = 1;
		else $enabled = 0;

		DB::getInstance()->update('store_gateways', $gateway->getId(), ['enabled' => $enabled]);

		Session::flash('gateways_success', $language->get('admin', 'successfully_updated'));
	} else $errors = [$language->get('general', 'invalid_token')];
}

$smarty->assign(
	[
		'SETTINGS_TEMPLATE' => ROOT_PATH . '/modules/Store/gateways/LAVA/gateway_settings/settings.tpl',
		'ENABLE_VALUE' => ((isset($enabled)) ? $enabled : $gateway->isEnabled()),
		'SHOP_ID_VALUE' => ((isset($_POST['shopid_key']) && $_POST['shopid_key']) ? Output::getClean(Input::get('shopid_key')) : StoreConfig::get('LAVA.shopid_key')),
		'SHOP_API_KEY_VALUE' => ((isset($_POST['secret1_key']) && $_POST['secret1_key']) ? Output::getClean(Input::get('secret1_key')) : StoreConfig::get('LAVA.secret1_key')),
		'SHOP_API_KEY_2_VALUE' => ((isset($_POST['secret2_key']) && $_POST['secret2_key']) ? Output::getClean(Input::get('secret2_key')) : StoreConfig::get('LAVA.secret2_key')),
		'SHOP_ID' => $lava_language->get('shopid'),
		'SHOP_KEY1' => $lava_language->get('key1'),
		'SHOP_KEY2' => $lava_language->get('key2'),
		'ENABLE_GATEWAY' => $lava_language->get('enablegateway'),
		'GATEWAY_NAME' => $lava_language->get('gatewayname'),
		'BANK_CARD' => $lava_language->get('bankcard'),
		'ONLINE_PAYMENTS' => $lava_language->get('onlinepay'),
		'GATEWAY_LINK' => $lava_language->get('gatewaylink'),
		'GATEWAY_TESTED' => $lava_language->get('gatewaytest'),
		'ALERT_URL' => $lava_language->get('alerturl'),
		'SUCCESS_URL' => $lava_language->get('sucurl'),
		'PINGBACK_URL' => rtrim(URL::getSelfURL(), '/') . URL::build('/store/listener', 'gateway=LAVA'),
		'SUCC_URL' => rtrim(URL::getSelfURL(), '/') . URL::build('/store/checkout', 'do=complete'),
		'FAILED_URL' => $lava_language->get('failurl'),
		'ACC_CUR' => $lava_language->get('acc_currency'),
		'WARINFO1' => $lava_language->get('warinfo1'),
		'WARINFO2' => $lava_language->get('warinfo2')
	]
);
