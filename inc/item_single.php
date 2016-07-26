<?php

echo "\n\t\t\t<div class='item-full'>";

$d = $entries[0];

echo "\n\t\t\t\t<div class='page-header'><h1>" . $d["name"] . " <button type='button' class='btn btn-default' onclick='editDevice(" . $d["id"] . ");'><span class='glyphicon glyphicon-pencil' aria-hidden='true'></span></button></h1></div>";

echo "\n\t\t\t\t<img class='item-icon' data-preview='icon/" . ( $d["model_image"] ?: "missing.jpg" ) . "' src='icon/" . ( $d["model_image"] ?: "missing.jpg" ) . "'><br><br>";

echo "\n\t\t\t\t<h2>Information</h2>";

echo "\n\t\t\t\t<h3>Device info</h3>";
echo "\n\t\t\t\t<table class='table-info table table-striped'>";						
echo "\n\t\t\t\t\t<tr><td>Device Type</td><td>" . ( $d["type_name"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
echo "\n\t\t\t\t\t<tr><td>Device Brand</td><td>" . ( $d["model_brand"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
echo "\n\t\t\t\t\t<tr><td>Device Model</td><td>" . ( $d["model_model"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
echo "\n\t\t\t\t\t<tr><td>Serial number</td><td>" . ( $d["serial"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
echo "\n\t\t\t\t\t<tr><td>MAC</td><td>" . ( $d["mac"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
echo "\n\t\t\t\t\t<tr><td>IP</td><td>" . ( $d["ip"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";

if($d["psu"]){
	echo "\n\t\t\t\t\t<tr><td>PSU Brand</td><td>" . $d["psu_brand"] . "</td></tr>";
	echo "\n\t\t\t\t\t<tr><td>PSU Voltage</td><td>" . $d["psu_voltage"] . "V</td></tr>";
	echo "\n\t\t\t\t\t<tr><td>PSU Amperage</td><td>" . $d["psu_amperage"] . "A</td></tr>";
	echo "\n\t\t\t\t\t<tr><td>PSU Model</td><td>" . $d["psu_model"] . "</td></tr>";
	echo "\n\t\t\t\t\t<tr><td>PSU S/N</td><td>" . ( $d["psu_serial"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
	if($d["psu_icon"]) echo "\n\t\t\t\t\t<tr><td>PSU Image</td><td><img data-preview='icon/" . $d["psu_icon"] . "' class='pic-big' src='icon/" . $d["psu_icon"] . "'></td></tr>";
}

echo "\n\t\t\t\t\t<tr><td>OS</td><td><img class='pic' src='icon/" . ($d["os_icon"] ?: "missing.jpg") . "'> " . $d["os_name"] . " " . $d["os_version"] . "</td></tr>";
echo "\n\t\t\t\t\t<tr><td>Location</td><td>";
	if($d["location"]){
		$lst = "";

		foreach($d["location_parents"] as $l){
			$lst = "<a href='" . build_url( "loc", $l["id"] ) . "'>" . $l["name"] . "</a> → " . $lst;
		}

		$lst .= "<a href='" . build_url( "loc", $d["location"] ) . "'>" . $d["location_name"] . "</a>";

		echo substr($lst, 0, -4);

	}else{
		echo "<span class='unavailable'>(no location)</span>";
	}
echo "</td></tr>";

$device_additional = $BraXuS->PDOFetchAll("SELECT * FROM device_additional WHERE device_id = :id", ["id" => $d["id"] ] );
foreach($device_additional as $m){
	echo "\n\t\t\t\t\t" . '<tr><td>' . $device_info_sql[ $m["key"] ]["name"] . '</td><td>' . $m["value"] . '</tr>';
}

$model_additional = $BraXuS->PDOFetchAll("SELECT * FROM model_additional WHERE model_id = :id", ["id" => $d["model"] ] );
foreach($model_additional as $m){
	echo "\n\t\t\t\t\t" . '<tr><td>' . $model_info_sql[ $m["key"] ]["name"] . '</td><td>' . $m["value"] . '</tr>';
}

echo "\n\t\t\t\t</table>";

if(!$d["no_owner"]){

	echo "\n\n\t\t\t\t<h3>Owners</h3>";

	if($d["owners"]){

		$no_owner = true;
		foreach($d["owners"] as $o){
			$o["date_aquired"] = substr($o["date_aquired"], 0, 4) == "0000" ? "" : $o["date_aquired"];
			$o["date_leave"] = substr($o["date_leave"], 0, 4) == "0000" ? "" : $o["date_leave"];

			if( strtotime( $o["date_aquired"] ) > time() ) { continue; }
			if( $o["date_leave"] && strtotime( $o["date_leave"] ) > time() ){ $no_owner = false; break; }
			if( !$o["date_leave"] ){ $no_owner = false; break; }
		}

		echo $no_owner ? "<h4><strong>No current owner</strong></h4>" : "";

		$text_co = false; // upcoming
		$text_po = false; // previous

		foreach($d["owners"] as $o){

			$o["date_aquired"] = substr($o["date_aquired"], 0, 4) == "0000" ? "" : $o["date_aquired"];
			$o["date_leave"] = substr($o["date_leave"], 0, 4) == "0000" ? "" : $o["date_leave"];

			if(!$text_po && $o["date_leave"] != "" && time() > strtotime($o["date_leave"]) ){
				echo "\n\t\t\t\t" . '<h4>Previous owners</h4>';
				$text_po = true;
			}

			if(!$text_co && strtotime( $o["date_aquired"] ) > time() ){
				echo "\n\t\t\t\t" . '<h4>Upcoming owners</h4>';
				$text_co = true;
			}

			if($o["date_leave"] == "" && $o["date_aquired"] != "" && time() > strtotime( $o["date_aquired"] ) ){
				echo "\n\t\t\t\t" . '<h4>Current owner</h4>';
			}

			$owner_data = $BraXuS->PDOFetch("SELECT * FROM owners WHERE id = :id", ["id" => $o["owner_id"] ] );
			echo "<table class='table-info table table-striped'>";
			echo "<tr><td width='120'>Name</td><td>" . $owner_data["firstname"] . " " . $owner_data["lastname"] . ( $owner_data["username"] ? " (" . $owner_data["username"] . ")" : "" ) . "</td></tr>";
			
			if( time() < strtotime($o["date_aquired"]) ){
				echo "<tr><td>Date</td><td><span class='unavailable'>" . ( $o["date_aquired"] ?: "Unknown" ) . ( time() < strtotime($o["date_aquired"]) ? " (Not received yet)" : "" ) . " -> " . ( $o["date_leave"] ?: "N/A" ) . "</span></td></tr>";
			}else{
				echo "<tr><td>Date</td><td>" . ( $o["date_aquired"] ?: "Unknown" ) . " -> " . ( $o["date_leave"] ?: "Still has device" ) . "</td></tr>";
			}

			echo "<tr><td>SSN</td><td>" . ( $owner_data["ssn"] ? substr($owner_data["ssn"], 0, 8) . "-XXXX" : "<span class='unavailable'>(unknown)</span>" ) . "</td></tr>";
			
			echo "<tr><td>Notes</td><td>" . $o["notes"] . "</td></tr>";
			
			echo "<tr><td>Damages</td><td>" . $o["damage"] . "</td></tr>";


			if( time() < strtotime($o["date_aquired"]) ){
				echo "<tr><td>Time until ownership</td><td>";
			}else{
				echo "<tr><td>Time owned</td><td>";
			}

			if(!$o["date_aquired"]){
				echo "N/A";
			}else{
				$time_from = new DateTime( $o["date_aquired"] );
				$time_diff = $time_from->diff( new DateTime( $o["date_leave"] == "" ? date("Y-m-d H:i:s") : $o["date_leave"] ) );
				echo $time_diff->days . " days (";
				echo $time_diff->y . "y, ";
				echo $time_diff->m . "m, ";
				echo $time_diff->d . "d";
				echo ")";
			}
			echo "</td></tr>";
			

			echo "</table>";
		}
	}else{
		echo "\n\n\t\t\t\t<h4>Old format</h4>";
		echo "\n\t\t\t\t<table class='table-info table table-striped'>";
		echo "\n\t\t\t\t\t<tr><td>Username</td><td>" . ( $d["flags"]["p"] ? "<span class='unavailable'>(public)</span>" : $d["owner_username"] ) . "</td></tr>";
		echo "\n\t\t\t\t\t<tr><td>SSN</td><td>" . ( $d["owner_ssn"] ? substr($d["owner_ssn"], 0, 8) . "-XXXX" : "<span class='unavailable'>(unknown)</span>" ) . "</td></tr>";
		if($d["owner_firstname"]) echo "\n\t\t\t\t\t<tr><td>First name</td><td>" . $d["owner_firstname"] . "</td></tr>";
		if($d["owner_lastname"]) echo "\n\t\t\t\t\t<tr><td>Last name</td><td>" . $d["owner_lastname"] . "</td></tr>";
		echo "\n\t\t\t\t</table>";
	}

}


echo "\n\n\t\t\t\t<h3>Service info</h3>";
echo "\n\t\t\t\t<table class='table-info table table-striped'>";
echo "\n\t\t\t\t\t<tr><td>Date aquired</td><td>" . ( $d["date_aquired"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
echo "\n\t\t\t\t\t<tr><td>Date installed</td><td>" . ( $d["date_installed"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
echo "\n\t\t\t\t\t<tr><td>Date serviced</td><td>" . ( $d["date_serviced"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
echo "\n\t\t\t\t\t<tr><td>Date issued</td><td>" . ( $d["date_issued"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
echo "\n\t\t\t\t</table>";

echo "\n\n\t\t\t\t<h3>Flags</h3>";
echo "\n\t\t\t\t<table class='table-info table table-striped'>";
foreach( $device_flags_sql as $k => $v ){
	echo "\n\t\t\t\t\t<tr><td>" . $v["name"] . "</td><td>"; 
	if($d["flags"][$k]) echo '<i class="fa fa-check" aria-hidden="true" style="font-size:16px"></i>';
	//echo $d["flags"][$k] ? "<input type='checkbox' checked>" : "<input type='checkbox'>";
	echo "</td></tr>";
}

echo "\n\t\t\t\t</table>";


echo "\n\t\t\t\t<h2>Repair log</h2>";
echo "<table class='table-data table table-striped'>";
echo "<tr><th>ID</th><th>Status</th><th>Description</th><th>Date created</th><th>Date started</th><th>Date finished</th><th></th></tr>";
if(	sizeof($d["repairs"]) > 0){

	foreach($d["repairs"] as $r){
		echo "\n<tr>";
		echo "<td>" . $r["id"] . "</td>";

		$repstatus = "Finished";

		if(!$r["date_start"] || strtotime($r["date_start"]) < $date_zero ){
			$repstatus = "Not started";
		}elseif(!$r["date_end"] || strtotime($r["date_end"]) < $date_zero ){
			$repstatus = "Not finished";
		}

		echo "<td>" . $repstatus . "</td>";
		
		echo '<form method="post" action="data.php?save=repair">';

		echo '<input type="hidden" name="id" value="' . $r["id"] . '">';

		echo "<td><input type='text' class='form-control' name='description' value='" . $r["description"] . "'></td>";

		echo "<td>";
		echo "<input type='date' class='form-control' name='date_created' value='" . ( $r["date_created"] ?: date($date_format, 0) ) . "'>";
		echo "</td>";

		echo "<td>";
		echo "<input type='date' class='form-control' name='date_start' value='" . ( $r["date_start"] ?: date($date_format, 0) ) . "'>";
		echo "</td>";

		echo "<td>";
		echo "<input type='date' class='form-control' name='date_end' value='" . ( $r["date_end"] ?: date($date_format, 0) ) . "'>";
		echo "</td>";

		echo "<td>";
		echo "<input type='submit' class='btn btn-default' value='Save'>";
		echo "</td>";

		echo '</form>';

		echo "</tr>";

		/* 
		echo '<div class="form-group">';
			echo '<label class="col-sm-2 control-label">Date aquired</label>';
			echo '<div class="col-sm-10">';
				echo '<div id="datetimepicker1" class="input-group date">';
					echo '<input class="form-control" type="text" name="date_aquired" value="' . $main["date_aquired"] . '" placeholder="' . $date_placeholder . '">';
					echo '<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>';
				echo '</div>';
			echo '</div>';
		echo '</div>'; */

	}

}else{
	echo "<tr><td colspan='5'>All fine!</td></tr>";
}
echo "</table>";

echo "\n\n\t\t\t\t<h3>Add new</h3>";
echo "\n\t\t\t\t" . '<form class="form-inline" method="post" action="data.php?new=repair">';
echo "\n\t\t\t\t\t" . '<input type="hidden" name="id" value="' . $d["id"] . '">';
echo "\n\t\t\t\t\t" . '<input class="form-control" type="text" name="description" placeholder="description">';
echo "\n\t\t\t\t\t" . '<input class="btn btn-default" type="submit" value="Create">';
echo "\n\t\t\t\t</form>\n";

/*echo '
<script type="text/javascript">
	$(function() {
		$("#datetimepicker1").datetimepicker({ locale: "en-gb", format: "YYYY-MM-DD HH:mm:ss"});
		$("#datetimepicker2").datetimepicker({ locale: "en-gb", format: "YYYY-MM-DD HH:mm:ss"});
	});
</script>';*/


echo "\n\n\t\t\t\t<h2>Extra data</h2>";
echo "\n\t\t\t\t<table class='table-data table table-striped'>";
echo "\n\t\t\t\t\t<tr><th>Name</th></tr>";
if(	sizeof($d["extra"]) > 0){

	foreach($d["extra"] as $r){
		echo "\n\t\t\t\t\t<tr>";
		echo "<td>" . $r["data"] . "</td>";
		echo "</tr>";
	}

}else{
	echo "\n\t\t\t\t\t<tr><td><span class='unavailable'>Nothing!</span></td></tr>";
}
echo "\n\t\t\t\t</table>";

echo "\n\t\t\t</div>";