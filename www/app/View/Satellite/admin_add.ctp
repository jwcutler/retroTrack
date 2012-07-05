<?php
$this->extend('/Common/admin_panel');

$this->start('panel_content');
?>
<h3>Create New Satellite</h3>
<?php if (empty($tle_names)): ?>
	A new satellite can not be added because no TLE's are configured. <a href="/admin" class="link">Import TLE's Here</a>.
<?php else: ?>
	To create a new satellite, select the desired TLE entry from the list.
	<div style="width: 70%; margin-top: 10px;">
		<form action="/admin/satellite/add" method="POST" class="form-horizontal">
			<div class="control-group">
				<label class="control-label" for="satellite_name">Satellite Name</label>
				<div class="controls">
					<select name="satellite_name">
						<?php foreach ($tle_names as $tle_name): ?>
							<option value="<?php echo $tle_name['Tle']['name'] ?>"><?php echo $tle_name['Tle']['name'] ?></option>
						<?php endforeach; ?>
					</select> 
					<p class="help-block">These are the names of available satellites as specified by the TLE's.</p>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="satellite_description">Satellite Description</label>
				<div class="controls">
					<textarea name="satellite_description"></textarea>
				</div>
			</div>
			<button type="submit" class="btn btn-success">Add Satellite</button>
		</form>
	</div>
<?php endif; ?>

<?php
$this->end();
?>
