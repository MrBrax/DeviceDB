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

	// deprecated

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
		"a" 	=> [ "col" => "acd", 			"name" => "Active Directory", 	"row" => "acd" ], 
		"s" 	=> [ "col" => "storage", 		"name" => "Storage", 			"row" => "storage" ],
		"o" 	=> [ "col" => "outside", 		"name" => "Outside",			"row" => "outside" ],
		"n" 	=> [ "col" => "needs_repair", 	"name" => "Needs repair",		"row" => "needs_repair" ],
		"r" 	=> [ "col" => "repairing", 		"name" => "Repairing",			"row" => "repairing" ],
		"b" 	=> [ "col" => "byod", 			"name" => "BYOD",				"row" => "byod" ],
		"p" 	=> [ "col" => "public", 		"name" => "Public",				"row" => "public"],
		"t" 	=> [ "col" => "travel", 		"name" => "Travel",				"row" => "travel" ],
		"d" 	=> [ "col" => "dyslexia", 		"name" => "Dyslexia",			"row" => "dyslexia" ],
		"rs" 	=> [ "col" => "resigned", 		"name" => "Resigned",			"row" => "resigned" ],
	];

	// filter query

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
		if($device_flags_sql[$filter_flag]){

			// $eq .= " AND " . htmlentities( $flags[ $filter_flag ] ) . " = 1";

		}
	}

	$eq .= " AND deleted = 0";

	$missing_serial = 0;
	$missing_location = 0;
	$missing_model = 0;

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
				if( $type_data["no_owner"] ) $d["no_owner"] = true;
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

		$ownerfull_data = $BraXuS->PDOFetchAll("SELECT * FROM owners_date WHERE device_id = :id ORDER BY date_leave, date_aquired", ["id" => $d["id"] ] );
		if($ownerfull_data){
			$d["owners"] = $ownerfull_data;
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

		// device flags
		$d["flags"] = [];
		$flags_data = $BraXuS->PDOFetchAll("SELECT * FROM device_flags WHERE device_id = :id", ["id" => $d["id"] ] );
		foreach($flags_data as $k => $v){
			$fd = $device_flags_sql[ $v["flag_id"] ];
			$d["flags"][ $v["flag_id"] ] = $v["flag_value"];
		}

		// repair data
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

		// extra parts
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
			if($d["owners"]){		
				//

				foreach($d["owners"] as $i => $o){
					$owner_data = $BraXuS->PDOFetch("SELECT * FROM owners WHERE id = :id", ["id" => $o["owner_id"] ] );
					$o["date_aquired"] = substr($o["date_aquired"], 0, 4) == "0000" ? "" : $o["date_aquired"];
					$o["date_leave"] = substr($o["date_leave"], 0, 4) == "0000" ? "" : $o["date_leave"];
					
					if( time() < strtotime($o["date_aquired"]) ){
						$sheet->setCellValue("F" . $row, "[W] " . $owner_data["firstname"] . " " . $owner_data["lastname"]);
						continue;
					}

					if( $o["date_leave"] && time() > strtotime($o["date_leave"]) ){
						continue;
					}

					$sheet->setCellValue("F" . $row, $owner_data["firstname"] . " " . $owner_data["lastname"]);

					break;

				}


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
		<link href="/css/font-awesome.min.css" rel="stylesheet">

		<script type="text/javascript">
			<?php
				echo 'var isDevicePage = ' . ( $filter_device ? 'true' : 'false' ) . ';';
			?>
		
		</script>

	</head>
<body>
	<div class="header">
		<h1><span class="glyphicon glyphicon-tasks" aria-hidden="true"></span><a href="/ddb/" id="title">DDB</a></h1>
	</div>
	<div class="content">
		<div>

			<?php

				if(sizeof($entries) == 1){

					include "inc/item_single.php";

				}else{

					include "inc/item_list.php";

				}
			?>

		</div>

	</div>

	<div class="sidebar">

		<?php include "inc/sidebar.php"; ?>

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