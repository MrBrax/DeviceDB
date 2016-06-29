<?php

$id = $_GET['id'];


$main = $BraXuS->PDOFetch("SELECT * FROM devices WHERE id = :id", ["id" => $id ] );

if(!$main) die("No such device");

echo '<h1>' . $main["name"] . '</h1>';

echo '<h2>Repair log</h2>';
$repair_data = $BraXuS->PDOFetchAll("SELECT * FROM repairs WHERE device_id = :id", ["id" => $id ] );
foreach( $repair_data as $k => $v ){
	echo '<div>';
	echo "#" . $v["id"] . " - " . $v["description"];
	/*
	array_push($d["repairs"], [
		"id" => $v["id"],
		"description" => $v["description"],
		"date_created" => $v["date_created"],
		"date_start" => $v["date_start"],
		"date_end" => $v["date_end"]
	]);
	*/
	echo '</div>';
}




?>