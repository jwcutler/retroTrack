<?php
$this->extend('/Common/admin_panel');

$this->start('panel_content');
?>
<h3>Tracker Configuration</h3>
test
<br /><br />
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
<div style="padding: 10px;">
	<button name="update_tles" id="update_tles" class="btn btn-primary">Manually Update TLE's</button>
	<div id="timestamp_container" style="font-style: italic;display: inline;">Last Updated: <?php echo $tle_last_update; ?></div>
	<div id="spinner_container" style="display: inline; visibility: hidden;"><img src='/img/ajax_spinner_small.gif' alt='Currently Loading TLE\'s' /></div>
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
