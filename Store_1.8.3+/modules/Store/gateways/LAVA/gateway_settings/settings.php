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

if (Input::exists()) {
	if (Token::check()) {
		if (isset($_POST['shopuuid_key'])
		&& isset($_POST['secret1_key'])
		&& isset($_POST['secret2_key'])
		&& isset($_POST['desc'])
		&& strlen($_POST['shopuuid_key'])
		&& strlen($_POST['secret1_key'])
		&& strlen($_POST['secret2_key'])
		&& strlen($_POST['desc'])) {
			StoreConfig::set('LAVA.shopuuid_key', $_POST['shopuuid_key']);
			StoreConfig::set('LAVA.secret1_key', $_POST['secret1_key']);
			StoreConfig::set('LAVA.secret2_key', $_POST['secret2_key']);
			StoreConfig::set('LAVA.desc', $_POST['desc']);
		}

        if (isset($_POST['enable']) && $_POST['enable'] == 'on') $enabled = 1;
		else $enabled = 0;
        DB::getInstance()->update('store_gateways', $gateway->getId(), ['enabled' => $enabled]);

        if (isset($_POST['debug']) && $_POST['debug'] == 'on') $debug = 1;
        else $debug = 0;
        StoreConfig::set('LAVA.debug', $debug);
        ErrorHandler::logWarning($LAVA_language->get("general.debug_informer") . ($debug ? $LAVA_language->get("general.activated") : $LAVA_language->get("general.deactivated")));

        Session::flash("gateways_success", $LAVA_language->get('general.saved'));
    } else {
        $errors = [$LAVA_language->get('general.wrong_form')];
    }
}

$template->getEngine()->addVariables([
		"SETTINGS_TEMPLATE" => ROOT_PATH . "/modules/Store/gateways/LAVA/gateway_settings/settings.tpl",
		"ENABLE_VALUE" => ((isset($enabled)) ? $enabled : $gateway->isEnabled()),
		"DEBUG_MODE_VALUE" => StoreConfig::get("LAVA.debug"),
		"SHOP_ID_VALUE" => isset($_POST["shopuuid_key"]) && $_POST["shopuuid_key"] ? Output::getClean(Input::get("shopuuid_key")) : StoreConfig::get("LAVA.shopuuid_key"),
		"SHOP_API_KEY_VALUE" => isset($_POST["secret1_key"]) && $_POST["secret1_key"] ? Output::getClean(Input::get("secret1_key")) : StoreConfig::get("LAVA.secret1_key"),
		"SHOP_API_KEY_2_VALUE" => isset($_POST["secret2_key"]) && $_POST["secret2_key"] ? Output::getClean(Input::get("secret2_key")) : StoreConfig::get("LAVA.secret2_key"),
		"DESCRIPTION_VALUE" => isset($_POST["desc"]) && $_POST["desc"] ? Output::getClean(Input::get("desc")) : StoreConfig::get("LAVA.desc"),
		"PINGBACK_URL" => rtrim(URL::getSelfURL(), '/') . URL::build('/store/listener', 'gateway=LAVA'),
		"SUCCESS_URL" => rtrim(URL::getSelfURL(), '/') . URL::build('/store/checkout', 'do=complete'),
		"GATEWAY_UUID" => $LAVA_language->get("general.shop_uuid"),
		"GATEWAY_UUID_FIELD" => $LAVA_language->get("general.shop_uuid_field"),
		"GATEWAY_SECRET1" => $LAVA_language->get("general.shop_secret1"),
		"GATEWAY_SECRET1_FIELD" => $LAVA_language->get("general.shop_secret1_field"),
		"GATEWAY_SECRET2" => $LAVA_language->get("general.shop_secret2"),
		"GATEWAY_SECRET2_FIELD" => $LAVA_language->get("general.shop_secret2_field"),
		"GATEWAY_PAY_DESC" => $LAVA_language->get("general.pay_desc"),
		"GATEWAY_PAY_DESC_FIELD" => $LAVA_language->get("general.pay_desc_filed"),
		"GATEWAY_PAY_DESK_PLACEHOLDERS" => $LAVA_language->get("general.pay_desc_placeholders"),
		"GATEWAY_DEBUG_MODE" => $LAVA_language->get("general.debug_mode"),
		"GATEWAY_ENABLE" => $LAVA_language->get("general.gateway_status"),
		"GATEWAY_SETTINGS_TITLE" => $LAVA_language->get("general.gateway_settings"),
		"GATEWAY_ALERT_URL" => $LAVA_language->get("general.gateway_alert_url"),
		"GATEWAY_SUCCESS_URL" => $LAVA_language->get("general.gateway_success_url"),
		"GATEWAY_CURRENT_VERSION" => $LAVA_language->get("update.current_version"),
		"GATEWAY_LATEST_VERSION" => $LAVA_language->get("update.latest_version"),
		"GATEWAY_DOWNLOAD_UPDATE" => $LAVA_language->get("update.download"),
		"GATEWAY_NEW_UPDATE" => $LAVA_language->get("update.new_update")
]);

/*
 ========================== Проверка новых версий ==========================
*/

$current_version = '1.8.4';
$gateway_code = 'lava';

try {
    $json = @file_get_contents('https://raw.githubusercontent.com/AMGewka/Update/main/versions.json');
    if ($json !== false) {
        $versions = json_decode($json, true);
        if (isset($versions[$gateway_code])) {
            $latest_version = $versions[$gateway_code];

            if (version_compare($latest_version, $current_version, '>')) {
                $smarty->assign('LAVA_UPDATE_AVAILABLE', true);
                $smarty->assign('LAVA_LATEST_VERSION', $latest_version);
                $smarty->assign('LAVA_CURRENT_VERSION', $current_version);
            }
        }
    }
} catch (Throwable $e) {

}