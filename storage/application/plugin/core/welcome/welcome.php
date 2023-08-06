<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

if (!auth_isvalid()) {
	auth_block();
}

	echo "<div style='text-align:center'>";
		$img_size_array = getimagesize('plugin/themes/common/images/playSMS_logo_full.png');
  		$width = ($img_size_array[0]/7);
  		$height = ($img_size_array[1]/7);
	
		echo "<img src='plugin/themes/common/images/playSMS_logo_full.png' height=$height width=$width >";  
	echo "<div style='text-align:left'>";
	


$fn = _APPS_PATH_THEMES_ . '/' . core_themes_get() . '/welcome.php';

if (file_exists($fn)) {
	include $fn;
} else {
	
	$information_title = ($core_config['main']['information_title'] ? $core_config['main']['information_title'] : _('Welcome information'));
	$information_content = ($core_config['main']['information_content'] ? $core_config['main']['information_content'] : _('Go to manage site menu to edit this page'));
	
	list($information_title, $information_content) = core_display_html(array(
		$information_title,
		$information_content 
	));
	
	$tpl = array(
		'name' => 'welcome',
		'vars' => array(
			'INFORMATION_TITLE' => $information_title,
			'INFORMATION_CONTENT' => $information_content 
		),
		'injects' => array(
			'user_config' 
		) 
	);
	$tpl['vars'][$doc . '_ACTIVE'] = 'class=active';
	_p(tpl_apply($tpl));
}
