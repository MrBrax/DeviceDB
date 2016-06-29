<?php
	
	// Report all errors except E_NOTICE
	error_reporting(E_ALL & ~E_NOTICE);

	include "braxus.php";
	$BraXuS = new BraXuS();

	$date_format = "Y-m-d H:i:s";

	$date_placeholder = date($date_format);

	$date_epoch = date($date_format, 0);
	$date_zero = strtotime("2010-01-01 00:00:00");

	$flags = [
		"a" => "acd", 
		"s" => "storage",
		"o" => "outside",
		//"n" => "needs_repair",
		//"r" => "repairing",
		"b" => "byod",
		"p" => "public",
		"t" => "travel",
		"d" => "dyslexia"
	];

	$device_flags = [

		"a" => [ "col" => "acd", "name" => "Active Directory" ], 
		"s" => [ "col" => "storage", "name" => "Storage" ],
		"o" => [ "col" => "outside", "name" => "Outside" ],
		"n" => [ "col" => "needs_repair", "name" => "Needs repair" ],
		"r" => [ "col" => "repairing", "name" => "Repairing" ],
		"b" => [ "col" => "byod", "name" => "BYOD" ],
		"p" => [ "col" => "public", "name" => "Public" ],
		"t" => [ "col" => "travel", "name" => "Travel" ],
		"d" => [ "col" => "dyslexia", "name" => "Dyslexia" ]

	];

	$filter_model = $_GET['filter_model'];
	$filter_flag = $_GET['flag'];
	$filter_location = $_GET['loc'];
	$filter_repair = $_GET['rep'];
	$filter_os = $_GET['os'];
	$filter_device = $_GET['dev'];

	$sqlorder = "name";
	$sqlorder_dir = "ASC";

	$order = $_GET['o'];
	$order_dir = $_GET['d'];
	if($order == "name") $sqlorder = "name";
	if($order == "ip") $sqlorder = "ip";
	if($order == "location") $sqlorder = "location";
	if($order == "1") $sqlorder_dir = "DESC";

	function build_url( $key, $val ){

		global $filter_model, $filter_flag, $filter_location, $filter_os, $filter_device, $order, $order_dir;

		$base = "/ddb/";

		$qd = [];

		if($filter_model) $qd["filter_model"] = $filter_model;
		if($filter_flag) $qd["flag"] = $filter_flag;
		if($filter_location) $qd["loc"] = $filter_location;
		if($filter_repair) $qd["rep"] = $filter_repair;
		if($filter_os) $qd["os"] = $filter_os;
		if($filter_device) $qd["dev"] = $filter_device;
		if($order) $qd["o"] = $order;
		if($order_dir) $qd["d"] = $order_dir;

		$qd[$key] = $val;

		$q = http_build_query($qd);

		return $base . ($q ? "?" . $q : "");

	}
					
	$ev = []; // sql prep array
	$eq = "1 = 1"; // sql query

	if($filter_model){
		$ev["model"] = $filter_model;
		$eq .= " AND model = :model";
	}

	if($filter_location){
		$ev["location"] = $filter_location;
		$eq .= " AND location = :location";

		$location_children = $BraXuS->PDOFetchAll("SELECT * FROM locations WHERE parent = :p", ["p" => $ev["location"] ] );
		$i = 1;
		foreach($location_children as $l){
			$ev["location" . $i] = $l["id"];
			$eq .= " OR location = :location" . $i;

			$location_subchildren = $BraXuS->PDOFetchAll("SELECT * FROM locations WHERE parent = :p", ["p" => $l["id"] ] );
			foreach($location_subchildren as $l){
				$ev["location" . $i] = $l["id"];
				$eq .= " OR location = :location" . $i;
				$i++;
			}

			$i++;
		}

	}

	if($filter_os){
		$ev["os"] = $filter_os;
		$eq .= " AND os = :os";
	}

	if($filter_device){
		$ev["devid"] = $filter_device;
		$eq .= " AND id = :devid";
	}

	if($filter_flag){
		if($flags[$filter_flag]){
			$eq .= " AND " . htmlentities( $flags[ $filter_flag ] ) . " = 1";
		}
	}

	$eq .= " AND deleted = 0";

	$missing_serial = 0;
	$missing_location = 0;
	$missing_model = 0;

	//if($section == "storage") $eq .= "AND storage = 1";

	//if($section == "repairing") $eq .= "AND repairing = 1";

	$main = $BraXuS->PDOFetchAll("SELECT * FROM devices WHERE $eq ORDER BY $sqlorder $sqlorder_dir", $ev);

	$count = [];

	$def_types = [];

	$entries = [];

	// parse first
	foreach($main as $d){

		// location
		$d["location_parents"] = [];
		$d["location_parentsn"] = [];
		if($d["location"]){
			$location_data = $BraXuS->PDOFetch("SELECT name, parent FROM locations WHERE id = :id", ["id" => $d["location"] ] );
			if($location_data){
				$d["location_name"] = $location_data["name"];

				$parent = $location_data["parent"];

				$dbg = 0;

				if($parent){
					do {
						$location_parent = $BraXuS->PDOFetch("SELECT id, name, parent FROM locations WHERE id = :id", ["id" => $parent ] );
						if($location_parent){
							array_push($d["location_parents"], ["id" => $location_parent["id"], "name" => $location_parent["name"] ] );
							array_push($d["location_parentsn"], $location_parent["name"] );
							$parent = $location_parent["parent"];
						}else{
							$parent = NULL;
						}

						$dbg++;

					}while( ($parent && $parent != "") && $dbg < 10);
				}

			}
		}

		// model
		if($d["model"]){
			$model_data = $BraXuS->PDOFetch("SELECT * FROM device_model WHERE id = :id", ["id" => $d["model"] ] );
			$d["model_brand"] = $model_data["brand"];
			$d["model_model"] = $model_data["model"];
			$d["model_image"] = $model_data["image"];
			$d["model_type"] = $model_data["type"];
			$type_data = $BraXuS->PDOFetch("SELECT * FROM device_types WHERE id = :id", ["id" => $model_data["type"] ] );
			if($type_data){
				$d["type_name"] = $type_data["name"];
			}else{
				$type_data = $BraXuS->PDOFetch("SELECT * FROM device_types WHERE id = :id", ["id" => $d["type"] ] );
				$d["type_guess"] = $type_data["name"];
			}
		}else{
			$type_data = $BraXuS->PDOFetch("SELECT * FROM device_types WHERE id = :id", ["id" => $d["type"] ] );
			$d["type_guess"] = $type_data["name"];
		}

		if($d["owner"]){
			$owner_data = $BraXuS->PDOFetch("SELECT * FROM owners WHERE id = :id", ["id" => $d["owner"] ] );
			$d["owner_username"] = $owner_data["username"];
			$d["owner_firstname"] = $owner_data["firstname"];
			$d["owner_lastname"] = $owner_data["lastname"];
			$d["owner_ssn"] = $owner_data["ssn"];
		}

		if($d["os"]){
			$os_data = $BraXuS->PDOFetch("SELECT * FROM system_os WHERE id = :id", ["id" => $d["os"] ] );
			$d["os_name"] = $os_data["name"];
			$d["os_version"] = $os_data["version"];
			$d["os_icon"] = $os_data["icon"];
		}

		if($d["psu"]){
			$psu_data = $BraXuS->PDOFetch("SELECT * FROM device_psu WHERE id = :id", ["id" => $d["psu"] ] );
			$d["psu_brand"] = $psu_data["brand"];
			$d["psu_voltage"] = $psu_data["voltage"];
			$d["psu_amperage"] = $psu_data["amperage"];
			$d["psu_model"] = $psu_data["model"];
			$d["psu_icon"] = $psu_data["icon"];
		}

		$d["flags"] = [];
		foreach( $device_flags as $k => $v ){
			$d["flags"][$k] = $d[ $v["col"] ] == 1;
		}

		$repair_data = $BraXuS->PDOFetchAll("SELECT * FROM repairs WHERE device_id = :id", ["id" => $d["id"] ] );
		$d["repairs"] = [];
		if($repair_data){
			foreach( $repair_data as $k => $v ){
				array_push($d["repairs"], [
					"id" => $v["id"],
					"description" => $v["description"],
					"date_created" => $v["date_created"],
					"date_start" => $v["date_start"],
					"date_end" => $v["date_end"]
				]);
			}
		}

		$extra_data = $BraXuS->PDOFetchAll("SELECT * FROM device_extra WHERE device_id = :id", ["id" => $d["id"] ] );
		$d["extra"] = [];
		if($extra_data){
			foreach( $extra_data as $k => $v ){
				array_push($d["extra"], [
					"data" => $v["data"]
				]);
			}
		}

		$d["date_aquired"] = preg_replace("/(\:[0-9]+)$/", "", $d["date_aquired"]);
		$d["date_installed"] = preg_replace("/(\:[0-9]+)$/", "", $d["date_installed"]);
		$d["date_serviced"] = preg_replace("/(\:[0-9]+)$/", "", $d["date_serviced"]);
		$d["date_issued"] = preg_replace("/(\:[0-9]+)$/", "", $d["date_issued"]);

		if($d["date_aquired"] == "0000-00-00 00:00" || strtotime($d["date_aquired"]) < $date_zero ) $d["date_aquired"] = NULL;
		if($d["date_installed"] == "0000-00-00 00:00" || strtotime($d["date_installed"]) < $date_zero ) $d["date_installed"] = NULL;
		if($d["date_serviced"] == "0000-00-00 00:00" || strtotime($d["date_serviced"]) < $date_zero ) $d["date_serviced"] = NULL;
		if($d["date_issued"] == "0000-00-00 00:00" || strtotime($d["date_issued"]) < $date_zero ) $d["date_issued"] = NULL;

		// year
		if( isset($d["date_aquired"]) && $d["date_aquired"][6] == "0" ) $d["date_aquired"] = substr($d["date_aquired"], 0, 4);
		if( isset($d["date_installed"]) && $d["date_installed"][6] == "0" ) $d["date_installed"] = substr($d["date_installed"], 0, 4);
		if( isset($d["date_serviced"]) && $d["date_serviced"][6] == "0" ) $d["date_serviced"] = substr($d["date_serviced"], 0, 4);
		if( isset($d["date_issued"]) && $d["date_issued"][6] == "0" ) $d["date_issued"] = substr($d["date_issued"], 0, 4);

		// month
		if( isset($d["date_aquired"]) && substr($d["date_aquired"], 8, 2) == "00" ) $d["date_aquired"] = substr($d["date_aquired"], 0, 7);
		if( isset($d["date_installed"]) && substr($d["date_installed"], 8, 2) == "00" ) $d["date_installed"] = substr($d["date_installed"], 0, 7);
		if( isset($d["date_serviced"]) && substr($d["date_serviced"], 8, 2) == "00" ) $d["date_serviced"] = substr($d["date_serviced"], 0, 7);
		if( isset($d["date_issued"]) && substr($d["date_issued"], 8, 2) == "00" ) $d["date_issued"] = substr($d["date_issued"], 0, 7);

		// day
		if( isset($d["date_aquired"]) && substr($d["date_aquired"], -5, 5) == "00:00" ) $d["date_aquired"] = substr($d["date_aquired"], 0, -6);
		if( isset($d["date_installed"]) && substr($d["date_installed"], -5, 5) == "00:00" ) $d["date_installed"] = substr($d["date_installed"], 0, -6);
		if( isset($d["date_serviced"]) && substr($d["date_serviced"], -5, 5) == "00:00" ) $d["date_serviced"] = substr($d["date_serviced"], 0, -6);
		if( isset($d["date_issued"]) && substr($d["date_issued"], -5, 5) == "00:00" ) $d["date_issued"] = substr($d["date_issued"], 0, -6);


		array_push($entries, $d);
	}

	$results_num = sizeof($entries);

	if(isset($_GET['export'])){

		include("Classes/PHPExcel.php");
    	include('Classes/PHPExcel/Writer/Excel5.php');

    	$objPHPExcel = new PHPExcel();
    	$objPHPExcel->setActiveSheetIndex(0);
   		$sheet = $objPHPExcel->getActiveSheet();

		//header("Content-Type: text/csv;charset=UTF-8");
		header("Content-Description: File Transfer");
		header("Content-Type: application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename=\"ddb-" . date("Ymd-His") . ".xls\"");
		header("Content-Transfer-Encoding: binary");

		$row = "2";
		$col = "A";
		$sheet->setCellValue("A1", "Name"); 	$sheet->getColumnDimension('A')->setWidth(30);
		$sheet->setCellValue("B1", "Type");		$sheet->getColumnDimension('B')->setWidth(20);
		$sheet->setCellValue("C1", "Serial");	$sheet->getColumnDimension('C')->setWidth(28);
		$sheet->setCellValue("D1", "Model");	$sheet->getColumnDimension('D')->setWidth(30);
		$sheet->setCellValue("E1", "Location");	$sheet->getColumnDimension('E')->setWidth(40);
		$sheet->setCellValue("F1", "Owner");	$sheet->getColumnDimension('F')->setWidth(30);		

		// styling
		$sheet->getStyle('A1:F1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('A1:F1')->getFill()->getStartColor()->setRGB('ABC9F5');
        $sheet->getStyle("A1:F1")->getFont()->setBold(true);
        $sheet->freezePane('A2');

		$sheet->getStyle('A1:F1')->applyFromArray([
		    'font'  => [
		        'bold'  => true,
		        //'color' => array('rgb' => 'FF0000'),
		        'size'  => 11,
		        'name'  => 'Arial'
	    	]
	    ]);

		foreach($entries as $d){

			$sheet->getStyle( 'A' . $row . ':F' . $row )->applyFromArray([
				'font'  => [
					// 'color' => array('rgb' => '000000'),
					'size' => 10,
					'name' => 'Arial'
				]
			]);

			$sheet->getStyle( 'C' . $row )->applyFromArray([
				'font'  => [
					'name' => 'Courier New'
				]
			]);

			$sheet->getStyle( 'A' . $row . ':F' . $row )->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_GENERAL);
			
			// name
			$sheet->setCellValue("A" . $row, ( $d["name"] == "" ? "(no name)" : $d["name"] ) );
			if($d["name"] == "") $sheet->getStyle( 'A' . $row )->applyFromArray([ 'font'  => [ 'color' => array('rgb' => '999999') ] ] );

			// type
			$sheet->setCellValue("B" . $row, ( $d["type_name"] ? $d["type_name"] : $d["type_guess"] ) );
			if(!$d["type_name"]) $sheet->getStyle( 'B' . $row )->applyFromArray([ 'font'  => [ 'color' => array('rgb' => '999999') ] ] );

			// serial
			$sheet->setCellValue("C" . $row, ( $d["serial"] ? $d["serial"] : "(no serial)" ) );
			if(!$d["serial"]) $sheet->getStyle( 'C' . $row )->applyFromArray([ 'font'  => [ 'color' => array('rgb' => '999999') ] ] );

			// model
			if($d["model"]){
				$sheet->setCellValue("D" . $row, $d["model_brand"] . " " . $d["model_model"]);
			}

			$loc = "";
			// location
			if($d["flags"]["s"]){

				$loc .= "#Storage";
				$sheet->getStyle( 'E' . $row )->applyFromArray([ 'font'  => [ 'color' => array('rgb' => '222299') ] ] );

			}elseif($d["location"]){
				$lst = "";

				foreach($d["location_parents"] as $l){
					$lst = $l["name"] . " → " . $lst;
				}

				$lst .= $d["location_name"];

				$loc .= $lst;

				if($d["location_spec"]) $loc .= " → " . $d["location_spec"];

			}else{
				$loc .= "(no location)";
				$sheet->getStyle( 'E' . $row )->applyFromArray([
					'font'  => [ 'color' => array('rgb' => '999999') ]
				]);
			}

			$sheet->setCellValue("E" . $row, $loc); 

			// owner
			if($d["owner"]){		
				$sheet->setCellValue("F" . $row, $d["owner_firstname"] . " " . $d["owner_lastname"]);
			}elseif($d["public"]){
				$sheet->setCellValue("F" . $row, "(public)");
				$sheet->getStyle( 'F' . $row )->applyFromArray([
					'font'  => [ 'color' => array('rgb' => '999999') ]
				]);
			}else{
				$sheet->setCellValue("F" . $row, "(no owner)");
				$sheet->getStyle( 'F' . $row )->applyFromArray([
					'font'  => [ 'color' => array('rgb' => '999999') ]
				]);
			}
			
			$row += 1;
			//$col = "A";
		}

		$sheet->setCellValue("A" . ($row + 1), "Generated by DDB @ " . date("Y-m-d H:i:s") );

		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$objWriter->save('php://output');

		exit;
	}

?><!doctype html>
<html>
	<head>
		<meta charset="utf-8">
	    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	    <meta name="viewport" content="width=device-width, initial-scale=1">

		<title>DDB</title>
		<script src="/js/jquery.js"></script>
		<script src="js/jquery.color.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/moment.min.js"></script>
		<script src="js/moment.en-gb.js"></script>
		<script src="js/daterangepicker.js"></script>
		<script src="js/ddb.js"></script>

		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/daterangepicker.css" rel="stylesheet">	
		<link href="css/ddb.css" rel="stylesheet">

	</head>
<body>
	<div class="header">
		<h1><span class="glyphicon glyphicon-tasks" aria-hidden="true"></span><a href="/ddb/" id="title">DDB</a></h1>
	</div>
	<div class="content">
		<div>

			<?php

				if(sizeof($entries) == 1){

					echo "\n\t\t\t<div class='item-full'>";

					$d = $entries[0];

					echo "\n\t\t\t\t<div class='page-header'><h1>" . $d["name"] . "</h1></div>";

					//echo "<div class='item-actions'>";
					//echo '<button class="btn btn-default" onclick="modal(\'?editor=repairs&id=' . $d["id"] . '\', \'Repairs\');">Repairs</button>';
					//echo "</div>";

					echo "\n\t\t\t\t<img class='item-icon' data-preview='icon/" . ( $d["model_image"] ?: "missing.jpg" ) . "' src='icon/" . ( $d["model_image"] ?: "missing.jpg" ) . "'><br><br>";

					echo "\n\t\t\t\t<h2>Information</h2>";

					echo "<h3>Device info</h3>";
					echo "<table class='table-info table table-striped'>";						
					echo "<tr><td>Device Type</td><td>" . ( $d["type_name"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
					echo "<tr><td>Device Brand</td><td>" . ( $d["model_brand"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
					echo "<tr><td>Device Model</td><td>" . ( $d["model_model"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
					echo "<tr><td>Serial number</td><td>" . ( $d["serial"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
					echo "<tr><td>MAC</td><td>" . ( $d["mac"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
					echo "<tr><td>IP</td><td>" . ( $d["ip"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";

					if($d["psu"]){
						echo "<tr><td>PSU Brand</td><td>" . $d["psu_brand"] . "</td></tr>";
						echo "<tr><td>PSU Voltage</td><td>" . $d["psu_voltage"] . "V</td></tr>";
						echo "<tr><td>PSU Amperage</td><td>" . $d["psu_amperage"] . "A</td></tr>";
						echo "<tr><td>PSU Model</td><td>" . $d["psu_model"] . "</td></tr>";
						echo "<tr><td>PSU S/N</td><td>" . ( $d["psu_serial"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
						if($d["psu_icon"]) echo "<tr><td>PSU Image</td><td><img data-preview='icon/" . $d["psu_icon"] . "' class='pic-big' src='icon/" . $d["psu_icon"] . "'></td></tr>";
					}

					echo "<tr><td>OS</td><td><img class='pic' src='icon/" . ($d["os_icon"] ?: "missing.jpg") . "'> " . $d["os_name"] . " " . $d["os_version"] . "</td></tr>";
					echo "<tr><td>Location</td><td>";
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
					echo "</table>";

					echo "<h3>Owner info</h3>";
					echo "<table class='table-info table table-striped'>";
					echo "<tr><td>Username</td><td>" . ( $d["flags"]["p"] ? "<span class='unavailable'>(public)</span>" : $d["owner_username"] ) . "</td></tr>";
					echo "<tr><td>SSN</td><td>" . ( $d["owner_ssn"] ? substr($d["owner_ssn"], 0, 8) . "-XXXX" : "<span class='unavailable'>(unknown)</span>" ) . "</td></tr>";
					if($d["owner_firstname"]) echo "<tr><td>First name</td><td>" . $d["owner_firstname"] . "</td></tr>";
					if($d["owner_lastname"]) echo "<tr><td>Last name</td><td>" . $d["owner_lastname"] . "</td></tr>";
					echo "</table>";

					echo "<h3>Service info</h3>";
					echo "<table class='table-info table table-striped'>";
					echo "<tr><td>Date aquired</td><td>" . ( $d["date_aquired"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
					echo "<tr><td>Date installed</td><td>" . ( $d["date_installed"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
					echo "<tr><td>Date serviced</td><td>" . ( $d["date_serviced"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
					echo "<tr><td>Date issued</td><td>" . ( $d["date_issued"] ?: "<span class='unavailable'>Unknown</span>" ) . "</td></tr>";
					echo "</table>";

					echo "<h3>Flags</h3>";
					echo "<table class='table-info table table-striped'>";
					foreach( $d["flags"] as $k => $v ){
						echo "<tr><td>" . $device_flags[$k]["name"] . "</td><td>"; 
						echo $v == true ? "<input type='checkbox' checked>" : "<input type='checkbox'>";
						echo "</td></tr>";
					}

					echo "</table>";


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

					echo "\n\t\t\t\t<h3>Add new</h3>";
					echo '<form class="form-inline" method="post" action="data.php?new=repair">';
					echo '<input type="hidden" name="id" value="' . $d["id"] . '">';
					echo '<input class="form-control" type="text" name="description" placeholder="description">';
					echo '<input class="btn btn-default" type="submit" value="Create">';
					echo "</form>\n";

					/*echo '
					<script type="text/javascript">
						$(function() {
							$("#datetimepicker1").datetimepicker({ locale: "en-gb", format: "YYYY-MM-DD HH:mm:ss"});
							$("#datetimepicker2").datetimepicker({ locale: "en-gb", format: "YYYY-MM-DD HH:mm:ss"});
						});
					</script>';*/

					
					echo "\n\t\t\t<h2>Extra data</h2>";
					echo "\n<table class='table-data table table-striped'>";
					echo "\n<tr><th>Name</th></tr>";
					if(	sizeof($d["extra"]) > 0){

						foreach($d["extra"] as $r){
							echo "\n<tr>";
							echo "<td>" . $r["data"] . "</td>";
							echo "</tr>";
						}

					}else{
						echo "<tr><td><span class='unavailable'>Nothing!</span></td></tr>";
					}
					echo "</table>";

					echo "</div>";

				}else{

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
							if($d["owner"]){
								
								echo ( $d["owner_username"] ? $d["owner_username"] . "<br>(" . $d["owner_firstname"] . " " . $d["owner_lastname"] . ")" : $d["owner_firstname"] . " " . $d["owner_lastname"] );

							}elseif($d["public"]){
								echo "<span class='unavailable'>(public)</span>";
							}else{
								echo "<span class='unavailable'>(no owner)</span>";
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
								echo $v == true ? 
								"<a href='" . build_url("flag", $k) . "' class='" . $k . "' title='" . $device_flags[$k]["name"] . "'>" . strtoupper($k) . "</a>" : 
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

				}
			?>

		</div>

	</div>

	<div class="sidebar">

		<div class="panel panel-default">
			<div class="panel-heading">Filters</div>
			<div class="panel-body">
				<input type="search" class="form-control" id="namesearch" placeholder="Search">
				<?php
					$filters_active = 0;
					if($filter_flag){ echo "Flag: " . htmlentities($filter_flag) . "<br>"; $filters_active++; }
					if($filter_model){ echo "Model: " . htmlentities($filter_model) . "<br>"; $filters_active++; }
					if($filter_location){ echo "Location: " . htmlentities($filter_location) . "<br>"; $filters_active++; }
					if($filter_os){ echo "OS: " . htmlentities($filter_os) . "<br>"; $filters_active++; }
					if($filters_active == 0){ echo "None"; }

					echo "<div class='itemtags'>";
					foreach($flags as $k => $v){
						echo "<a href='" . build_url("flag", $k) . "' class='" . $k . "' title='" . $v . "'>" . strtoupper($k) . "</a>";
					}
					echo "</div>";
				?>
				<hr>
				<a href="<?=build_url("rep","1")?>"><button class="btn btn-default">Repairs</button></a>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">Editors</div>
			<div class="panel-body">
				<button class="btn btn-default" onclick="modal('data.php?editor=locations', 'Location editor');">Locations</button>
			</div>
		</div>

		<!-- creation tools -->

		<div class="panel panel-default">
			<div class="panel-heading">New owner</div>
			<form data-status="true" method="post" action="data.php?new=owner" class="panel-body" id="new-owner">
				<input type="text" class="form-control" name="username" placeholder="Username">
				<input type="text" class="form-control" name="firstname" placeholder="First name">
				<input type="text" class="form-control" name="lastname" placeholder="Last name">
				<input type="submit" class="btn btn-default" value="create">
			</form>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">New model</div>
			<form data-status="true" method="post" action="data.php?new=model" class="panel-body" id="new-model">
				<input type="text" class="form-control" name="brand" placeholder="Brand">
				<input type="text" class="form-control" name="model" placeholder="Model">
				<input type="submit" class="btn btn-default" value="create">
			</form>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">New device</div>
			<form method="post" action="data.php?new=device" class="panel-body" id="new-device">
				<input type="text" class="form-control" name="name" placeholder="Name">
				<input type="text" class="form-control" name="serial" placeholder="S/N">
				<?php
					echo "<label>Owner:</label><select class='form-control' name='owner'>";
					$owner_data = $BraXuS->PDOFetchAll("SELECT * FROM owners ORDER BY username, firstname, lastname");
					echo "<option value='0'>None</option>";
					foreach ($owner_data as $o) {
						echo "<option value='" . $o["id"] . "'>";
						echo ( $o["username"] ? $o["username"] : $o["firstname"] . " " . $o["lastname"] );
						echo "</option>";
					}
					echo "</select>";

					echo "<label>Model:</label><select class='form-control' name='model'>";
					$location_data = $BraXuS->PDOFetchAll("SELECT * FROM device_model ORDER BY brand, model");
					echo "<option value='0'>None</option>";
					foreach ($location_data as $o) {
						echo "<option value='" . $o["id"] . "'>";
						echo ( $o["brand"] . " " . $o["model"] );
						echo "</option>";
					}
					echo "</select>";

					echo "<label>Location:</label><select class='form-control' name='location'>";
					$location_data = $BraXuS->PDOFetchAll("SELECT * FROM locations ORDER BY name");
					echo "<option value='0'>None</option>";
					foreach ($location_data as $o) {
						echo "<option value='" . $o["id"] . "'>";
						echo ( $o["name"] );
						echo "</option>";
					}
					echo "</select>";
				?>
				<input type="submit" class="btn btn-default" value="create">
			</form>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">New location</div>
			<form data-status="true" method="post" action="data.php?new=location" class="panel-body" id="new-location">
				
				<input type="text" class="form-control" name="name" placeholder="name">
				<label>Parent</label>
				<select class="form-control" name="parent">
					<?php
						$location_data = $BraXuS->PDOFetchAll("SELECT * FROM locations WHERE parent IS NULL ORDER BY name");
						echo "<option value='0'>None</option>";
						foreach ($location_data as $o) {
							echo "<option value='" . $o["id"] . "'>" . $o["name"] . "</option>";
						}
					?>
				</select>
				<input type="submit" class="btn btn-default" value="create">
			</form>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">Results</div>
			<div class="panel-body">
				<h4>Device types</h4>
				<?php
					echo "<table width='100%'>";
					foreach($count as $k => $v){
						echo "<tr><td>" . $def_types[ $k ] . "</td><td>" . $v . "</td></tr>";
					}
					echo "</table>";

					echo sizeof($main) . " entries";
				?>
				<br><br><h4>Missing</h4>
				<table width="100%">
					<tr><td>Serial</td><td><?=$missing_serial?></td></tr>
					<tr><td>Model</td><td><?=$missing_model?></td></tr>
					<tr><td>Location</td><td><?=$missing_location?></td></tr>
				</table>
				<a href="<?=build_url("export","1")?>">Export view</a>
			</div>
		</div>

		<br><br>

	</div>

	<div id="modalbg"></div>
	<div id="modal" class="panel panel-primary">
		<div id="modal-title" class="panel-heading">Information</div>
		<div class="panel-body">
			<div id="modal-content"></div>
		</div>
	</div>

	<div id="status-bar">Ready</div>

</body>
</html>