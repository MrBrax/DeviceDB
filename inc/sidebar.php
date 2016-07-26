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
			foreach($device_flags_sql as $k => $v){
				echo "<a href='" . build_url("flag", $k) . "' class='flag-" . $v["short"] . "' title='" . $v["name"] . "'>" . strtoupper( $v["short"] ) . "</a>";
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
		<h4>Missing</h4>
		<table width="100%">
			<tr><td>Serial</td><td><?=$missing_serial?></td></tr>
			<tr><td>Model</td><td><?=$missing_model?></td></tr>
			<tr><td>Location</td><td><?=$missing_location?></td></tr>
		</table>
		<a href="<?=build_url("export","1")?>">Export view</a>
	</div>
</div>

<br><br>