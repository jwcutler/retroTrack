<?php
$this->extend('/Common/admin_panel');

$this->start('panel_content');
?>
<script type="text/javascript">
var groups_json = jQuery.parseJSON('<?php echo $groups_json; ?>');
    
$(document).ready(function(){
  // Detect group selection 
  $("[name^=groups]").change(function(){
    if ($(this).is(":checked")){
      group_id = $(this).attr('rel');
            
	    // Select all satellites in the group
	    for (group_index in groups_json[group_id]['satellites']){
        $("[name='satellites["+groups_json[group_id]['satellites'][group_index]['id']+"]']").prop('checked', true);
	    }
    }
  });
});
</script>
<h3>Static Version Exporter</h3>
<?php if (empty($satellites)): ?>
  No satellites are currently configured, unable to generate static version to export. Please try adding some satellites.
<?php else: ?>
  To create a new static instance of retroTracker, select which groups and satellites you would like to include below.

  <div style="width: 70%; margin-top: 10px;">
    <form action="<?php echo $this->Html->url(array('controller' => 'export', 'action' => 'generate', 'admin' => true)); ?>" method="POST" class="form-horizontal">
      <div class="control-group">
        <label class="control-label">TLE URL Base</label>
        <div class="controls">
          <input type="text" name="tle_base_path" value="http://fetchtle.local/api/satellites/" />
          <p class="help-block">The base URL to use when getting TLE's via Ajax.</p>
        </div>
      </div>
      <div class="control-group">
        <label class="control-label">Groups</label>
        <div class="controls">
          <?php foreach ($groups as $group): ?>
            <label class="checkbox">
              <input type="checkbox" name="groups[<?php echo $group['Group']['id']; ?>]" rel="<?php echo $group['Group']['id']; ?>" value="<?php echo $group['Group']['name']; ?>">
              <?php echo $group['Group']['name']; ?>
            </label>
          <?php endforeach; ?>
          <p class="help-block">Select the groups you would like to include in the static version.</p>
        </div>
      </div>
      <div class="control-group">
        <label class="control-label">Satellites</label>
        <div class="controls">
          <?php foreach ($satellites as $satellite): ?>
            <label class="checkbox">
              <input type="checkbox" name="satellites[<?php echo $satellite['Satellite']['id']; ?>]" value="<?php echo $satellite['Satellite']['name']; ?>">
              <?php echo $satellite['Satellite']['name']; ?>
            </label>
          <?php endforeach; ?>
          <p class="help-block">Select the satellites you would like to include in the static version.</p>
        </div>
      </div>
      <div class="control-group">
        <label class="control-label">Ground Stations</label>
        <div class="controls">
          <?php foreach ($stations as $station): ?>
            <label class="checkbox">
              <input type="checkbox" checked="checked" name="stations[<?php echo $station['Station']['id']; ?>]" value="<?php echo $station['Station']['name']; ?>">
              <?php echo $station['Station']['name']; ?>
            </label>
          <?php endforeach; ?>
          <p class="help-block">Select the ground stations you would like to include in the static version.</p>
        </div>
      </div>
      <button type="submit" class="btn btn-success">Generate Static Tracker</button>
    </form>
  </div>
<?php endif; ?>
<?php
$this->end();
?>
