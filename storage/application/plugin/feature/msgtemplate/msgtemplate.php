<?php
defined('_SECURE_') or die('Forbidden');


if(!auth_isvalid()){auth_block();};
if(auth_issubuser()){


$gpid = $_REQUEST['gpid'];
$pid = $_REQUEST['pid'];
$tid = $_REQUEST['tid'];

if ($tid = $_REQUEST['tid']) {
	if (! ($tid = dba_valid(_DB_PREF_.'_featureMsgtemplate', 'tid', $tid))) {
		auth_block();
	}
}

switch (_OP_) {
	case "list":
		$content = _dialog() . "
			<h2 class=page-header-title>"._('Message template')."</h2>
			<form id='fm_smstemp' name='fm_smstemp' action='index.php?app=main&inc=feature_msgtemplate&op=actions' method=POST>
			"._CSRF_FORM_."
			<input type=hidden name=go value=delete>
			<div class=playsms-actions-box>
				<div class=pull-left><a href='"._u('index.php?app=main&inc=feature_msgtemplate&op=add')."'>".$icon_config['add']."</a></div>
				<div class=pull-right>" . _submit(_('Are you sure you want to delete ?'), 'fm_smstemp', 'delete') . "</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width=30%>"._('Name')."</th>
				<th width=67%>"._('Content')."</th>
				<th width=3% nowrap><input type=checkbox onclick=CheckUncheckAll(document.fm_smstemp)></th>
			</tr></thead>
			<tbody>";
		$parent_uid = user_getparentbyuid($user_config['uid']);
		$db_query = "SELECT * FROM "._DB_PREF_."_featureMsgtemplate WHERE uid='".$parent_uid."' ORDER BY t_title";

		$db_result = dba_query($db_query);
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$tid = $db_row['tid'];
			$temp_title = $db_row['t_title'];
			$temp_text = $db_row['t_text'];
			$i++;
			$content .= "
				<tr>
					<td><a href='"._u('index.php?app=main&inc=feature_msgtemplate&op=edit&tid='.$tid)."'>".$temp_title."</a></td>
					<td>$temp_text</td>
					<td nowrap><input type=checkbox name=chkid".$i."></td>
					<input type=hidden name=chkid_value".$i." value='".$db_row['tid']."'>
				</tr>";
		}
		$content .= "
			</tbody>
			</table>
			</div>
			<input type='hidden' name='item_count' value='$i'>
			</form>
			<p class=help-block>
				" . _('Notes') . ":
				<ul>
					<li>#NAME# "._('will be replaced with the name listed in phonebook')."</li>
					<li>#NUM# "._('will be replaced with the phone number listed in phonebook')."</li>
				</ul>
			</p>
		";
		_p($content);
		break;
	case "add":
		$content = _dialog() . "
			<h2 class=page-header-title>"._('Message template')."</h2>
			<h3 class=page-header-subtitle>"._('Add message template')."</h3>
			<form action='index.php?app=main&inc=feature_msgtemplate&op=actions&go=add' method=POST>
			"._CSRF_FORM_."
			<table class=playsms-table>
			<tr>
				<td class=playsms-label-sizer>"._('Message template name')."</td><td><input type=text maxlength=100 name=t_title></td>
			</tr>
			<tr>
				<td>"._('Message template content')."</td><td><textarea type=text name=t_text></textarea></td>
			</tr>	
			</table>	
			<p><input type='submit' class='button' value='"._('Save')."'></p>
			</form>
			"._back('index.php?app=main&inc=feature_msgtemplate&op=list');
			_p($content);
		break;
	case "edit":
		$db_query = "SELECT * FROM "._DB_PREF_."_featureMsgtemplate WHERE tid='$tid'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$content = _dialog() . "
			<h2 class=page-header-title>"._('Message template')."</h2>
			<h3 class=page-header-subtitle>"._('Edit message template')."</h3>
			<form action='index.php?app=main&inc=feature_msgtemplate&op=actions&go=edit' method=POST>
			"._CSRF_FORM_."
			<input type=hidden name=item_count value='".$i."'>
			<input type=hidden name=tid value='".$tid."'>
			<table class=playsms-table>
			<tr>
				<td class=playsms-label-sizer>"._('Message template name')."</td><td><input type=text maxlength=100 name=t_title value='".$db_row['t_title']."'></td>
			</tr>
			<tr>
				<td>"._('Message template content')."</td><td><textarea type=text name=t_text>".$db_row['t_text']."</textarea></td>
			</tr>
			</table>
			<input type='hidden' name='item_count' value='$i'>
			<p><input type='submit' class='button' value='"._('Save')."'></p>
			</form>
			"._back('index.php?app=main&inc=feature_msgtemplate&op=list');
		_p($content);
		break;
	case "actions":
		$go = $_REQUEST['go'];
		switch ($go) {
			case "add":
				$t_title = $_POST['t_title'];
				$t_text = $_POST['t_text'];
				if ($t_title && $t_text) {
					$db_query = "INSERT INTO "._DB_PREF_."_featureMsgtemplate (uid,t_title,t_text) VALUES ('".$user_config['uid']."','$t_title','$t_text')";
					$db_result = dba_insert_id($db_query);
					if ($db_result > 0) {
						$_SESSION['dialog']['info'][] = _('Message template has been saved');
					} else {
						$_SESSION['dialog']['info'][] = _('Fail to add message template');
					}
				} else {
					$_SESSION['dialog']['info'][] = _('You must fill all fields');
				}
				header("Location: "._u('index.php?app=main&inc=feature_msgtemplate&op=add'));
				exit();
				break;
			case "edit":
				$t_title = $_POST['t_title'];
				$t_text = $_POST['t_text'];
				if ($t_title && $t_text) {
					$db_query = "UPDATE "._DB_PREF_."_featureMsgtemplate SET c_timestamp='".time()."',t_title='$t_title', t_text='$t_text' WHERE tid='$tid'";
					$db_result = dba_affected_rows($db_query);
					if ($db_result > 0) {
						$_SESSION['dialog']['info'][] = _('Message template has been edited');
					} else {
						$_SESSION['dialog']['info'][] = _('Fail to edit message template');
					}
				} else {
					$_SESSION['dialog']['info'][] = _('You must fill all fields');
				}
				header("Location: "._u('index.php?app=main&inc=feature_msgtemplate&op=list'));
				exit();
				break;
			case "delete":
				$item_count = $_POST['item_count'];
				for ($i=1;$i<=$item_count;$i++) {
					$chkid[$i] = $_POST['chkid'.$i];
					$chkid_value[$i] = $_POST['chkid_value'.$i];
				}
				for ($i=1;$i<=$item_count;$i++) {
					if (($chkid[$i] == 'on') && $chkid_value[$i]) {
						$db_query = "DELETE FROM "._DB_PREF_."_featureMsgtemplate WHERE tid='".$chkid_value[$i]."'";
						$db_result = dba_affected_rows($db_query);
					}
				}
				$_SESSION['dialog']['info'][] = _('Selected message template has been deleted');
				header("Location: "._u('index.php?app=main&inc=feature_msgtemplate&op=list'));
				exit();
				break;
		}
}



}else{



$gpid = $_REQUEST['gpid'];
$pid = $_REQUEST['pid'];
$tid = $_REQUEST['tid'];

if ($tid = $_REQUEST['tid']) {
	if (! ($tid = dba_valid(_DB_PREF_.'_featureMsgtemplate', 'tid', $tid))) {
		auth_block();
	}
}

switch (_OP_) {
	case "list":
		$content = _dialog() . "
			<h2 class=page-header-title>"._('Message template')."</h2>
			<form id='fm_smstemp' name='fm_smstemp' action='index.php?app=main&inc=feature_msgtemplate&op=actions' method=POST>
			"._CSRF_FORM_."
			<input type=hidden name=go value=delete>
			<div class=playsms-actions-box>
				<div class=pull-left><a href='"._u('index.php?app=main&inc=feature_msgtemplate&op=add')."'>".$icon_config['add']."</a></div>
				<div class=pull-right>" . _submit(_('Are you sure you want to delete ?'), 'fm_smstemp', 'delete') . "</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width=30%>"._('Name')."</th>
				<th width=67%>"._('Content')."</th>
				<th width=3% nowrap><input type=checkbox onclick=CheckUncheckAll(document.fm_smstemp)></th>
			</tr></thead>
			<tbody>";
		$db_query = "SELECT * FROM "._DB_PREF_."_featureMsgtemplate WHERE uid='".$user_config['uid']."' ORDER BY t_title";

		$db_result = dba_query($db_query);
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$tid = $db_row['tid'];
			$temp_title = $db_row['t_title'];
			$temp_text = $db_row['t_text'];
			$i++;
			$content .= "
				<tr>
					<td><a href='"._u('index.php?app=main&inc=feature_msgtemplate&op=edit&tid='.$tid)."'>".$temp_title."</a></td>
					<td>$temp_text</td>
					<td nowrap><input type=checkbox name=chkid".$i."></td>
					<input type=hidden name=chkid_value".$i." value='".$db_row['tid']."'>
				</tr>";
		}
		$content .= "
			</tbody>
			</table>
			</div>
			<input type='hidden' name='item_count' value='$i'>
			</form>
			<p class=help-block>
				" . _('Notes') . ":
				<ul>
					<li>#NAME# "._('will be replaced with the name listed in phonebook')."</li>
					<li>#NUM# "._('will be replaced with the phone number listed in phonebook')."</li>
				</ul>
			</p>
		";
		_p($content);
		break;
	case "add":
		$content = _dialog() . "
			<h2 class=page-header-title>"._('Message template')."</h2>
			<h3 class=page-header-subtitle>"._('Add message template')."</h3>
			<form action='index.php?app=main&inc=feature_msgtemplate&op=actions&go=add' method=POST>
			"._CSRF_FORM_."
			<table class=playsms-table>
			<tr>
				<td class=playsms-label-sizer>"._('Message template name')."</td><td><input type=text maxlength=100 name=t_title></td>
			</tr>
			<tr>
				<td>"._('Message template content')."</td><td><textarea type=text name=t_text></textarea></td>
			</tr>	
			</table>	
			<p><input type='submit' class='button' value='"._('Save')."'></p>
			</form>
			"._back('index.php?app=main&inc=feature_msgtemplate&op=list');
			_p($content);
		break;
	case "edit":
		$db_query = "SELECT * FROM "._DB_PREF_."_featureMsgtemplate WHERE tid='$tid'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$content = _dialog() . "
			<h2 class=page-header-title>"._('Message template')."</h2>
			<h3 class=page-header-subtitle>"._('Edit message template')."</h3>
			<form action='index.php?app=main&inc=feature_msgtemplate&op=actions&go=edit' method=POST>
			"._CSRF_FORM_."
			<input type=hidden name=item_count value='".$i."'>
			<input type=hidden name=tid value='".$tid."'>
			<table class=playsms-table>
			<tr>
				<td class=playsms-label-sizer>"._('Message template name')."</td><td><input type=text maxlength=100 name=t_title value='".$db_row['t_title']."'></td>
			</tr>
			<tr>
				<td>"._('Message template content')."</td><td><textarea type=text name=t_text>".$db_row['t_text']."</textarea></td>
			</tr>
			</table>
			<input type='hidden' name='item_count' value='$i'>
			<p><input type='submit' class='button' value='"._('Save')."'></p>
			</form>
			"._back('index.php?app=main&inc=feature_msgtemplate&op=list');
		_p($content);
		break;
	case "actions":
		$go = $_REQUEST['go'];
		switch ($go) {
			case "add":
				$t_title = $_POST['t_title'];
				$t_text = $_POST['t_text'];
				if ($t_title && $t_text) {
					$db_query = "INSERT INTO "._DB_PREF_."_featureMsgtemplate (uid,t_title,t_text) VALUES ('".$user_config['uid']."','$t_title','$t_text')";
					$db_result = dba_insert_id($db_query);
					if ($db_result > 0) {
						$_SESSION['dialog']['info'][] = _('Message template has been saved');
					} else {
						$_SESSION['dialog']['info'][] = _('Fail to add message template');
					}
				} else {
					$_SESSION['dialog']['info'][] = _('You must fill all fields');
				}
				header("Location: "._u('index.php?app=main&inc=feature_msgtemplate&op=add'));
				exit();
				break;
			case "edit":
				$t_title = $_POST['t_title'];
				$t_text = $_POST['t_text'];
				if ($t_title && $t_text) {
					$db_query = "UPDATE "._DB_PREF_."_featureMsgtemplate SET c_timestamp='".time()."',t_title='$t_title', t_text='$t_text' WHERE tid='$tid'";
					$db_result = dba_affected_rows($db_query);
					if ($db_result > 0) {
						$_SESSION['dialog']['info'][] = _('Message template has been edited');
					} else {
						$_SESSION['dialog']['info'][] = _('Fail to edit message template');
					}
				} else {
					$_SESSION['dialog']['info'][] = _('You must fill all fields');
				}
				header("Location: "._u('index.php?app=main&inc=feature_msgtemplate&op=list'));
				exit();
				break;
			case "delete":
				$item_count = $_POST['item_count'];
				for ($i=1;$i<=$item_count;$i++) {
					$chkid[$i] = $_POST['chkid'.$i];
					$chkid_value[$i] = $_POST['chkid_value'.$i];
				}
				for ($i=1;$i<=$item_count;$i++) {
					if (($chkid[$i] == 'on') && $chkid_value[$i]) {
						$db_query = "DELETE FROM "._DB_PREF_."_featureMsgtemplate WHERE tid='".$chkid_value[$i]."'";
						$db_result = dba_affected_rows($db_query);
					}
				}
				$_SESSION['dialog']['info'][] = _('Selected message template has been deleted');
				header("Location: "._u('index.php?app=main&inc=feature_msgtemplate&op=list'));
				exit();
				break;
		}
}




}

