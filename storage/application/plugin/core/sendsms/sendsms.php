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


if (auth_issubuser()) {

switch (_OP_) {
	case "sendsms":
		$_SESSION['val']=12;
		$_SESSION['refresh']=' (autorefresh OFF)';
		header('Refresh: 99999999999');

		// get $to and $message from session or query string
		$to = stripslashes($_REQUEST['to']);
		$message = (stripslashes($_REQUEST['message']) ? stripslashes($_REQUEST['message']) : trim(stripslashes($_SESSION['tmp']['sendsms']['message'])));
		unset($_SESSION['tmp']['sendsms']['message']);
		
		// set themes_layout for popup
		if ($_REQUEST['popup'] == 1) {
			$_SESSION['tmp']['themes']['layout'] = 'contentonly';
		}
		
		// clear return_url as we are not in popup
		if ($_REQUEST['popup'] != 1) {
			$_SESSION['tmp']['sendsms']['return_url'] = '';
		}
		
		// sender ID
		$sms_from = sendsms_get_sender($user_config['username']);
		$user_sender_id = sender_id_getall($user_config['username']);
		$ismatched = FALSE;
		foreach ($user_sender_id as $sender_id) {
			$selected = '';
			if (strtoupper($sms_from) == strtoupper($sender_id)) {
				$selected = 'selected';
				$ismatched = TRUE;
				
				break;
			}
		}
		if ($ismatched) {
			$sms_sender_id = _select('sms_sender', $user_sender_id, $sms_from);
		} else {
			$sms_sender_id = "<input type='text' name='sms_sender' value='" . $sms_from . "' readonly>";
		}
		
		// SMS footer
		$sms_footer = $user_config['footer'];
		
		// Send SMS form ID
		$sendsms_form_id = 'msg_form_id_' . uniqid();
		
		// message template
		$c_template_option[_('Select template')] = '';
		foreach (sendsms_get_template() as $c_template) {
			$c_template_option[$c_template['title']] = $c_template['text'];
		}
		$sms_template = _select('smstemplate', $c_template_option);
		
		// build form
		unset($tpl);
		$tpl = array(
			'name' => 'sendsms',
			'vars' => array(
				'Compose message' => _('Compose message'),
				'Sender ID' => _('Sender ID'),
				'Message footer' => _('Message footer'),
				'Send to' => _('Send to'),
				'Message template' => _('Templates'),
				'Message' => _('Message'),
				'Flash message' => _('Flash message'),
				'Unicode message' => _('Unicode message'),
				'Send' => _('Send'),
				'Cancel' => _('Cancel'),
				'Schedule' => _('Schedule'),
				'Options' => _('Options'),
				'DIALOG_DISPLAY' => _dialog(),
				'SENDSMS_FORM_ID' => $sendsms_form_id,
				'SENDTO_PLACEHOLDER_TEXT' => _('Select receiver'),
				'HTTP_PATH_BASE' => _HTTP_PATH_BASE_,
				'HTTP_PATH_THEMES' => _HTTP_PATH_THEMES_,
				'THEMES_MODULE' => core_themes_get(),
				'HINT_SEND_TO' => _('digita almeno 2 caratteri per avere la lista dei contatti corrispondente'),
				'HINT_SCHEDULE' => _('Format YYYY-MM-DD hh:mm'),
				'HINT_UNICODE_MESSAGE' => _hint(_('Unicode message detected automatically')),
				'sms_from' => $sms_from,
				'sms_footer' => $sms_footer,
				'to' => $to,
				'sms_sender_id' => $sms_sender_id,
				'sms_template' => $sms_template,
				
				// 'sms_schedule' => core_display_datetime(core_get_datetime()),
				'sms_schedule' => '',
				'message' => $message,
				'sms_footer_length' => $user_config['opt']['sms_footer_length'],
				'per_sms_length' => $user_config['opt']['per_sms_length'],
				'per_sms_length_unicode' => $user_config['opt']['per_sms_length_unicode'],
				'max_sms_length' => $user_config['opt']['max_sms_length'],
				'max_sms_length_unicode' => $user_config['opt']['max_sms_length_unicode'],
				'lang' => substr($user_config['language_module'], 0, 2),
				'chars' => _('chars'),
				'SMS' => _('SMS') 
			),
			'ifs' => array(
				'normal' => ( $_REQUEST['popup'] == 1 ? false : true ),
				'popup' => ( $_REQUEST['popup'] == 1 ? true : false )
			)
		);
		_p(tpl_apply($tpl));
		break;
	
	case "sendsms_yes":
		
		// sender ID
		$sms_sender = trim($_REQUEST['sms_sender']);
		
		// SMS footer
		$sms_footer = trim($_REQUEST['sms_footer']);
		
		// nofooter option
		$nofooter = true;
		if ($sms_footer) {
			$nofooter = false;
		}
		
		// schedule option
		$sms_schedule = trim($_REQUEST['sms_schedule']);
		
		// type of SMS, text or flash
		$msg_flash = $_REQUEST['msg_flash'];
		$sms_type = "text";
		if ($msg_flash == "on") {
			$sms_type = "flash";
		}
		
		// unicode or not
		$msg_unicode = $_REQUEST['msg_unicode'];
		$unicode = "0";
		if ($msg_unicode == "on") {
			$unicode = "1";
		}
		
		// SMS message
		$message = $_REQUEST['message'];
		
		// save it in session for next form
		$_SESSION['tmp']['sendsms']['message'] = $message;
		
		// destination numbers
		if ($sms_to = trim($_REQUEST['p_num_text'])) {
			$sms_to = explode(',', $sms_to);
		}
		$url_ok='';
		if ($sms_to[0] && $message) {
			
			list($ok, $to, $smslog_id, $queue, $counts, $sms_count, $sms_failed, $error_strings) = sendsms_helper($user_config['username'], $sms_to, $message, $sms_type, $unicode, '', $nofooter, $sms_footer, $sms_sender, $sms_schedule, $reference_id);
			
			if (!$sms_count && $sms_failed) {
				$_SESSION['dialog']['danger'][] = _('Fail to send message to all destinations') . " (" . _('queued') . ":" . (int) $sms_count . " " . _('failed') . ":" . (int) $sms_failed . ")";
			} else if ($sms_count && $sms_failed) {
				$_SESSION['dialog']['danger'][] = _('Your message has been delivered to some of the destinations') . " (" . _('queued') . ":" . (int) $sms_count . " " . _('failed') . ":" . (int) $sms_failed . ")";
			} else if ($sms_count && !$sms_failed) {
				$_SESSION['dialog']['info'][] = _('Your message has been delivered to queue') . " (" . _('queued') . ":" . (int) $sms_count . " " . _('failed') . ":" . (int) $sms_failed . ")";
				$url_ok='index.php?app=main&inc=feature_report&route=user_outgoing&op=user_refresh';
			} else {
				if (!is_array($error_strings)) {
					$_SESSION['dialog']['danger'][] = $error_strings;
				} else {
					$_SESSION['dialog']['danger'][] = _('System error has occured');
				}
			}
		} else {
			$_SESSION['dialog']['danger'][] = _('You must select receiver and your message should not be empty');
		}

		if ($return_url = $_SESSION['tmp']['sendsms']['return_url']) {
		
			// clear return_url as we are out of popup
			$_SESSION['tmp']['sendsms']['return_url'] = '';
			
			// also clear themes_layout
			$_SESSION['tmp']['themes']['layout'] = '';
			
			if ($url_ok<>''){
				fx_alert_and_redirect("...INVIO SMS IN CORSO...", "Gli SMS Stanno per essere inviati... Reindirizzo alla pagina SMS in Uscita...", $url_ok);
			}else{
				header("Location: " . $return_url);
			}
		} else {
			if ($url_ok<>''){
				fx_alert_and_redirect("...INVIO SMS IN CORSO...", "Gli SMS Stanno per essere inviati... Reindirizzo alla pagina SMS in Uscita...", $url_ok);
			}else{
				header("Location: " . _u('index.php?app=main&inc=core_sendsms&op=sendsms'));
			}

		}
		exit();
		break;
}

}else{
		echo "<div style='text-align:center'>";
		
		$img_size_array = getimagesize('plugin/themes/common/images/playSMS_logo_full.png');
  		$width = ($img_size_array[0]/6);
  		$height = ($img_size_array[1]/6);
		echo "<img src='plugin/themes/common/images/playSMS_logo_full.png' height=$height width=$width >";  
		echo "<div style='text-align:left'>";

		
		echo  nl2br ("\n");
		echo  nl2br ("\n");
		echo  nl2br ("\n");
		
			
		echo '<span style="font-size: 26px;"> "Componi messaggio" è abilitato solo per gli utenti appartenenti alla tipologia: SUBUSERS!!! </a></span>';
			 
 
		 

}