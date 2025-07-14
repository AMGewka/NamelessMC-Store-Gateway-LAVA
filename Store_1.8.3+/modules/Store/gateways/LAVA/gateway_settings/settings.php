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
$LAVA_language = new Language(ROOT_PATH . '/modules/Store/gateways/LAVA/language', LANGUAGE);

/*
 ========================== Проверка и сохранение полей настроек ==========================
*/

$LAVA_keys = [
    'LAVA.shopid_key',
    'LAVA.secret1_key',
    'LAVA.secret2_key',
    'LAVA.desc'
];

foreach ($LAVA_keys as $key) {
    if (StoreConfig::get($key) === null) {
        StoreConfig::set($key, '');
    }
}

if (Input::exists()) {
	if (Token::check()) {
		if (isset($_POST['shopid_key'])
		&& isset($_POST['secret1_key'])
		&& isset($_POST['secret2_key'])
		&& isset($_POST['desc'])
		&& strlen($_POST['shopid_key'])
		&& strlen($_POST['secret1_key'])
		&& strlen($_POST['secret2_key'])
		&& strlen($_POST['desc'])) {
			StoreConfig::set('LAVA.shopid_key', $_POST['shopid_key']);
			StoreConfig::set('LAVA.secret1_key', $_POST['secret1_key']);
			StoreConfig::set('LAVA.secret2_key', $_POST['secret2_key']);
			StoreConfig::set('LAVA.secret2_key', $_POST['secret2_key']);
			StoreConfig::set('LAVA.desc', $_POST['desc']);
		}

		foreach ($fields as $post_key => $store_key) {
            if (Input::get($post_key) !== null) {
                StoreConfig::set($store_key, Input::get($post_key));
            }
        }

        if (isset($_POST['enable']) && $_POST['enable'] == 'on') $enabled = 1;
		else $enabled = 0;
        DB::getInstance()->update('store_gateways', $gateway->getId(), ['enabled' => $enabled]);

        if (isset($_POST['debug']) && $_POST['debug'] == 'on') $debug = 1;
            else $debug = 0;
            StoreConfig::set('LAVA.debug', $debug);
            ErrorHandler::logWarning($LAVA_language->get("general.debug_informer") . ($debug ? $LAVA_language->get("general.activated") : $LAVA_language->get("general.deactivated")));


		Session::flash('gateways_success', $LAVA_language->get('general.saved'));
	} else $errors = [$LAVA_language->get('general.wrong_form')];
}

$template->getEngine()->addVariables([
		'SETTINGS_TEMPLATE' => ROOT_PATH . '/modules/Store/gateways/LAVA/gateway_settings/settings.tpl',
		'ENABLE_VALUE' => ((isset($enabled)) ? $enabled : $gateway->isEnabled()),
		'SHOP_ID_VALUE' => ((isset($_POST['shopid_key']) && $_POST['shopid_key']) ? Output::getClean(Input::get('shopid_key')) : StoreConfig::get('LAVA.shopid_key')),
		'SHOP_API_KEY_VALUE' => ((isset($_POST['secret1_key']) && $_POST['secret1_key']) ? Output::getClean(Input::get('secret1_key')) : StoreConfig::get('LAVA.secret1_key')),
		'SHOP_API_KEY_2_VALUE' => ((isset($_POST['secret2_key']) && $_POST['secret2_key']) ? Output::getClean(Input::get('secret2_key')) : StoreConfig::get('LAVA.secret2_key')),
		'PINGBACK_URL' => rtrim(URL::getSelfURL(), '/') . URL::build('/store/listener', 'gateway=LAVA'),
		'SUCC_URL' => rtrim(URL::getSelfURL(), '/') . URL::build('/store/checkout', 'do=complete')
]);
