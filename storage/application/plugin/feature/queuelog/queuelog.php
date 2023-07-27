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

switch (_OP_) {
	case "queuelog_list":
		$content = _dialog() . "
			<h2 class=page-header-title>" . _('View SMS queue') . "</h2>";
		
		$count = queuelog_countall();
		if ($count) {
			$content .= "
				<p>
					" . _confirm(
							_("Are you sure you want to delete ALL queues ?"), 
							_u('index.php?app=main&inc=feature_queuelog&op=queuelog_delete_all'),
							$icon_config['delete'] . _("Delete ALL queues") . " ($count)") . "
				</p>";
		}
		
		$content .= "
			<div class=table-responsive>
			<table id=playsms-table-list class=playsms-table-list>
			<thead>
			<tr>
		";
		if (auth_isadmin()) {
			$content .= "
				<th width=20%>" . _('Queue Code') . "</th>
				<th width=10%>" . _('User') . "</th>
			";
		} else {
			$content .= "
				<th width=30%>" . _('Queue Code') . "</th>
			";
		}
		$content .= "
				<th width=15%>" . _('Scheduled') . "</th>
				<th width=3%>#</th>
				<th width=49%>" . _('Message') . "</th>
				<th width=3% nowrap>" . $icon_config['action'] . "</th>
			</tr>
			</thead>
			<tbody>
		";
		$data = queuelog_get($nav['limit'], $nav['offset']);
		for ($c = count($data) - 1; $c >= 0; $c--) {
			$c_queue_code = $data[$c]['queue_code'];
			$c_datetime_scheduled = core_display_datetime($data[$c]['datetime_scheduled']);
			$c_username = user_uid2username($data[$c]['uid']);
			
			// total number of SMS in queue
			$c_count = $data[$c]['sms_count'];
			
			$c_message = stripslashes(core_display_text($data[$c]['message']));
			$c_action = _confirm(
				_("Are you sure you want to delete queue ?") . " (" . _('queue'). ": " . $c_queue_code . ")",
				_u('index.php?app=main&inc=feature_queuelog&op=queuelog_delete&queue=' . $c_queue_code),
				'delete');
			$content .= "
				<tr>
			";
			if (auth_isadmin()) {
				$content .= "
					<td>" . $c_queue_code . "</td>
					<td>" . $c_username . "</td>
				";
			} else {
				$content .= "
					<td>" . $c_queue_code . "</td>
				";
			}
			$content .= "
					<td>" . $c_datetime_scheduled . "</td>
					<td>" . $c_count . "</td>
					<td>" . $c_message . "</td>
					<td nowrap>" . $c_action . "</td>
				</tr>
			";
		}
		$content .= "
				</tbody>
				<tfoot>
					<tr>
						<td id='playsms-table-pager' class='playsms-table-pager' colspan=6>
						<div class='form-inline pull-right'>
							<div class='btn-group btn-group-sm mx-1' role='group'>
								<button type='button' class='btn btn-secondary first'>&#8676;</button>
								<button type='button' class='btn btn-secondary prev'>&larr;</button>
								<span class='pagedisplay'></span>
							</div>
							<div class='btn-group btn-group-sm mx-1' role='group'>
								<button type='button' class='btn btn-secondary next' title='next'>&rarr;</button>
								<button type='button' class='btn btn-secondary last' title='last'>&#8677;</button>
							</div>
							<select class='form-control-sm custom-select px-1 pagesize' title='{{ Select page size }}'>
								<option selected='selected' value='10'>10</option>
								<option value='20'>20</option>
								<option value='50'>50</option>
								<option value='100'>100</option>
							</select>
						</div>
						</td>
					</tr>
				</tfoot>
			</table>
			</div>
			<script type='text/javascript'>
				$(document).ready(function() { 
					$('#playsms-table-list').tablesorterPager({container: $('#playsms-table-pager')}); 
				});
			</script>";
		_p($content);
		break;
	case "queuelog_delete":
		if ($queue = $_REQUEST['queue']) {
			if (queuelog_delete($queue)) {
				$_SESSION['dialog']['info'][] = _('Queue has been removed');
			}
		}
		header("Location: " . _u('index.php?app=main&inc=feature_queuelog&op=queuelog_list'));
		exit();
		break;
	case "queuelog_delete_all":
		if (queuelog_delete_all($queue)) {
			$_SESSION['dialog']['info'][] = _('All queues have been removed');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_queuelog&op=queuelog_list'));
		exit();
		break;
}
