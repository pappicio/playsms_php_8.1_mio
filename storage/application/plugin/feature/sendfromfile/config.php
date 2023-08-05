<?php
defined('_SECURE_') or die('Forbidden');

// insert to left menu array
$menutab = $core_config['menutab']['my_account'];

if (!auth_isadmin()) {
$menu_config[$menutab][] = array(
	"index.php?app=main&inc=feature_sendfromfile&op=list",
	_('Send from file'),
	1 
);
}

$plugin_config['feature']['sendfromfile'] = [ 
	'row_limit' => 5000, 
];
