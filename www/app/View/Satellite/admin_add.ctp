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
	<form action="<?php echo $this->Html->url(array('controller' => 'satellite', 'action' => 'add', 'admin' => true)); ?>" method="POST" class="form-horizontal">
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
	    <div class="control-group">
		<label class="control-label" for="show_on_home">Show on Homepage</label>
		<div class="controls">
		    <input type="checkbox" name="show_on_home" />
		    <p class="help-block">If this is checked, the satellite will be displayed on the main homepage tracker. Note the satellite will also be shown if any of its groups are on the homepage.</p>
		</div>
	    </div>
	    <button type="submit" class="btn btn-success">Add Satellite</button>
	</form>
    </div>
<?php endif; ?>
<?php
$this->end();
?>
