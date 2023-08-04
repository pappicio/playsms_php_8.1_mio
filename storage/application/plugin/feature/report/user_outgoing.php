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

@set_time_limit(0);

switch (_OP_) {
	case "user_refresh":
		$_SESSION['val']=0;
		$_SESSION['link']='';
		$_SESSION['refresh'] = ' (autorefresh ON)';
		$ref = $nav['url'] . 'index.php?app=main&inc=feature_report&route=user_outgoing&op=user_outgoing';
		header("Location: " . _u($ref));
		exit();
	break;

	case "user_outgoing":
		$search_category = array(
			_('Gateway') => 'p_gateway',
			_('SMSC') => 'p_smsc',
			_('Time') => 'p_datetime',
			_('To') => 'p_dst',
			_('Message') => 'p_msg',
			_('Footer') => 'p_footer',
			_('Queue') => 'queue_code',
		);
		
		$base_url = 'index.php?app=main&inc=feature_report&route=user_outgoing&op=user_outgoing';
		$queue_label = "";
		$queue_home_link = "";
		
		$table = _DB_PREF_ . "_tblSMSOutgoing AS A";
		$fields = "B.username, A.p_gateway, A.p_smsc, A.smslog_id, A.p_dst, A.p_sms_type, A.p_msg, A.p_footer, A.p_datetime, A.p_update, A.p_status, A.parent_uid, A.queue_code";
		$conditions = [
			//'B.uid' => $_SESSION['uid'],
                        'A.parent_uid' => user_getparentbyuid($user_config["uid"]),
			'A.flag_deleted' => 0,
		];
		$extras = [];
		
		if ($queue_code = trim($_REQUEST['queue_code'])) {
			$conditions['A.queue_code'] = $queue_code;
			$queue_label = "<p class=lead>" . sprintf(_('List of queue %s'), $queue_code) . "</p>";
			$queue_home_link = _back($base_url);
			$base_url .= '&queue_code=' . $queue_code;
		} else {
			$fields .= ", COUNT(A.queue_code) AS queue_count";
			$extras['GROUP BY'] = "A.queue_code";
		}
		
		$search = themes_search($search_category, $base_url);
		$keywords = $search['dba_keywords'];
		$extras['ORDER BY'] = "A.smslog_id DESC";
		$join = "INNER JOIN " . _DB_PREF_ . "_tblUser AS B ON A.uid=B.uid AND A.flag_deleted=B.flag_deleted";
		$list = dba_search($table, $fields, $conditions, $keywords, $extras, $join);
		$tmpCount = $list ? count($list) : 0;
		$nav = themes_nav($tmpCount, $search['url']);
		$extras['LIMIT'] = $nav['limit'];
		$extras['OFFSET'] = $nav['offset'];
		$list = dba_search($table, $fields, $conditions, $keywords, $extras, $join);

///cosi solo il primo utente al primo collegamento puo eliminare i vecchi files e nn ogni volta che va.....
	////if ($_SESSION['val'] < 1) {
	if (!isset($_SESSION['deleteoldsms']))
	{
  		$_SESSION['deleteoldsms'] = 1;

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
// cancellazione automatica degli SMS piu vecchi di 7 giorni se si visualizzano gli SMS APPENA INVIATI
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
		for ($i = 0; $i < $nav['limit']; $i++) {
			$itemid = $_POST['itemid' . $i];
			$parent = user_getparentbyuid($user_config["uid"]);
			$timepast = time() - (60 * 60 * 24 * 7);
			$up = array(
				'c_timestamp' => time(),
				'flag_deleted' => '1' 
			);
			$conditions = array(
				//'uid' => $_SESSION['uid'],
				'parent_uid' => user_getparentbyuid($user_config["uid"]),
				'smslog_id' => $itemid,
			);
			if ($queue_code = trim($_REQUEST['queue_code'])) {
				$conditions['queue_code'] = $queue_code;
			}
			//$db_query = "UPDATE "._DB_PREF_."_tblSMSOutgoing SET c_timestamp='".time()."' , flag_deleted= '1'  WHERE c_timestamp < '$timepast' AND parent_uid = '$parent' ";
			$db_query = "UPDATE "._DB_PREF_."_tblSMSOutgoing SET c_timestamp='".time()."' , flag_deleted= '1'  WHERE c_timestamp < '$timepast' AND c_timestamp <> '' AND c_timestamp <> '0' AND parent_uid = '$parent' ";
			dba_query($db_query);
		}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ripetiamo la prima parte del codice, cosi da ricaricare la lista SMS INVIATI epurata dalla cancellazione appena effettuata...
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		$search_category = array(
			_('Gateway') => 'p_gateway',
			_('SMSC') => 'p_smsc',
			_('Time') => 'p_datetime',
			_('To') => 'p_dst',
			_('Message') => 'p_msg',
			_('Footer') => 'p_footer',
			_('Queue') => 'queue_code',
		);
		
		$base_url = 'index.php?app=main&inc=feature_report&route=user_outgoing&op=user_outgoing';
		$queue_label = "";
		$queue_home_link = "";
		
		$table = _DB_PREF_ . "_tblSMSOutgoing AS A";
		$fields = "B.username, A.p_gateway, A.p_smsc, A.smslog_id, A.p_dst, A.p_sms_type, A.p_msg, A.p_footer, A.p_datetime, A.p_update, A.p_status, A.parent_uid, A.queue_code";
		$conditions = [
			//'B.uid' => $_SESSION['uid'],
                        'A.parent_uid' => user_getparentbyuid($user_config["uid"]),
			'A.flag_deleted' => 0,
		];
		$extras = [];
		
		if ($queue_code = trim($_REQUEST['queue_code'])) {
			$conditions['A.queue_code'] = $queue_code;
			$queue_label = "<p class=lead>" . sprintf(_('List of queue %s'), $queue_code) . "</p>";
			$queue_home_link = _back($base_url);
			$base_url .= '&queue_code=' . $queue_code;
		} else {
			$fields .= ", COUNT(A.queue_code) AS queue_count";
			$extras['GROUP BY'] = "A.queue_code";
		}
		
		$search = themes_search($search_category, $base_url);
		$keywords = $search['dba_keywords'];
		$extras['ORDER BY'] = "A.smslog_id DESC";
		$join = "INNER JOIN " . _DB_PREF_ . "_tblUser AS B ON A.uid=B.uid AND A.flag_deleted=B.flag_deleted";
		$list = dba_search($table, $fields, $conditions, $keywords, $extras, $join);
		$tmpCount = $list ? count($list) : 0;
		$nav = themes_nav($tmpCount, $search['url']);
		$extras['LIMIT'] = $nav['limit'];
		$extras['OFFSET'] = $nav['offset'];
		$list = dba_search($table, $fields, $conditions, $keywords, $extras, $join);
	}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// fine ripetizione della prima parte del codice
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		$content = _dialog() . "
			<h2 class=page-header-title>" . _('SMS Inviati dal mio Gruppo') . $_SESSION['refresh'] . "</h2>
			" . $queue_label . "
			<p>" . $search['form'] . "</p>
			<form id=fm_user_outgoing name=fm_user_outgoing action=\"index.php?app=main&inc=feature_report&route=user_outgoing&op=actions&queue_code=" . $queue_code . "\" method=POST>
			" . _CSRF_FORM_ . "
			<input type=hidden name=go value=delete>
			<div class=playsms-actions-box>
				<div class=pull-left>
					<a href=\"" . _u('index.php?app=main&inc=feature_report&route=user_outgoing&op=actions&go=export&queue_code=' . $queue_code) . "\">" . $icon_config['export'] . "</a>
				</div>
				<div class=pull-left>
					<td> &emsp;&emsp;&emsp;&emsp; </td>
				</div>
				
				<div class=pull-left>
					<a href=\"" . _u('index.php?app=main&inc=feature_report&route=user_outgoing&op=actions&go=startstoprefresh&queue_code=' . $queue_code) . "\">" . $icon_config['action'] . "</a>
				</div>
				
				<td> <- Imposta ON/OFF Autorefresh pagina (10 volte, ogni 15s e solo se SMS ancora in attesa) </td>
				
				<div class=pull-right>" . _submit(_('Are you sure you want to delete ?'), 'fm_user_outgoing', 'delete') . "</div>

				<div class=pull-right><td> Elimina gli SMS più vecchi di 7 giorni ->&emsp; </td></div>

			</div>



			


			<div class=table-responsive>

			 <!-- 
			<table class=playsms-table-list>
			 -->
			<table class=playsms-table cellpadding=1 cellspacing=2 border=2>
			<thead>
			<tr>
				<th width=15%>" . _('Date/Time')  . "</th>
				<th width=10%>" . _('Sender') . "</th>
				<th width=15%>" . _('To') . "</th>
				<th width=57%>" . _('Message') . "</th>
	<!-- 001 //////////////////////////////////////////// eliminaimo il checkbox elimina sms!!!-->
				<!--
					<th width=3% class=\"sorttable_nosort\" nowrap><input type=checkbox onclick=CheckUncheckAll(document.fm_user_outgoing)></th> 
				-->
			</tr>
			</thead>
			<tbody>";

//////////////////////////////////
		$i = $nav['top'];
		$j = 0;
		$inviato=1;
		for ($j = 0; $j < count($list); $j++) {
			$list[$j] = core_display_data($list[$j]);
			$p_username = $list[$j]['username'];
			$p_gateway = $list[$j]['p_gateway'];
			$p_smsc = $list[$j]['p_smsc'];
			$smslog_id = $list[$j]['smslog_id'];
			$p_uid = $list[$j]['uid'];
			$p_dst = $list[$j]['p_dst'];
			$current_p_dst = report_resolve_sender($p_uid, $p_dst);
			$p_sms_type = $list[$j]['p_sms_type'];
			if (($p_footer = $list[$j]['p_footer']) && (($p_sms_type == "text") || ($p_sms_type == "flash"))) {
				$p_msg = $p_msg . ' ' . $p_footer;
			}
			$p_datetime = core_display_datetime($list[$j]['p_datetime']);
			$p_update = $list[$j]['p_update'];
			$p_status = $list[$j]['p_status'];
			$c_queue_code = $list[$j]['queue_code'];
			$c_queue_count = (int) $list[$j]['queue_count'];
			$queue_view_link = "";
			if ($c_queue_count > 1) {
				$queue_view_link = "<a href='" . $base_url . "&queue_code=" . $c_queue_code . "'>" . sprintf(_('view all %d'), $c_queue_count) . "</a>";
			}
			
			// 0 = pending
			// 1 = sent
			// 2 = failed
			// 3 = delivered

			if ($p_status == "1") {
				$inviato=0;
				$p_status = "<span class=status_sent title='" . _('Sent') . "'></span>";
			} else if ($p_status == "2") {
				$p_status = "<span class=status_failed title='" . _('Failed') . "'></span>";
			} else if ($p_status == "3") {
				$p_status = "<span class=status_delivered title='" . _('Delivered') . "'></span>";
			} else {
				$inviato=0;
				$p_status = "<span class=status_pending title='" . _('Pending') . "'></span>";
			}
			$p_status = "<span class='msg_status'>" . $p_status . "</span>";

			// get billing info
			$billing = billing_getdata($smslog_id);
			$p_count = ($billing['count'] ? $billing['count'] : '0');
			$p_count = "<span class='msg_price'>" . $p_count . " sms</span>";

			$p_rate = core_display_credit($billing['rate'] ? $billing['rate'] : '0.0');
			$p_rate = "<span class='msg_rate'><span class='playsms-icon fas fa-table' title='" . _('Rate') . "'></span>" . $p_rate . "</span>";

			$p_charge = core_display_credit($billing['charge'] ? $billing['charge'] : '0.0');
			$p_charge = "<span class='msg_charge'><span class='playsms-icon fas fa-file-invoice-dollar' title='" . _('Charge') . "'></span>" . $p_charge . "</span>";

			// if send SMS failed then display charge as 0
			//if ($list[$j]['p_status'] == 2) {
			//	$p_charge = '0.00';
			//}
			$p_charge = '';
			$p_rate ='';

/////////////////////////////////////////////////////////////////////////   R E S E N D   /////////////////////////////////////////////////////////////////////////

			$msg = $list[$j]['p_msg'];
			$p_msg = core_display_text($msg);
			if ($msg && $p_dst) {
				$resend = _sendsms($p_dst, $msg, $icon_config['resend']);
				$forward = _sendsms('', $msg, $icon_config['forward']);
			}
			$c_message = "
				<div class=\"row\">
	<!-- fix 001 //////////////////////////////////////////// allunghiamo width per testo SMS !!!-->
					<!-- <div class=\"col-sm\"> -->
					<div class=\"col-sm-9\">
						<div id=\"user_outgoing_msg\">
							<div class='msg_text'>" . $p_msg . "</div>
						</div>
					</div>
					<div class=\"col-sm\">
						<div class=\"row pull-right\">
							<div class=\"col d-none d-md-block\">
								<div class=\"msg_option\">" . $resend . " " . $forward . "</div>
								<div class=\"msg_info\">" . $p_status . " " . $p_count . " " . $p_rate . " " . $p_charge . "</div>
							</div>
						</div>
					</div>
				</div>
			";
/////////////////////////////////////////////////////////////////////////
			$content .= "
				<tr>
					<td>$p_datetime</td>
					<td>$p_footer</td>
					<td><div>" . $current_p_dst . "</div><div>" . $queue_view_link . "</div></td>
					<td>$c_message</td>

	<!-- 001  //////////////////////////////////////////// eliminaimo il checkbox elimina sms!!!-->
					<!--
					<td nowrap>
						<input type=hidden name=itemid" . $j . " value=\"$smslog_id\">
						<input type=checkbox name=checkid" . $j . ">
					</td>
					-->
				</tr>";
		}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//$_SESSION['val'] ++;
		//if ($_SESSION['val'] <= 10) {
		//	if ($_SESSION['val'] > 9){
		//		$_SESSION['refresh']=' (autorefresh OFF)';
		//	}else{
		//		$_SESSION['refresh']=' (autorefresh ON)';
		//	}
		//	if ($inviato == 1) {
		//		$_SESSION['refresh']=' (autorefresh OFF)';
		//		$_SESSION['val']=9;
		//	}else{
		//		//////refresh time in secondi
		//		header('Refresh: 30'); 
		//	}
		//}else{
		//	$_SESSION['refresh']=' (autorefresh OFF)';
		//	header('Refresh: 99999999999');
		//}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$_SESSION['val'] ++;
			if (($inviato == 1) AND ($_SESSION['val'] < 10)) {
				$_SESSION['refresh']=' (autorefresh OFF)';
				$_SESSION['val']=10;
			}
		if ($_SESSION['val'] < 10) {
			$_SESSION['refresh']=' (autorefresh ON)';
			header('Refresh: 15');
		}elseif ($_SESSION['val'] == 10){
			$_SESSION['val']=12;
			$_SESSION['refresh']=' (autorefresh OFF)';
			header('Refresh: 99999999999');
			$ref = $nav['url'] ; 
			//if ( $_SESSION['link'] <> '') {
			//	$ref=($_SESSION['link'];
			//}

			header("Location: " . _u($ref));
		}elseif ($_SESSION['val'] > 15){
			$_SESSION['val']=15;
		}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		$content .= "
			</tbody>
			</table>
			</div>
			<div class=pull-right>" . $nav['form'] . "</div>
			</form>" . $queue_home_link;
		
		_p($content);
		break;
	
	case "actions":
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case 'autorefresh':
				if ($inviato == 1) {
					break;
				}else{
					$_SESSION['val']=0;
					$_SESSION['refresh']= ' (autorefresh ON)';
					$ref = $nav['url'] . '&search_keyword=' . $search['keyword'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
					header("Location: " . _u($ref));
					break;

				}
				case 'startstoprefresh':
				if ($_SESSION['val']<10){
					$_SESSION['refresh']= ' (autorefresh OFF)';
					$_SESSION['val']=9;
				}else{
					$_SESSION['refresh']= ' (autorefresh ON)';
					$_SESSION['val']=0;
				}
				$ref = $nav['url'] ; 
				header("Location: " . _u($ref));
				exit();

			case 'export':
				$table = _DB_PREF_ . "_tblSMSOutgoing AS A";
				$fields = "B.username, A.p_gateway, A.p_smsc, A.p_datetime, A.p_dst, A.p_msg, A.p_footer, A.p_status, A.queue_code";
				$conditions = array(
					///'B.uid' => $_SESSION['uid'],
					'A.parent_uid' => user_getparentbyuid($user_config["uid"]),
					'A.flag_deleted' => 0,
				);
				if ($queue_code = trim($_REQUEST['queue_code'])) {
					$conditions['A.queue_code'] = $queue_code;
				}
				$keywords = $search['dba_keywords'];
				
				// fixme anton - will solve this later, for now maxed to 50k
				$extras = array(
					'ORDER BY' => "A.smslog_id DESC",
					'LIMIT' => 50000,
				);

				$join = "INNER JOIN " . _DB_PREF_ . "_tblUser AS B ON A.uid=B.uid AND A.flag_deleted=B.flag_deleted";
				$list = dba_search($table, $fields, $conditions, $keywords, $extras, $join);

				if (!(count($list) > 0)) {
					$ref = $nav['url'] . '&search_keyword=' . $search['keyword'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
					$_SESSION['dialog']['info'][] = _('Nothing to export');
					header("Location: " . _u($ref));
					exit();
				}

				$data[0] = array(
					_('Gateway'),
					_('SMSC'),
					_('Time'),
					_('To'),
					_('Message'),
					_('Status'),
					_('Queue'),
				);
				for ($i = 0; $i < count($list); $i++) {
					$j = $i + 1;
					$data[$j] = array(
						$list[$i]['p_gateway'],
						$list[$i]['p_smsc'],
						core_display_datetime($list[$i]['p_datetime']),
						$list[$i]['p_dst'],
						$list[$i]['p_msg'] . $list[$i]['p_footer'],
						$list[$i]['p_status'],
						$list[$i]['queue_code'],
					);
				}
				$content = core_csv_format($data);
				if ($queue_code) {
					$fn = 'user_outgoing-' . $core_config['datetime']['now_stamp'] . '-' . $queue_code . '.csv';
				} else {
					$fn = 'user_outgoing-' . $core_config['datetime']['now_stamp'] . '.csv';
				}
				core_download($content, $fn, 'text/csv');
				break;

/////////////////////////////////////////////////////////////////////////////////////////////////////
// eliminiamo, anche se non selezionati i messaggi piu vecchi di 7 giorni...
/////////////////////////////////////////////////////////////////////////////////////////////////////

			case 'delete':
				for ($i = 0; $i < $nav['limit']; $i++) {
					//$checkid = $_POST['checkid' . $i];
					$itemid = $_POST['itemid' . $i];
					$parent = user_getparentbyuid($user_config["uid"]);
					$timepast = time() - (60 * 60 * 24 * 7);
					//if (($checkid == "on") && $itemid) {
					//if ( $itemid) {
						$up = array(
							'c_timestamp' => time(),
							'flag_deleted' => '1' 
						);
						$conditions = array(
							//'uid' => $_SESSION['uid'],
							'parent_uid' => user_getparentbyuid($user_config["uid"]),
							'smslog_id' => $itemid,
						);
						if ($queue_code = trim($_REQUEST['queue_code'])) {
							$conditions['queue_code'] = $queue_code;
						}
						//dba_update(_DB_PREF_ . '_tblSMSOutgoing', $up, $conditions);
						$db_query = "UPDATE "._DB_PREF_."_tblSMSOutgoing SET c_timestamp='".time()."' , flag_deleted= '1'  WHERE c_timestamp < '$timepast' AND parent_uid = '$parent' ";
						dba_query($db_query);
					//}
				}
				$ref = $nav['url'] . '&search_keyword=' . $search['keyword'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
				$_SESSION['dialog']['info'][] = _('All messages older than 7 days, has been deleted');
				header("Location: " . _u($ref));
				exit();
		}
		break;
	}

}



if (auth_isuser()) {

@set_time_limit(0);

switch (_OP_) {
	case "user_outgoing":
		$search_category = array(
			_('Gateway') => 'p_gateway',
			_('SMSC') => 'p_smsc',
			_('Time') => 'p_datetime',
			_('To') => 'p_dst',
			_('Message') => 'p_msg',
			_('Footer') => 'p_footer',
			_('Queue') => 'queue_code',
		);
		
		$base_url = 'index.php?app=main&inc=feature_report&route=user_outgoing&op=user_outgoing';
		$queue_label = "";
		$queue_home_link = "";
		
		$table = _DB_PREF_ . "_tblSMSOutgoing AS A";
		$fields = "B.username, A.p_gateway, A.p_smsc, A.smslog_id, A.p_dst, A.p_sms_type, A.p_msg, A.p_footer, A.p_datetime, A.p_update, A.p_status, A.parent_uid, A.queue_code";
		$conditions = [
			////'B.uid' => $_SESSION['uid'],
                        'A.parent_uid' =>  ($user_config["uid"]),
			'A.flag_deleted' => 0,
		];
		$extras = [];
		
		if ($queue_code = trim($_REQUEST['queue_code'])) {
			$conditions['A.queue_code'] = $queue_code;
			$queue_label = "<p class=lead>" . sprintf(_('List of queue %s'), $queue_code) . "</p>";
			$queue_home_link = _back($base_url);
			$base_url .= '&queue_code=' . $queue_code;
		} else {
			$fields .= ", COUNT(A.queue_code) AS queue_count";
			$extras['GROUP BY'] = "A.queue_code";
		}
		
		$search = themes_search($search_category, $base_url);
		$keywords = $search['dba_keywords'];
		$extras['ORDER BY'] = "A.smslog_id DESC";
		$join = "INNER JOIN " . _DB_PREF_ . "_tblUser AS B ON A.uid=B.uid AND A.flag_deleted=B.flag_deleted";
		$list = dba_search($table, $fields, $conditions, $keywords, $extras, $join);
		$tmpCount = $list ? count($list) : 0;
		$nav = themes_nav($tmpCount, $search['url']);
		$extras['LIMIT'] = $nav['limit'];
		$extras['OFFSET'] = $nav['offset'];
		$list = dba_search($table, $fields, $conditions, $keywords, $extras, $join);
		
		$content = _dialog() . "
			<h2 class=page-header-title>" . _('SMS inviati dai Sub Users') . "</h2>
			" . $queue_label . "
			<p>" . $search['form'] . "</p>
			<form id=fm_user_outgoing name=fm_user_outgoing action=\"index.php?app=main&inc=feature_report&route=user_outgoing&op=actions&queue_code=" . $queue_code . "\" method=POST>
			" . _CSRF_FORM_ . "
			<input type=hidden name=go value=delete>
			<div class=playsms-actions-box>
				<div class=pull-left>
					<a href=\"" . _u('index.php?app=main&inc=feature_report&route=user_outgoing&op=actions&go=export&queue_code=' . $queue_code) . "\">" . $icon_config['export'] . "</a>
				</div>
				<div class=pull-right>" . _submit(_('Are you sure you want to delete ?'), 'fm_user_outgoing', 'delete') . "</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead>
			<tr>
				<th width=15%>" . _('Date/Time') . "</th>
				<th width=10%>" . _('Sender') . "</th>
				<th width=15%>" . _('To') . "</th>
				<th width=57%>" . _('Message') . "</th>
				<!-- <th width=3% class=\"sorttable_nosort\" nowrap><input type=checkbox onclick=CheckUncheckAll(document.fm_user_outgoing)></th> -->
			</tr>
			</thead>
			<tbody>";
		
		$i = $nav['top'];
		$j = 0;
		for ($j = 0; $j < count($list); $j++) {
			$list[$j] = core_display_data($list[$j]);
			$p_username = $list[$j]['username'];
			$p_gateway = $list[$j]['p_gateway'];
			$p_smsc = $list[$j]['p_smsc'];
			$smslog_id = $list[$j]['smslog_id'];
			$p_uid = $list[$j]['uid'];
			$p_dst = $list[$j]['p_dst'];
			$current_p_dst = report_resolve_sender($p_uid, $p_dst);
			$p_sms_type = $list[$j]['p_sms_type'];
			if (($p_footer = $list[$j]['p_footer']) && (($p_sms_type == "text") || ($p_sms_type == "flash"))) {
				$p_msg = $p_msg . ' ' . $p_footer;
			}
			$p_datetime = core_display_datetime($list[$j]['p_datetime']);
			$p_update = $list[$j]['p_update'];
			$p_status = $list[$j]['p_status'];
			$c_queue_code = $list[$j]['queue_code'];
			$c_queue_count = (int) $list[$j]['queue_count'];
			
			$queue_view_link = "";
			if ($c_queue_count > 1) {
				$queue_view_link = "<a href='" . $base_url . "&queue_code=" . $c_queue_code . "'>" . sprintf(_('view all %d'), $c_queue_count) . "</a>";
				
			}
			
			// 0 = pending
			// 1 = sent
			// 2 = failed
			// 3 = delivered
			if ($p_status == "1") {
				$p_status = "<span class=status_sent title='" . _('Sent') . "'></span>";
			} else if ($p_status == "2") {
				$p_status = "<span class=status_failed title='" . _('Failed') . "'></span>";
			} else if ($p_status == "3") {
				$p_status = "<span class=status_delivered title='" . _('Delivered') . "'></span>";
			} else {
				$p_status = "<span class=status_pending title='" . _('Pending') . "'></span>";
			}
			$p_status = "<span class='msg_status'>" . $p_status . "</span>";

			// get billing info
			$billing = billing_getdata($smslog_id);
			$p_count = ($billing['count'] ? $billing['count'] : '0');
			$p_count = "<span class='msg_price'>" . $p_count . " sms</span>";

			$p_rate = core_display_credit($billing['rate'] ? $billing['rate'] : '0.0');
			$p_rate = "<span class='msg_rate'><span class='playsms-icon fas fa-table' title='" . _('Rate') . "'></span>" . $p_rate . "</span>";

			$p_charge = core_display_credit($billing['charge'] ? $billing['charge'] : '0.0');
			$p_charge = "<span class='msg_charge'><span class='playsms-icon fas fa-file-invoice-dollar' title='" . _('Charge') . "'></span>" . $p_charge . "</span>";

			// if send SMS failed then display charge as 0
			if ($list[$j]['p_status'] == 2) {
				$p_charge = '0.00';
			}
			$p_charge = '';
			$p_rate = '';
/////////////////////////////resend........
			$msg = $list[$j]['p_msg'];
			$p_msg = core_display_text($msg);
			if ($msg && $p_dst) {
				$resend = ''; 
				$forward = ''; 			}
			$c_message = "
				<div class=\"row\">
	<!-- fix 001 //////////////////////////////////////////// allunghiamo width per testo SMS !!!-->
					<div class=\"col-sm-8\">
						<div id=\"user_outgoing_msg\">
							<div class='msg_text'>" . $p_msg . "</div>
						</div>
					</div>
					<div class=\"col-sm\">
						<div class=\"row pull-right\">
							<div class=\"col d-none d-md-block\">
								<div class=\"msg_option\">" . $resend . " " . $forward . "</div>
								<div class=\"msg_info\">" . $p_status . " " . $p_count . " " . $p_rate . " " . $p_charge . "</div>
							</div>
						</div>
					</div>
				</div>
			";
/////////////////////////////////////////////////////////////////////////
			$content .= "
				<tr>
					<td>$p_datetime</td>
					<td>$p_footer</td>
					<td><div>" . $current_p_dst . "</div><div>" . $queue_view_link . "</div></td>
					<td>$c_message</td>
					<td nowrap>
						<input type=hidden name=itemid" . $j . " value=\"$smslog_id\">
						<input type=checkbox name=checkid" . $j . "> 
					</td>
				</tr>";
		}
		
		$content .= "
			</tbody>
			</table>
			</div>
			<div class=pull-right>" . $nav['form'] . "</div>
			</form>" . $queue_home_link;
		
		_p($content);
		break;
	
	case "actions":
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case 'export':
				$table = _DB_PREF_ . "_tblSMSOutgoing AS A";
				$fields = "B.username, A.p_gateway, A.p_smsc, A.p_datetime, A.p_dst, A.p_msg, A.p_footer, A.p_status, A.queue_code";
				$conditions = array(
					////'B.uid' => $_SESSION['uid'],
					'A.parent_uid' => ($user_config["uid"]),
					'A.flag_deleted' => 0,
				);
				if ($queue_code = trim($_REQUEST['queue_code'])) {
					$conditions['A.queue_code'] = $queue_code;
				}
				$keywords = $search['dba_keywords'];
				
				// fixme anton - will solve this later, for now maxed to 50k
				$extras = array(
					'ORDER BY' => "A.smslog_id DESC",
					'LIMIT' => 50000,
				);

				$join = "INNER JOIN " . _DB_PREF_ . "_tblUser AS B ON A.uid=B.uid AND A.flag_deleted=B.flag_deleted";
				$list = dba_search($table, $fields, $conditions, $keywords, $extras, $join);

				if (!(count($list) > 0)) {
					$ref = $nav['url'] . '&search_keyword=' . $search['keyword'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
					$_SESSION['dialog']['info'][] = _('Nothing to export');
					header("Location: " . _u($ref));
					exit();
				}

				$data[0] = array(
					_('Gateway'),
					_('SMSC'),
					_('Time'),
					_('To'),
					_('Message'),
					_('Status'),
					_('Queue'),
				);
				for ($i = 0; $i < count($list); $i++) {
					$j = $i + 1;
					$data[$j] = array(
						$list[$i]['p_gateway'],
						$list[$i]['p_smsc'],
						core_display_datetime($list[$i]['p_datetime']),
						$list[$i]['p_dst'],
						$list[$i]['p_msg'] . $list[$i]['p_footer'],
						$list[$i]['p_status'],
						$list[$i]['queue_code'],
					);
				}
				$content = core_csv_format($data);
				if ($queue_code) {
					$fn = 'user_outgoing-' . $core_config['datetime']['now_stamp'] . '-' . $queue_code . '.csv';
				} else {
					$fn = 'user_outgoing-' . $core_config['datetime']['now_stamp'] . '.csv';
				}
				core_download($content, $fn, 'text/csv');
				break;
			
			case 'delete':
				for ($i = 0; $i < $nav['limit']; $i++) {
					$checkid = $_POST['checkid' . $i];
					$itemid = $_POST['itemid' . $i];
					if (($checkid == "on") && $itemid) {
						$up = array(
							'c_timestamp' => time(),
							'flag_deleted' => '1' 
						);
						$conditions = array(
							////'uid' => $_SESSION['uid'],
							'parent_uid' => ($user_config["uid"]),
							'smslog_id' => $itemid,
						);
						if ($queue_code = trim($_REQUEST['queue_code'])) {
							$conditions['queue_code'] = $queue_code;
						}
						dba_update(_DB_PREF_ . '_tblSMSOutgoing', $up, $conditions);
					}
				}
				$ref = $nav['url'] . '&search_keyword=' . $search['keyword'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
				$_SESSION['dialog']['info'][] = _('Selected outgoing message has been deleted');
				header("Location: " . _u($ref));
				exit();
		}
		break;
	}
}





if (auth_isadmin()) {
 
@set_time_limit(0);

switch (_OP_) {
	case "user_outgoing":
		$search_category = array(
			_('Gateway') => 'p_gateway',
			_('SMSC') => 'p_smsc',
			_('Time') => 'p_datetime',
			_('To') => 'p_dst',
			_('Message') => 'p_msg',
			_('Footer') => 'p_footer',
			_('Queue') => 'queue_code',
		);
		
		$base_url = 'index.php?app=main&inc=feature_report&route=user_outgoing&op=user_outgoing';
		$queue_label = "";
		$queue_home_link = "";
		
		$table = _DB_PREF_ . "_tblSMSOutgoing AS A";
		$fields = "B.username, A.p_gateway, A.p_smsc, A.smslog_id, A.p_dst, A.p_sms_type, A.p_msg, A.p_footer, A.p_datetime, A.p_update, A.p_status, B.uid, A.queue_code";
		$conditions = [
			'B.uid' => $_SESSION['uid'],
                        ////'A.parent_uid' =>  ($user_config["uid"]),
			'A.flag_deleted' => 0,
		];
		$extras = [];
		
		if ($queue_code = trim($_REQUEST['queue_code'])) {
			$conditions['A.queue_code'] = $queue_code;
			$queue_label = "<p class=lead>" . sprintf(_('List of queue %s'), $queue_code) . "</p>";
			$queue_home_link = _back($base_url);
			$base_url .= '&queue_code=' . $queue_code;
		} else {
			$fields .= ", COUNT(A.queue_code) AS queue_count";
			$extras['GROUP BY'] = "A.queue_code";
		}
		
		$search = themes_search($search_category, $base_url);
		$keywords = $search['dba_keywords'];
		$extras['ORDER BY'] = "A.smslog_id DESC";
		$join = "INNER JOIN " . _DB_PREF_ . "_tblUser AS B ON A.uid=B.uid AND A.flag_deleted=B.flag_deleted";
		$list = dba_search($table, $fields, $conditions, $keywords, $extras, $join);
		$tmpCount = $list ? count($list) : 0;
		$nav = themes_nav($tmpCount, $search['url']);
		$extras['LIMIT'] = $nav['limit'];
		$extras['OFFSET'] = $nav['offset'];
		$list = dba_search($table, $fields, $conditions, $keywords, $extras, $join);
		
		$content = _dialog() . "
			<h2 class=page-header-title>" . _('I meie SMS inviati') . "</h2>
			" . $queue_label . "
			<p>" . $search['form'] . "</p>
			<form id=fm_user_outgoing name=fm_user_outgoing action=\"index.php?app=main&inc=feature_report&route=user_outgoing&op=actions&queue_code=" . $queue_code . "\" method=POST>
			" . _CSRF_FORM_ . "
			<input type=hidden name=go value=delete>
			<div class=playsms-actions-box>
				<div class=pull-left>
					<a href=\"" . _u('index.php?app=main&inc=feature_report&route=user_outgoing&op=actions&go=export&queue_code=' . $queue_code) . "\">" . $icon_config['export'] . "</a>
				</div>
				<div class=pull-right>" . _submit(_('Are you sure you want to delete ?'), 'fm_user_outgoing', 'delete') . "</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead>
			<tr>
				<th width=15%>" . _('Date/Time') . "</th>
				<th width=10%>" . _('Sender') . "</th>
				<th width=15%>" . _('To') . "</th>
				<th width=57%>" . _('Message') . "</th>
				<th width=3% class=\"sorttable_nosort\" nowrap><input type=checkbox onclick=CheckUncheckAll(document.fm_user_outgoing)></th>
			</tr>
			</thead>
			<tbody>";
		
		$i = $nav['top'];
		$j = 0;
		for ($j = 0; $j < count($list); $j++) {
			$list[$j] = core_display_data($list[$j]);
			$p_username = $list[$j]['username'];
			$p_gateway = $list[$j]['p_gateway'];
			$p_smsc = $list[$j]['p_smsc'];
			$smslog_id = $list[$j]['smslog_id'];
			$p_uid = $list[$j]['uid'];
			$p_dst = $list[$j]['p_dst'];
			$current_p_dst = report_resolve_sender($p_uid, $p_dst);
			$p_sms_type = $list[$j]['p_sms_type'];
			if (($p_footer = $list[$j]['p_footer']) && (($p_sms_type == "text") || ($p_sms_type == "flash"))) {
				$p_msg = $p_msg . ' ' . $p_footer;
			}
			$p_datetime = core_display_datetime($list[$j]['p_datetime']);
			$p_update = $list[$j]['p_update'];
			$p_status = $list[$j]['p_status'];
			$c_queue_code = $list[$j]['queue_code'];
			$c_queue_count = (int) $list[$j]['queue_count'];
			
			$queue_view_link = "";
			if ($c_queue_count > 1) {
				$queue_view_link = "<a href='" . $base_url . "&queue_code=" . $c_queue_code . "'>" . sprintf(_('view all %d'), $c_queue_count) . "</a>";
			}
			
			// 0 = pending
			// 1 = sent
			// 2 = failed
			// 3 = delivered
			if ($p_status == "1") {
				$p_status = "<span class=status_sent title='" . _('Sent') . "'></span>";
			} else if ($p_status == "2") {
				$p_status = "<span class=status_failed title='" . _('Failed') . "'></span>";
			} else if ($p_status == "3") {
				$p_status = "<span class=status_delivered title='" . _('Delivered') . "'></span>";
			} else {
				$p_status = "<span class=status_pending title='" . _('Pending') . "'></span>";
			}
			$p_status = "<span class='msg_status'>" . $p_status . "</span>";

			// get billing info
			$billing = billing_getdata($smslog_id);
			$p_count = ($billing['count'] ? $billing['count'] : '0');
			$p_count = "<span class='msg_price'>" . $p_count . " sms</span>";

			$p_rate = core_display_credit($billing['rate'] ? $billing['rate'] : '0.0');
			$p_rate = "<span class='msg_rate'><span class='playsms-icon fas fa-table' title='" . _('Rate') . "'></span>" . $p_rate . "</span>";

			$p_charge = core_display_credit($billing['charge'] ? $billing['charge'] : '0.0');
			$p_charge = "<span class='msg_charge'><span class='playsms-icon fas fa-file-invoice-dollar' title='" . _('Charge') . "'></span>" . $p_charge . "</span>";

			// if send SMS failed then display charge as 0
			if ($list[$j]['p_status'] == 2) {
				$p_charge = '0.00';
			}
			$p_charge='';
			$p_rate='';
/////////////////////////////resend........
			$msg = $list[$j]['p_msg'];
			$p_msg = core_display_text($msg);
			if ($msg && $p_dst) {
				$resend = _sendsms($p_dst, $msg, $icon_config['resend']);
				$forward = _sendsms('', $msg, $icon_config['forward']);
			}
			$c_message = "
				<div class=\"row\">
	<!-- fix 001 //////////////////////////////////////////// allunghiamo width per testo SMS !!!-->
					<div class=\"col-sm-8\">
						<div id=\"user_outgoing_msg\">
							<div class='msg_text'>" . $p_msg . "</div>
						</div>
					</div>
					<div class=\"col-sm\">
						<div class=\"row pull-right\">
							<div class=\"col d-none d-md-block\">
								<div class=\"msg_option\">" . $resend . " " . $forward . "</div>
								<div class=\"msg_info\">" . $p_status . " " . $p_count . " " . $p_rate . " " . $p_charge . "</div>
							</div>
						</div>
					</div>
				</div>
			";
/////////////////////////////////////////////////////////////////////////
			$content .= "
				<tr>
					<td>$p_datetime</td>
					<td>$p_footer</td>
					<td><div>" . $current_p_dst . "</div><div>" . $queue_view_link . "</div></td>
					<td>$c_message</td>
					<td nowrap>
						<input type=hidden name=itemid" . $j . " value=\"$smslog_id\">
						<input type=checkbox name=checkid" . $j . ">
					</td>
				</tr>";
		}
		
		$content .= "
			</tbody>
			</table>
			</div>
			<div class=pull-right>" . $nav['form'] . "</div>
			</form>" . $queue_home_link;
		
		_p($content);
		break;
	
	case "actions":
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case 'export':
				$table = _DB_PREF_ . "_tblSMSOutgoing AS A";
				$fields = "B.username, A.p_gateway, A.p_smsc, A.p_datetime, A.p_dst, A.p_msg, A.p_footer, A.p_status, A.queue_code";
				$conditions = array(
					'B.uid' => $_SESSION['uid'],
					////'A.parent_uid' => ($user_config["uid"]),
					'A.flag_deleted' => 0,
				);
				if ($queue_code = trim($_REQUEST['queue_code'])) {
					$conditions['A.queue_code'] = $queue_code;
				}
				$keywords = $search['dba_keywords'];
				
				// fixme anton - will solve this later, for now maxed to 50k
				$extras = array(
					'ORDER BY' => "A.smslog_id DESC",
					'LIMIT' => 50000,
				);

				$join = "INNER JOIN " . _DB_PREF_ . "_tblUser AS B ON A.uid=B.uid AND A.flag_deleted=B.flag_deleted";
				$list = dba_search($table, $fields, $conditions, $keywords, $extras, $join);

				if (!(count($list) > 0)) {
					$ref = $nav['url'] . '&search_keyword=' . $search['keyword'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
					$_SESSION['dialog']['info'][] = _('Nothing to export');
					header("Location: " . _u($ref));
					exit();
				}

				$data[0] = array(
					_('Gateway'),
					_('SMSC'),
					_('Time'),
					_('To'),
					_('Message'),
					_('Status'),
					_('Queue'),
				);
				for ($i = 0; $i < count($list); $i++) {
					$j = $i + 1;
					$data[$j] = array(
						$list[$i]['p_gateway'],
						$list[$i]['p_smsc'],
						core_display_datetime($list[$i]['p_datetime']),
						$list[$i]['p_dst'],
						$list[$i]['p_msg'] . $list[$i]['p_footer'],
						$list[$i]['p_status'],
						$list[$i]['queue_code'],
					);
				}
				$content = core_csv_format($data);
				if ($queue_code) {
					$fn = 'user_outgoing-' . $core_config['datetime']['now_stamp'] . '-' . $queue_code . '.csv';
				} else {
					$fn = 'user_outgoing-' . $core_config['datetime']['now_stamp'] . '.csv';
				}
				core_download($content, $fn, 'text/csv');
				break;
			
			case 'delete':
				for ($i = 0; $i < $nav['limit']; $i++) {
					$checkid = $_POST['checkid' . $i];
					$itemid = $_POST['itemid' . $i];
					if (($checkid == "on") && $itemid) {
						$up = array(
							'c_timestamp' => time(),
							'flag_deleted' => '1' 
						);
						$conditions = array(
							'uid' => $_SESSION['uid'],
							////'parent_uid' => ($user_config["uid"]),
							'smslog_id' => $itemid,
						);
						if ($queue_code = trim($_REQUEST['queue_code'])) {
							$conditions['queue_code'] = $queue_code;
						}
						dba_update(_DB_PREF_ . '_tblSMSOutgoing', $up, $conditions);
					}
				}
				$ref = $nav['url'] . '&search_keyword=' . $search['keyword'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
				$_SESSION['dialog']['info'][] = _('Selected outgoing message has been deleted');
				header("Location: " . _u($ref));
				exit();
		}
		break;
	}
}

