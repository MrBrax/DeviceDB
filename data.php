<?php

// Report all errors except E_NOTICE
error_reporting(E_ALL & ~E_NOTICE);

include "defines.php";

include "braxus.php";
$BraXuS = new BraXuS();

$date_format = "Y-m-d H:i:s";

$date_placeholder = date($date_format);

$date_epoch = date($date_format, 0);
$date_zero = strtotime("2010-01-01 00:00:00");

if($_GET['edit']){

	$id = (int)$_GET['edit'];

	$main = $BraXuS->PDOFetch("SELECT * FROM devices WHERE id = :id", ["id" => $id] );

	echo '<form class="form-horizontal" id="form_edit" onsubmit="editform(this); return false;" method="post" action="data.php?savedevice=' . $id . '">';

	echo '<h1>' . htmlentities($main["name"]) . '</h1>';

	// General info
	echo '<div class="form-group"><label class="col-sm-2 control-label"><h3>General info</h3></label></div>';

	echo "<input name='id' value='" . $id . "' type='hidden'>";

	echo '<div class="form-group"><label class="col-sm-2 control-label">Name</label><div class="col-sm-10"><input class="form-control" name="name" value="' . $main["name"] . '" type="text"></div></div>';

	echo '<div class="form-group"><label class="col-sm-2 control-label">MAC</label><div class="col-sm-10"><input class="form-control mono" name="mac" value="' . $main["mac"] . '" placeholder="mac" type="text"></div></div>';
	echo '<div class="form-group"><label class="col-sm-2 control-label">IP</label><div class="col-sm-10"><input class="form-control mono" name="ip" value="' . $main["ip"] . '" placeholder="ip" type="text"></div></div>';
	echo '<div class="form-group"><label class="col-sm-2 control-label">S/N</label><div class="col-sm-10"><input class="form-control mono" name="serial" value="' . $main["serial"] . '" placeholder="serial" type="text"></div></div>';

	echo '<div class="form-group"><label class="col-sm-2 control-label">PSU</label><div class="col-sm-10"><select class="form-control" name="psu">';
	$psu_data = $BraXuS->PDOFetchAll("SELECT * FROM device_psu");
	echo "<option " . ( !$main["psu"] ? "selected " : "" ) . "value='0'>None</option>";
	foreach ($psu_data as $o) {
		echo "<option class='optpreview' style='background-image: url(icon/" . $o["icon"] . ")' " . ( $o["id"] == $main["psu"] ? "selected " : "" ) . "value='" . $o["id"] . "'>";
		echo ( $o["brand"] . " " . $o["voltage"] . "V " . $o["amperage"] . "A (" . $o["model"] . ")" );
		echo "</option>";
	}
	echo "</select></div></div>";


	echo '<div class="form-group"><label class="col-sm-2 control-label">PSU S/N</label><div class="col-sm-10"><input class="form-control mono" name="psu_serial" value="' . $main["psu_serial"] . '" placeholder="psu serial" type="text"></div></div>';




	// model
	echo '<div class="form-group"><label class="col-sm-2 control-label">Model</label><div class="col-sm-10"><select class="form-control" name="model">';
	$model_data = $BraXuS->PDOFetchAll("SELECT * FROM device_model ORDER BY brand, model");
	echo "<option " . ( !$main["model"] ? "selected " : "" ) . "value='0'>None</option>";
	foreach ($model_data as $o) {
		echo "<option " . ( $o["id"] == $main["model"] ? "selected " : "" ) . "value='" . $o["id"] . "'>";
		echo ( $o["brand"] . " " . $o["model"] );
		echo "</option>";
	}
	echo "</select></div></div>";


	// os
	echo '<div class="form-group"><label class="col-sm-2 control-label">OS</label><div class="col-sm-10"><select class="form-control" name="os">';
	$os_data = $BraXuS->PDOFetchAll("SELECT * FROM system_os ORDER BY name, version");
	echo "<option " . ( !$main["os"] ? "selected " : "" ) . "value='0'>None</option>";
	foreach ($os_data as $o) {
		echo "<option " . ( $o["id"] == $main["os"] ? "selected " : "" ) . "value='" . $o["id"] . "'>";
		echo ( $o["name"] . " " . $o["version"] );
		echo "</option>";
	}
	echo "</select></div></div>";

	// location
	echo '<div class="form-group"><label class="col-sm-2 control-label">Location</label><div class="col-sm-10"><select class="form-control" name="location">';
	
	echo "<option " . ( !$main["location"] ? "selected " : "" ) . "value='0'>None</option>";
	

	$depth = 0;

	function lTree($tree, $parent){

		global $main, $depth;

		$tree2 = [];

		foreach($tree as $i => $item){
			if($item["parent"] == $parent){
				$d = "";
				$c = "└";
				if($depth > 0) $d = str_repeat("&nbsp;&nbsp;", $depth) . $c;
				echo "<option " . ( $main["location"] == $item["id"] ? "selected " : "" ) . "value='" . $item["id"] . "'>" . $d . $item["name"] . "</option>";
				
				$depth++;
				$tree2[$item['id']] = $item;
				$tree2[$item['id']]['submenu'] = lTree($tree, $item['id']);
				$depth--;
			}
		}

		return $tree2;

	}

	$location_p = $BraXuS->PDOFetchAll("SELECT id, name, parent FROM locations ORDER BY name");

	lTree($location_p, 0);

	echo "</select></div></div>";

	echo '<div class="form-group"><label class="col-sm-2 control-label">Spec. Location</label><div class="col-sm-10"><input class="form-control" name="location_spec" value="' . $main["location_spec"] . '" placeholder="e.g. furthest back" type="text"></div></div>';

	if($main["date_aquired"] == "0000-00-00 00:00:00") $main["date_aquired"] = NULL;
	if($main["date_installed"] == "0000-00-00 00:00:00") $main["date_installed"] = NULL;
	if($main["date_serviced"] == "0000-00-00 00:00:00") $main["date_serviced"] = NULL;
	if($main["date_issued"] == "0000-00-00 00:00:00") $main["date_issued"] = NULL;

	echo '<div class="form-group">';
		echo '<label class="col-sm-2 control-label">Date aquired</label>';
		echo '<div class="col-sm-10">';
			//echo '<div id="datetimepicker1" class="input-group date">';
				echo '<input class="form-control" type="date" name="date_aquired" value="' . ( $main["date_aquired"] ?: $date_epoch ) . '" placeholder="' . $date_placeholder . '">';
				//echo '<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>';
			//echo '</div>';
		echo '</div>';
	echo '</div>';

	echo '<div class="form-group">';
		echo '<label class="col-sm-2 control-label">Date installed</label>';
		echo '<div class="col-sm-10">';
			//echo '<div id="datetimepicker2" class="input-group date">';
				echo '<input class="form-control" type="date" name="date_installed" value="' . ( $main["date_installed"] ?: $date_epoch ) . '" placeholder="' . $date_placeholder . '">';
				//echo '<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>';
			//echo '</div>';
		echo '</div>';
	echo '</div>';

	echo '<div class="form-group">';
		echo '<label class="col-sm-2 control-label">Date serviced</label>';
		echo '<div class="col-sm-10">';
			//echo '<div id="datetimepicker3" class="input-group date">';
				echo '<input class="form-control" type="date" name="date_serviced" value="' . ( $main["date_serviced"] ?: $date_epoch ) . '" placeholder="' . $date_placeholder . '">';
				//echo '<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>';
			//echo '</div>';
		echo '</div>';
	echo '</div>';

	echo '<div class="form-group">';
		echo '<label class="col-sm-2 control-label">Date issued</label>';
		echo '<div class="col-sm-10">';
			//echo '<div id="datetimepicker3" class="input-group date">';
				echo '<input class="form-control" type="date" name="date_issued" value="' . ( $main["date_issued"] ?: $date_epoch ) . '" placeholder="' . $date_placeholder . '">';
				//echo '<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>';
			//echo '</div>';
		echo '</div>';
	echo '</div>';

	/*
	// Additional data
	echo '<div class="form-group"><label class="col-sm-2 control-label"><h3>Additional data</h3></label></div>';

	echo '<div class="form-group">';
	echo '<label class="col-sm-2 control-label"></label>';
		echo '<div class="col-sm-10">';

		$additional_data = $BraXuS->PDOFetchAll("SELECT * FROM device_additional WHERE device_id = :id", ["id" => $main["id"] ] );

		foreach ($additional_data as $i => $a) {
			echo '<select name="additional[' . $i . '][key]">';
			foreach ($device_info_sql as $o) {

			}
			echo '</select>';
		}

		echo '</div>';
	echo '</div>';
	*/

	// Owners
	echo '<div class="form-group"><label class="col-sm-2 control-label"><h3>Owners</h3></label></div>';
	$ownerfull_data = $BraXuS->PDOFetchAll("SELECT * FROM owners_date WHERE device_id = :id", ["id" => $main["id"] ] );
	$owner_data = $BraXuS->PDOFetchAll("SELECT * FROM owners ORDER BY firstname, lastname");

	echo '<div class="form-group">';
		echo '<label class="col-sm-2 control-label"></label>';
		echo '<div class="col-sm-10">';

			echo '<span class="unavailable">Both date fields blank = remove</span>';
	
			echo '<div id="owners_data">';
				foreach ($ownerfull_data as $o) {

					echo '<select name="owner[' . $o["id"] . '][owner_id]">';

						foreach ($owner_data as $u) {
							echo "<option " . ( $o["owner_id"] == $u["id"] ? "selected " : "" ) . "value='" . $u["id"] . "'>";
							echo $u["firstname"] . " " . $u["lastname"] . ( $u["username"] ? " (" . $u["username"] . ")" : "" );
							echo "</option>";
						}

					echo '</select> ';

					echo '<input type="text" name="owner[' . $o["id"] . '][date_aquired]" value="' . ( substr($o["date_aquired"], 0, 4) == "0000" ? "" : $o["date_aquired"] ) . '"> -> ';
					echo '<input type="text" name="owner[' . $o["id"] . '][date_leave]" value="' . ( substr($o["date_leave"], 0, 4) == "0000" ? "" : $o["date_leave"] ) . '">';

					echo '<input type="text" name="owner[' . $o["id"] . '][damage]" value="' . $o["damage"] . '" placeholder="Damages">';
					echo '<input type="text" name="owner[' . $o["id"] . '][notes]" value="' . $o["notes"] . '" placeholder="Notes">';

					echo '<br>';

				}
			echo '</div>';

			echo '<button onclick="addowner();" type="button">+</button>';

		echo "</div>";

	echo "</div>";


	// Flags
	echo '<div class="form-group"><label class="col-sm-2 control-label"><h3>Flags</h3></label></div>';

	$main["flags"] = [];
	$flags_data = $BraXuS->PDOFetchAll("SELECT * FROM device_flags WHERE device_id = :id", ["id" => $main["id"] ] );
	foreach($flags_data as $k => $v){
		$fd = $device_flags_sql[ $v["flag_id"] ];
		$main["flags"][ $v["flag_id"] ] = $v["flag_value"];
	}

	foreach( $device_flags_sql as $flag_id => $flag_data ){
		echo '<div class="form-group"><label class="col-sm-2 control-label">' . $flag_data["name"] . '</label><div class="col-sm-10"><input type="checkbox" name="flags[' . $flag_id . ']" value="1" ' . ( $main["flags"][$flag_id] == 1 ? "checked " : "" ) . '></div></div>';
	}

	// Extra data
	echo '<div class="form-group"><label class="col-sm-2 control-label"><h3>Extra</h3></label></div>';
	echo '<div class="form-group"><label class="col-sm-2 control-label"></label><div class="col-sm-10">';
	echo "<div id='extra_container'>";
	$extra_data = $BraXuS->PDOFetchAll("SELECT * FROM device_extra WHERE device_id = :id", ["id" => $id]);
	foreach ($extra_data as $n => $m) {
		if($n > 0) echo '<br>';
		echo '<input class="form-control" type="text" name="extra[]" value="' . $m["data"] . '">';
	}
	if(sizeof($extra_data) == 0) echo '<input class="form-control" type="text" name="extra[]" value="">';
	echo "</div>";
	echo '<button type="button" onclick="addextra();">+</button>';
	echo '</div></div>';


	echo "</div>";

	echo '<div class="modal-bar"><input class="btn btn-success" type="submit" value="Save"></div>';

	echo "</form>";

	?>
	<script type="text/javascript">
		
		var owner_amount = 1;

		function addowner(){

			var html = '<select name="ownernew[' + owner_amount + '][owner_id]">';
			<?php
			foreach ($owner_data as $u) {
				echo "\n\t\t\thtml += \"";
				echo "<option " . ( $main["owner"] == $u["id"] ? "selected " : "" ) . "value='" . $u["id"] . "'>";
				echo $u["firstname"] . " " . $u["lastname"] . ( $u["username"] ? " (" . $u["username"] . ")" : "" );
				echo "</option>";
				echo '";';
			}
			?>

			html += '</select> ';

			html += '<input type="text" name="ownernew[' + owner_amount + '][date_aquired]" value="<?=( $main["date_issued"] ?: date("Y-m-d H:i:s") )?>"> -> ';
			html += '<input type="text" name="ownernew[' + owner_amount + '][date_leave]" value="<?=date("Y-m-d H:i:s")?>">';

			html += '<input type="text" name="ownernew[' + owner_amount + '][damage]" placeholder="Damage">';
			html += '<input type="text" name="ownernew[' + owner_amount + '][notes]" placeholder="Notes">';

			html += '<br>';

			owner_amount++;

			$("#owners_data").append(html);
		}

		$(dthook);

	/*
		$(function() {
			$('input[type=date]').each(function(){ 
				var d = $(this).val();
				$(this).daterangepicker({ 
					startDate: d, endDate: d, locale: { format: "YYYY-MM-DD HH:mm:ss" }, singleDatePicker: true, timePicker: true, timePickerIncrement: 30, timePicker24Hour: true 
				}); 
			});
		});*/
	</script>
	<?php

	exit;
}

if($_GET['savedevice']){

	$id = $_GET['savedevice'];

	$date_aquired = $_POST['date_aquired'];
	$date_installed = $_POST['date_installed'];
	$date_serviced = $_POST['date_serviced'];
	$date_issued = $_POST['date_issued'];

	if( strlen($date_aquired) == 4 ) $date_aquired .= "-00-00 00:00:00";
	if( strlen($date_installed) == 4 ) $date_installed .= "-00-00 00:00:00";
	if( strlen($date_serviced) == 4 ) $date_serviced .= "-00-00 00:00:00";
	if( strlen($date_issued) == 4 ) $date_issued .= "-00-00 00:00:00";

	if( strlen($date_aquired) == 7 ) $date_aquired .= "-00 00:00:00";
	if( strlen($date_installed) == 7 ) $date_installed .= "-00 00:00:00";
	if( strlen($date_serviced) == 7 ) $date_serviced .= "-00 00:00:00";
	if( strlen($date_issued) == 7 ) $date_issued .= "-00 00:00:00";

	if( strlen($date_aquired) == 10 ) $date_aquired .= " 00:00:00";
	if( strlen($date_installed) == 10 ) $date_installed .= " 00:00:00";
	if( strlen($date_serviced) == 10 ) $date_serviced .= " 00:00:00";
	if( strlen($date_issued) == 10 ) $date_issued .= " 00:00:00";

	$BraXuS->PDOUpdate(
		[
			"name" => $_POST['name'],
			"location" => $_POST['location'],
			"location_spec" => $_POST['location_spec'],
			"serial" => $_POST['serial'],
			"psu" => $_POST['psu'],
			"psu_serial" => $_POST['psu_serial'],
			// "owner" => "",
			"os" => $_POST['os'],
			"model" => $_POST['model'],
			"mac" => $_POST['mac'],
			"ip" => $_POST['ip'],

			"storage" => $_POST['storage'] == "1",
			"needs_repair" => $_POST['needs_repair'] == "1",
			"repairing" => $_POST['repairing'] == "1",
			"byod" => $_POST['byod'] == "1",
			"outside" => $_POST['outside'] == "1",
			"acd" => $_POST['acd'] == "1",
			"public" => $_POST['public'] == "1",
			"travel" => $_POST['travel'] == "1",
			"dyslexia" => $_POST['dyslexia'] == "1",
			"resigned" => $_POST['resigned'] == "1",

			"date_aquired" => 	$date_aquired,
			"date_installed" => $date_installed,
			"date_serviced" => 	$date_serviced,
			"date_issued" => 	$date_issued
		],
		"devices",
		"id = :id",
		["id" => $id]
	);

	// save extra
	$BraXuS->PDODelete("device_id = :id", "device_extra", ["id" => $id]);
	foreach($_POST['extra'] as $d){
		if($d == "") continue;
		$BraXuS->PDOInsert(["device_id" => $id, "data" => $d], "device_extra");
	}

	// save flags
	$BraXuS->PDODelete("device_id = :id", "device_flags", ["id" => $id]);
	if($_POST['flags']){
		foreach($_POST['flags'] as $d => $v){
			$BraXuS->PDOInsert(["device_id" => $id, "flag_id" => $d, "flag_value" => $v], "device_flags");
		}
	}

	if($_POST['owner']){
		foreach($_POST['owner'] as $oid => $o){

			if(strlen($o["date_aquired"]) == 4) $o["date_aquired"] .= "-01-01";
			if(strlen($o["date_aquired"]) == 7) $o["date_aquired"] .= "-01";
			if(strlen($o["date_leave"]) == 4) $o["date_leave"] .= "-01-01";
			if(strlen($o["date_leave"]) == 7) $o["date_leave"] .= "-01";

			if( $o["date_aquired"] == "" && $o["date_leave"] == "" ){
				$BraXuS->PDODelete("id = :id", "owners_date", [ "id" => $oid ] );
			}else{
				$BraXuS->PDOReplace([
					"id" => $oid,
					"owner_id" => $o["owner_id"],
					"device_id" => $id,
					"date_aquired" => $o["date_aquired"],
					"date_leave" => $o["date_leave"],
					"damage" => $o["damage"],
					"notes" => $o["notes"]
				], "owners_date");
			}
		}
	}

	if($_POST['ownernew']){
		foreach($_POST['ownernew'] as $oid => $o){

			if(strlen($o["date_aquired"]) == 4) $o["date_aquired"] .= "-01-01";
			if(strlen($o["date_aquired"]) == 7) $o["date_aquired"] .= "-01";
			if(strlen($o["date_leave"]) == 4) $o["date_leave"] .= "-01-01";
			if(strlen($o["date_leave"]) == 7) $o["date_leave"] .= "-01";

			if( $o["date_aquired"] == "" && $o["date_leave"] == "" ){
				
			}else{
				$BraXuS->PDOInsert([
					"owner_id" => $o["owner_id"],
					"device_id" => $id,
					"date_aquired" => $o["date_aquired"],
					"date_leave" => $o["date_leave"],
					"damage" => $o["damage"],
					"notes" => $o["notes"]
				], "owners_date");
			}
		}
	}

	echo "ok";
	exit;
	//header("Location: " . $_SERVER['HTTP_REFERER'] . "#item_" . $_GET['save'] );

}

if($_GET['save']){

	$what = $_GET['save'];

	if($what == "repair"){
		$BraXuS->PDOUpdate(
			[
				"description" => $_POST['description'],
				"date_created" => $_POST['date_created'],
				"date_start" => $_POST['date_start'],
				"date_end" => $_POST['date_end']
			],
			"repairs",
			"id = :id",
			["id" => $_POST['id']]
		) or die("save error");
		header("Location: " . $_SERVER['HTTP_REFERER'] );
		exit;
	}

	echo "no save type";

	exit;

}

if($_GET['new']){

	$type = $_GET['new'];

	if($type == "owner"){

		$BraXuS->PDOInsert(
			[
				"username" => $_POST['username'] == "" ? NULL : $_POST['username'],
				"firstname" => $_POST['firstname'],
				"lastname" => $_POST['lastname']
			],
			"owners"
		) or die("insert error");

		die("New owner '" . $_POST['firstname'] . " " . $_POST['lastname'] . "' (" . $_POST['username'] . ") created.");

	}

	if($type == "model"){

		$BraXuS->PDOInsert(
			[
				"brand" => $_POST['brand'],
				"model" => $_POST['model']
			],
			"device_model"
		) or die("insert error");

		die("New model '" . $_POST['brand'] . " " . $_POST['model'] . "' created.");

	}

	if($type == "device"){

		$newid = $BraXuS->PDOInsert(
			[
				"name" => $_POST['name'],
				"serial" => $_POST['serial'],
				//"owner" => $_POST['owner'],
				"model" => $_POST['model'],
				"location" => $_POST['location']
			],
			"devices"
		) or die("insert error");

		// add owner
		if($_POST['owner']){
			$BraXuS->PDOInsert( ["device_id" => $newid, "owner_id" => $_POST['owner'], "date_aquired" => date("Y-m-d H:i:s") ], "owners_date");
		}

		header("Location: " . $_SERVER['HTTP_REFERER'] . "#item_" . $newid );
		exit;

	}

	if($type == "location"){

		$BraXuS->PDOInsert(
			[
				"name" => $_POST['name'],
				"parent" => $_POST['parent'] == 0 ? NULL : $_POST['parent']
			],
			"locations"
		) or die("insert error");

		die("New location '" . $_POST['name'] . "' created.");

	}

	if($type == "repair"){

		$BraXuS->PDOInsert(
			[
				"device_id" => $_POST['id'],
				"description" => $_POST['description'],
				"date_created" => date($date_format)
			],
			"repairs"
		) or die("insert error");

	}

	//die("done");
	//echo "ok";
	header("Location: " . $_SERVER['HTTP_REFERER'] );
	exit;
}

if($_GET['editor']){

	$f = htmlentities( stripslashes( str_replace(".", "", $_GET['editor']) ) );

	if( file_exists("editors/" . $f . ".php") ){
		include "editors/" . $f . ".php";
	}else{
		echo "editor error";
	}

	exit;

}