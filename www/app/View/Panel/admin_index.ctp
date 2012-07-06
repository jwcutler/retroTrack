<?php
$this->extend('/Common/admin_panel');

$this->start('panel_content');
?>
<h3>Tracker Configuration</h3>
<div style="padding-bottom: 10px;">Use the form below to configure how the satellite tracker script behaves.</div>
<form action="/admin/configuration" method="POST" class="form-horizontal">
	<div class="control-group">
		<label class="control-label" for="clock_period">Clock Period</label>
		<div class="controls">
			<input type="text" name="clock_period" value="<?php echo $clock_period['Configuration']['value']; ?>" />
			<input type="hidden" name="clock_period_id" value="<?php echo $clock_period['Configuration']['id']; ?>" />
			<p class="help-block">How frequently satellite positions should be recalculated.</p>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="map_update_period">Map Update Period</label>
		<div class="controls">
			<input type="text" name="map_update_period" value="<?php echo $map_update_period['Configuration']['value']; ?>" />
			<input type="hidden" name="map_update_period_id" value="<?php echo $map_update_period['Configuration']['id']; ?>" />
			<p class="help-block">How frequently the map should be redrawn.</p>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="default_ground_station">Default Ground Station</label>
		<div class="controls">
			<select name="default_ground_station">
				<?php foreach($stations as $station): ?>
					<option value="<?php echo $station['Station']['id']; ?>" <?php if($default_ground_station['Configuration']['value']==$station['Station']['id']){echo "selected='selected'";} ?>><?php echo $station['Station']['name']; ?></option>
				<?php endforeach;?>
			</select>
			<input type="hidden" name="default_ground_station_id" value="<?php echo $default_ground_station['Configuration']['id']; ?>" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="satellite_size">Satellite Size</label>
		<div class="controls">
			<input type="text" name="satellite_size" value="<?php echo $satellite_size['Configuration']['value']; ?>" />
			<input type="hidden" name="satellite_size_id" value="<?php echo $satellite_size['Configuration']['id']; ?>" />
			<p class="help-block">Edge length of satellite indicator.</p>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="satellite_color">Satellite Color</label>
		<div class="controls">
			<input type="text" name="satellite_color" value="<?php echo $satellite_color['Configuration']['value']; ?>" />
			<input type="hidden" name="satellite_color_id" value="<?php echo $satellite_color['Configuration']['id']; ?>" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="ground_station_color">Ground Station Color</label>
		<div class="controls">
			<input type="text" name="ground_station_color" value="<?php echo $ground_station_color['Configuration']['value']; ?>" />
			<input type="hidden" name="ground_station_color_id" value="<?php echo $ground_station_color['Configuration']['id']; ?>" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="eclipse_color">Eclipse Color</label>
		<div class="controls">
			<input type="text" name="eclipse_color" value="<?php echo $eclipse_color['Configuration']['value']; ?>" />
			<input type="hidden" name="eclipse_color_id" value="<?php echo $eclipse_color['Configuration']['id']; ?>" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="show_grid">Show Grid Overlay</label>
		<div class="controls">
			<input type="checkbox" name="show_grid" value="<?php echo $show_grid['Configuration']['value']; ?>" <?php if($show_grid['Configuration']['value']=='1'){echo 'checked="checked"';} ?> />
			<input type="hidden" name="show_grid_id" value="<?php echo $show_grid['Configuration']['id']; ?>" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="show_sun">Show Sun</label>
		<div class="controls">
			<input type="checkbox" name="show_sun" value="<?php echo $show_sun['Configuration']['value']; ?>" <?php if($show_sun['Configuration']['value']=='1'){echo 'checked="checked"';} ?> />
			<input type="hidden" name="show_sun_id" value="<?php echo $show_sun['Configuration']['id']; ?>" />
		</div>
	</div>
	<button type="submit" class="btn btn-success">Update Configuration</button>
</form>
<br />
<h3>TLE Management</h3>
TLE's specify the orbital parameters of satellites and are required to track the satellite.
<script type="text/javascript">
$(document).ready(function() {
	$("#update_tles").click(function(){
		// Disable the button
		$("#update_tles").attr('disabled', 'disabled');
		$("#update_tles").addClass('disabled');
		
		// Add the spinner
		$("#timestamp_container").hide();
		$("#spinner_container").css('visibility', 'visible');
		
		// Attempt to update the TLE's
		$.get('/admin/panel/tleupdate', function(data) {
			if (data.indexOf("okay") != -1){
				// Success, re-enable the button and refresh the page
				$("#update_tles").removeAttr('disabled');
				$("#update_tles").removeClass('disabled');
				$("#timestamp_container").show();
				$("#spinner_container").css('visibility', 'hidden');
				
				window.location.reload()
			} else {
				// Error, re-enable button
				$("#update_tles").removeAttr('disabled');
				$("#update_tles").removeClass('disabled');
				$("#timestamp_container").show();
				$("#spinner_container").css('visibility', 'hidden');
				
				$("#timestamp_container").html("There was an error updating the TLE's. Please try again.");
			}
		});
	});
	
	// Handle TLE detail expansions
	$('[id^="expand_tle_"]').click(function() {
		id = $(this).attr('title');
		
		// Toggle the box
		$("#expanded_tle_"+id).toggle();
		
		// Toggle the indicator
		curr_indicator = $(this).html();
		if (curr_indicator=="[+]"){
			$(this).html("[-]");
		} else {
			$(this).html("[+]");
		}
	});
});
</script>
<div style="padding: 10px 0px 10px 0px;">
	<button name="update_tles" id="update_tles" class="btn btn-primary">Manually Update TLE's</button>
	<div id="timestamp_container" style="font-style: italic;display: inline;">Last Updated: <?php echo $tle_last_update; ?></div>
	<div id="spinner_container" style="display: inline; visibility: hidden;position: relative; top: 5px;"><img src='/img/ajax_spinner_small.gif' alt='Currently Loading TLE\'s' /></div>
</div>
<?php if (empty($tles)): ?>
	No TLE's currently present in the database. Try updating.
<?php else: ?>
	<strong>Currently Loaded TLE's:</strong><br />
	<div style="font-size: 10px;">
		<?php foreach ($tles as $tle): ?>
			<a id="expand_tle_<?php echo $tle['Tle']['id']; ?>" title="<?php echo $tle['Tle']['id']; ?>" style="cursor: pointer; font-size: 8px;">[+]</a> <?php echo $tle['Tle']['name']; ?><br />
			<div id="expanded_tle_<?php echo $tle['Tle']['id']; ?>" class="tle_expansion">
				<?php echo $tle['Tle']['raw_l1']; ?><br />
				<?php echo $tle['Tle']['raw_l2']; ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
<?php
$this->end();
?>
