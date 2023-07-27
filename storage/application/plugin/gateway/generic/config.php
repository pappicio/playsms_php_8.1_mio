<?php
defined('_SECURE_') or die('Forbidden');

$callback_url = '';
if (!$core_config['daemon_process']) {
	$callback_url = _HTTP_PATH_BASE_ . "/index.php?app=call&cat=gateway&plugin=generic&access=callback";
}

$data = registry_search(0, 'gateway', 'generic');
$plugin_config['generic'] = $data['gateway']['generic'];
$plugin_config['generic']['name'] = 'generic';
$plugin_config['generic']['default_url'] = 'http://example.api.url/handler.php?user={GENERIC_API_USERNAME}&pwd={GENERIC_API_PASSWORD}&sender={GENERIC_SENDER}&msisdn={GENERIC_TO}&message={GENERIC_MESSAGE}';
$plugin_config['generic']['default_callback_url'] = $callback_url;
if (!trim($plugin_config['generic']['url'])) {
	$plugin_config['generic']['url'] = $plugin_config['generic']['default_url'];
}
if (!trim($plugin_config['generic']['callback_url'])) {
	$plugin_config['generic']['callback_url'] = $plugin_config['generic']['default_callback_url'];
}
if (!trim($plugin_config['generic']['callback_url_authcode'])) {
	$plugin_config['generic']['callback_url_authcode'] = md5(core_get_random_string());
}

// smsc configuration
$plugin_config['generic']['_smsc_config_'] = array(
	'url' => _('Generic send SMS URL'),
	'api_username' => _('API username'),
	'api_password' => _('API password'),
	'module_sender' => _('Module sender ID'),
	'datetime_timezone' => _('Module timezone') 
);
