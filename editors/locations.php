<?php

function lTree($tree, $parent){

	global $main, $depth;

	$tree2 = [];

	foreach($tree as $i => $item){
		if($item["parent"] == $parent){
			//$d = "";
			//$c = "└";
			//if($depth > 0) $d = str_repeat("&nbsp;&nbsp;", $depth) . $c;

			echo "<div style='border: 2px solid rgb(178, 222, 178); margin-bottom: 10px'>";

			echo "<div style='padding:5px; background-color:rgb(178, 222, 178)'>" . $item["name"] . "</div>";

			echo "<div style='padding:5px 5px 0 15px; background-color:#fff'>";
			
			$depth++;
			$tree2[$item['id']] = $item;
			$tree2[$item['id']]['submenu'] = lTree($tree, $item['id']);
			$depth--;

			echo "<form data-modular='true' style='margin-bottom:10px' method='post' action='?new=location'><input type='hidden' name='parent' value='" . $item["id"] . "'><input type='text' name='name'><input type='submit' value='+'></form>";

			echo "</div>";

			echo "</div>";

		}
	}

	return $tree2;

}

$location_p = $BraXuS->PDOFetchAll("SELECT id, name, parent FROM locations ORDER BY name");

lTree($location_p, 0);

echo "<form data-modular='true' style='margin-bottom:10px' method='post' action='?new=location'><input type='hidden' name='parent' value='0'><input type='text' name='name'><input type='submit' value='+'></form>";

?>
<script type="text/javascript">
	
	$("form[data-modular]").submit(function(e){

		$("#modal-content").html("Loading...");

		$.post( $(this).attr("action"), $(this).serialize(), function(data){
			$("#modal-content").html(data);
		});

		e.preventDefault();

	});

</script>