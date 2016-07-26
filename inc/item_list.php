<?php
echo '<table class="main-data table table-striped">
<tr>
	<th>Name/Type</th>
	<th>OS</th>
	<th>Serial</th>
	<th>MAC/IP</th>
	<th>Model</th>
	<th>PSU</th>
	<th>Location</th>
	<th>Owner</th>
	<th>Dates</th>
	<th>Flags</th>
	<th class="text-right">Options</th>
</tr>';

// or print table
foreach($entries as $d){

	// fucking php
	$model_data = NULL;
	$type_data = NULL;
	$location_data = NULL;
	$owner_data = NULL;
	$repair_data = NULL;

	$repair_data = $BraXuS->PDOFetch("SELECT * FROM repairs WHERE device_id = :id AND (date_end IS NULL OR date_end < '2010-01-01 00:00:00' )", ["id" => $d["id"] ] );

	if($filter_repair && !$repair_data) continue;

	echo "\n\t\t\t<tr";
	echo " data-name='" . ( $d["name"] ?: "(no name)" ) . "'";
	echo " data-model='" . ( $d["model"] ? $d["model_brand"] . " " . $d["model_model"] : " " ) . "'";
	echo " data-serial='" . ( $d["serial"] ?: " " ) . "'";
	
	echo " data-location='";
	$al = "";
	foreach($d["location_parents"] as $l){
		$al .= $l["name"] . " ";
	}
	$al .= $d["location_name"];
	echo $al == "" ? " " : trim($al);
	echo "'";

	// resigned
	if($d["flags"][ 10 ]) echo " class=\"resigned\"";

	// Missing
	if($d["location_name"] == "Missing" || $d["flags"][ 11 ] ) echo " class=\"missing\"";

	echo ">";

	echo "\n\t\t\t\t<td>";

		// icon
		if($d["model"]){
			echo "<img class='item-icon-float' data-preview='icon/" . ( $d["model_image"] ?: "missing.jpg" ) . "' src='icon/" . ( $d["model_image"] ?: "missing.jpg" ) . "'>";
		}else{
			echo "<img class='item-icon-float' src='icon/missing.jpg'>";
		}

		// name
		if($d["name"] == ""){
			echo "<div class='item-name unavailable' id='item_" . $d["id"] . "'><a href='" . build_url("dev", $d["id"] ) . "'>(no name)</a></div>";
		}else{
			echo "<div class='item-name' id='item_" . $d["id"] . "'><a href='" . build_url("dev", $d["id"] ) . "'>" . $d["name"] . "</a></div>";
		}

		// type
		echo "<div class='item-type'>";
		if($d["type_name"] ){
			//$type_data = $BraXuS->PDOFetch("SELECT * FROM device_types WHERE id = :id", ["id" => $model_data["type"] ] );
			echo $d["type_name"];
			if(!$count[ $d["model_type"] ]) $count[ $d["model_type"] ] = 0;
			if(!$d["byod"]) $count[ $d["model_type"] ]++;
			$def_types[ $d["model_type"] ] = $type_data["name"];
		}else{
			//$type_data = $BraXuS->PDOFetch("SELECT * FROM device_types WHERE id = :id", ["id" => $d["type"] ] );
			echo "&lt;" . $d["type_guess"] . "&gt;";
			if(!$count[ $d["type"] ]) $count[ $d["type"] ] = 0;
			if(!$d["byod"]) $count[ $d["type"] ]++;
			$def_types[ $d["type"] ] = $type_data["name"];
		}
		echo "</div>";

	echo "</td>";

	// os - ok
	echo "\n\t\t\t\t";
	if($d["os"]){
		if($d['os_icon']){
			echo "<td>";
			echo "<a href='" . build_url( "filter_os", $d["os"] ) . "'>";
			echo "<img class='item-icon' src='icon/" . $d["os_icon"] . "' title='" . $d["os_name"] . " " . $d["os_version"] . "'>";
			echo "</a>";
			echo "</td>";
		}else{
			echo "<td>" . $d["os_name"] . " " . $d["os_version"] . "</td>";
		}
	}else{
		echo "<td></td>";
	}

	// serial - ok
	echo "\n\t\t\t\t<td><div class='mono-field " . ( $d["serial"] ? "'>" . $d["serial"] : " unavailable'>(no serial)" ) . "</div></td>";

	// mac & ip - ok
	echo "\n\t\t\t\t<td>";
		echo "<div class='mono-field " . ( $d["mac"] ? "'>" .  $d["mac"] : " unavailable'>(no mac)" ) . "</div>";
		echo "<div class='mono-field " . ( $d["ip"] ? "'>" . $d["ip"] : " unavailable'>(no ip)" ) . "</div>";
	echo "</td>";

	// model - ok
	echo "\n\t\t\t\t<td>";
	if($d["model"]){
		echo "<div class='item-extra'>";
		echo "<a href='" . build_url( "filter_model", $d["model"] ) . "'>";
		echo "<b>" . $d["model_brand"] . "</b><br>" . $d["model_model"];
		echo "</a>";
		echo "</div>";
	}else{
		echo "&nbsp;";
	}
	echo "</td>";

	// psu - ok
	echo "\n\t\t\t\t<td>";
	if($d["psu"]){
		echo "<div class='item-extra'>";
		if($d["psu_icon"]) echo "<img data-preview='icon/" . $d["psu_icon"] . "' class='pic' src='icon/img.png'>";
		echo "<a href='" . build_url( "filter_psu", $d["psu"] ) . "'>";
		echo $d["psu_voltage"] . "V<br>" . $d["psu_amperage"] . "A";
		echo "</a>";
		echo "</div>";
	}else{
		echo "&nbsp;";
	}
	echo "</td>";
	
	// location - ok
	echo "\n\t\t\t\t<td>";
		if($d["flags"]["s"]){

			echo "<span class='unavailable'>Storage</span>";

		}elseif($d["location"]){
			$lst = "";

			foreach($d["location_parents"] as $l){
				$lst = "<a href='" . build_url( "loc", $l["id"] ) . "'>" . $l["name"] . "</a> → " . $lst;
			}

			//$lst = substr($lst, 0, -1);

			$lst .= "<a href='" . build_url( "loc", $d["location"] ) . "'>" . $d["location_name"] . "</a>";

			echo $lst;

			if($d["location_spec"]) echo " → " . $d["location_spec"];

		}else{
			//echo "&nbsp;";
			echo "<span class='unavailable'>(no location)</span>";
		}
	echo "</td>";

	// owner - ok
	echo "\n\t\t\t\t<td>";
		if(!$d["no_owner"]){
			if($d["owner"] || $d["owners"] ){

				if($d["owners"]){
					foreach($d["owners"] as $i => $o){
						$owner_data = $BraXuS->PDOFetch("SELECT * FROM owners WHERE id = :id", ["id" => $o["owner_id"] ] );
						$o["date_aquired"] = substr($o["date_aquired"], 0, 4) == "0000" ? "" : $o["date_aquired"];
						$o["date_leave"] = substr($o["date_leave"], 0, 4) == "0000" ? "" : $o["date_leave"];
						
						if( time() < strtotime($o["date_aquired"]) ){
							echo ( $i > 0 ? "<br>" : "") . '<span style="color:#777"><i title="Not yet received" class="fa fa-clock-o" aria-hidden="true"></i> ' . $owner_data["firstname"] . " " . $owner_data["lastname"] . "</span>";
							continue;
						}

						if( $o["date_leave"] && time() > strtotime($o["date_leave"]) ){
							echo ( $i > 0 ? "<br>" : "") . '<span style="color:#bbb"><i title="Previous owner" class="fa fa-times-circle-o" aria-hidden="true"></i> ' . $owner_data["firstname"] . " " . $owner_data["lastname"] . "</span>";
							continue;
						}

						echo '<i title="Owner" class="fa fa-user" aria-hidden="true"></i> ';
						echo $owner_data["firstname"] . " " . $owner_data["lastname"];
						// if($owner_data["username"]) echo " (" . $owner_data["username"] . ")";

						break;

					}
				}else{
				
					echo "<span style='color:#f00'>";
					echo '<i title="Old format, update ASAP" class="fa fa-cogs" aria-hidden="true"></i> ';
					echo ( $d["owner_username"] ? $d["owner_username"] . "<br>(" . $d["owner_firstname"] . " " . $d["owner_lastname"] . ")" : $d["owner_firstname"] . " " . $d["owner_lastname"] );
					echo "</span>";
				}

			}elseif($d["public"]){
				echo "<span class='unavailable'>(public)</span>";
			}else{
				echo "<span class='unavailable'>(no owner)</span>";
			}
		}

	echo "</td>";

	// dates
	echo "\n\t\t\t\t<td>";
		echo "<div title='Date aquired' class='item-date" . ( $d["date_aquired"] ? "'>A " . $d["date_aquired"] : " unavailable'>A (no date)" ) . "</div>";
		echo "<div title='Date installed' class='item-date" . ( $d["date_installed"] ? "'>I " . $d["date_installed"] : " unavailable'>I (no date)" ) . "</div>";
		echo "<div title='Date serviced' class='item-date" . ( $d["date_serviced"] ? "'>S " . $d["date_serviced"] : " unavailable'>S (no date)" ) . "</div>";
		echo "<div title='Date issued' class='item-date" . ( $d["date_issued"] ? "'>R " . $d["date_issued"] : " unavailable'>R (no date)" ) . "</div>";
	echo "</td>";

	// status
	echo "\n\t\t\t\t<td class='itemtags'>";

		// flags - ok
		foreach( $d["flags"] as $k => $v ){
			$fd = $device_flags_sql[ $k ];
			echo $v ? 
			"<a href='" . build_url("flag", $k) . "' class='flag-" . $fd["short"] . "' title='" . $fd["name"] . "'>" . strtoupper($fd["short"]) . "</a>" : 
			"";
		}

		// repair
		if( $d["repairs"] && $d["repairs"][0] ){
			if($d["repairs"][0]["date_start"] && strtotime($d["repairs"][0]["date_start"]) > $date_zero ){
				echo "<a href='" . build_url("flag", "r") . "' class='r' title='Is being repaired (" . addslashes($d["repairs"][0]["description"]) . ")'>R1</a>";
			}else{
				echo "<a href='" . build_url("flag", "r") . "' class='r' title='Needs repairs (" . addslashes($d["repairs"][0]["description"]) . ")'>R0</a>";
			}
		}

	echo "</td>";

	// edit
	echo "\n\t\t\t\t<td class='text-right'><button type='button' class='btn btn-default' onclick='editDevice(" . $d["id"] . ");'><span class='glyphicon glyphicon-pencil' aria-hidden='true'></span></button></div>";

	echo "\n\t\t\t</td>";

	if(!$d["serial"]) $missing_serial++;
	if(!$d["model"]) $missing_model++;
	if(!$d["location"]) $missing_location++;

}

echo '</table>';