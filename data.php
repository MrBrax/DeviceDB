<?php

// Report all errors except E_NOTICE
error_reporting(E_ALL & ~E_NOTICE);

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

	// owner
	echo '<div class="form-group"><label class="col-sm-2 control-label">Owner</label><div class="col-sm-10"><select class="form-control" name="owner">';
	$owner_data = $BraXuS->PDOFetchAll("SELECT * FROM owners");
	echo "<option " . ( !$main["owner"] ? "selected " : "" ) . "value='0'>None</option>";
	foreach ($owner_data as $o) {
		echo "<option " . ( $o["id"] == $main["owner"] ? "selected " : "" ) . "value='" . $o["id"] . "'>";
		echo ( $o["username"] ? $o["username"] : $o["firstname"] . " " . $o["lastname"] );
		echo "</option>";
	}
	echo "</select></div></div>";

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
	
	//$location_root = $BraXuS->PDOFetchAll("SELECT * FROM locations WHERE parent IS NULL ORDER BY name");

	//$tree = [];
	//$parent = 0;

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



	echo '<div class="form-group"><label class="col-sm-2 control-label">In storage</label><div class="col-sm-10"><input type="checkbox" name="storage" value="1" ' . ( $main["storage"] == 1 ? "checked " : "" ) . '></div></div>';

	//echo '<div class="form-group"><label class="col-sm-2 control-label">Needs repair</label><div class="col-sm-10"><input type="checkbox" name="needs_repair" value="1" ' . ( $main["needs_repair"] == 1 ? "checked " : "" ) . '></div></div>';

	//echo '<div class="form-group"><label class="col-sm-2 control-label">Being repaired</label><div class="col-sm-10"><input type="checkbox" name="repairing" value="1" ' . ( $main["repairing"] == 1 ? "checked " : "" ) . '></div></div>';

	echo '<div class="form-group"><label class="col-sm-2 control-label">BYOD</label><div class="col-sm-10"><input type="checkbox" name="byod" value="1" ' . ( $main["byod"] == 1 ? "checked " : "" ) . '></div></div>';

	echo '<div class="form-group"><label class="col-sm-2 control-label">Outside</label><div class="col-sm-10"><input type="checkbox" name="outside" value="1" ' . ( $main["outside"] == 1 ? "checked " : "" ) . '></div></div>';

	echo '<div class="form-group"><label class="col-sm-2 control-label">Public</label><div class="col-sm-10"><input type="checkbox" name="public" value="1" ' . ( $main["public"] == 1 ? "checked " : "" ) . '></div></div>';

	echo '<div class="form-group"><label class="col-sm-2 control-label">Travel</label><div class="col-sm-10"><input type="checkbox" name="travel" value="1" ' . ( $main["travel"] == 1 ? "checked " : "" ) . '></div></div>';

	echo '<div class="form-group"><label class="col-sm-2 control-label">Active Directory</label><div class="col-sm-10"><input type="checkbox" name="acd" value="1" ' . ( $main["acd"] == 1 ? "checked " : "" ) . '></div></div>';

	echo '<div class="form-group"><label class="col-sm-2 control-label">Dyslexia</label><div class="col-sm-10"><input type="checkbox" name="dyslexia" value="1" ' . ( $main["dyslexia"] == 1 ? "checked " : "" ) . '></div></div>';

	// extra data
	echo '<div class="form-group"><label class="col-sm-2 control-label">Extra</label><div class="col-sm-10">';
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
			"owner" => $_POST['owner'],
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

			"date_aquired" => 	$date_aquired,
			"date_installed" => $date_installed,
			"date_serviced" => 	$date_serviced,
			"date_issued" => 	$date_issued
		],
		"devices",
		"id = :id",
		["id" => $id]
	);

	$BraXuS->PDODelete("device_id = :id", "device_extra", ["id" => $id]);

	foreach($_POST['extra'] as $d){
		if($d == "") continue;
		$BraXuS->PDOInsert(["device_id" => $id, "data" => $d], "device_extra");
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
				"owner" => $_POST['owner'],
				"model" => $_POST['model'],
				"location" => $_POST['location']
			],
			"devices"
		) or die("insert error");

		//echo "ok";
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