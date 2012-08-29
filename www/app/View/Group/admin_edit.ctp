<?php
$this->extend('/Common/admin_panel');

$this->start('panel_content');
?>
<?php echo $this->Html->script('chosen.jquery.min.js'); ?>
<?php echo $this->Html->css('chosen.css'); ?>
<h3>Edit Satellite Group '<?php echo $group['Group']['name']; ?>'</h3>
<script type="text/javascript">
$(document).ready(function() {
    // Set up select box
    $("#satellites").chosen();

    // Form validation
    var container = $("div.form_errors");
    jQuery.validator.setDefaults({ 
        ignore: ".hidden"
    });
    $("#new_group_form").validate({
	errorContainer: container,
	errorLabelContainer: $("ul", container),
	errorClass: "form_error",
	wrapper: 'li',
	rules: {
	    group_name: {
		required: true
	    },
	    "satellites[]": {
		required: true
	    }
	},
	messages: {
	    group_name: {
		required: "Please enter a group name."
	    },
	    "satellites[]": {
		required: "Please select at least one satellite."
	    }
	}
    });
});
</script>
You are now editing the '<?php echo $group['Group']['name']; ?>' satellite group.
<div class="form_errors">
	<ul>
	</ul>
</div>
<div style="width: 70%; margin-top: 10px;">
    <form action="<?php echo $this->webroot; ?>admin/group/<?php echo $group['Group']['id']; ?>/edit" method="POST" class="form-horizontal" id="new_group_form">
	<div class="control-group">
	    <label class="control-label" for="group_name">Group Name*</label>
	    <div class="controls">
		<input type="text" name="group_name" id="group_name" value="<?php echo $group['Group']['name']; ?>" /> 
	    </div>
	</div>
	<div class="control-group">
	    <label class="control-label" for="satellites">Satellites*</label>
	    <div class="controls">
		<select name="satellites[]" multiple="multiple" style="width: 350px;" id="satellites">
		    <?php foreach($satellites as $satellite): ?>
			<option value="<?php echo $satellite['Satellite']['id']; ?>" <?php if(in_array($satellite['Satellite']['id'], $group_satellites)){echo 'selected="selected"';} ?>><?php echo $satellite['Satellite']['name']; ?></option>
		    <?php endforeach; ?>
		</select>
		<p class="help-block">Select the satellites that you want in the group. Press CTRL to select multiple satellites.</p>
	    </div>
	</div>
	<div class="control-group">
	    <label class="control-label" for="group_description">Group Description</label>
	    <div class="controls">
		<textarea name="group_description"><?php echo $group['Group']['description']; ?></textarea>
	    </div>
	</div>
	<div class="control-group">
	    <label class="control-label" for="show_on_home">Show on Homepage</label>
	    <div class="controls">
		<input type="checkbox" name="show_on_home" <?php if($group['Group']['show_on_home']=='1'){echo "checked='checked'";} ?>/>
		<p class="help-block">If this is checked, the group and all of its satellites (regardless of their individual settings) will be shown on the homepage tracker.</p>
	    </div>
	</div>
	<div class="control-group">
	    <label class="control-label" for="default_on_home">Default on Homepage</label>
	    <div class="controls">
		<input type="checkbox" name="default_on_home" <?php if($group['Group']['default_on_home']=='1'){echo "checked='checked'";} ?>/>
		<p class="help-block">If this is checked, the group and all of its satellites (regardless of their individual settings) will be automatically selected on the homepage.</p>
	    </div>
	</div>
	<div class="control-group">
            <label class="control-label">Static Link</label>
            <div class="controls">
                <?php echo $this->Html->link('View Group', array('controller' => 'display', 'action' => 'group_display', urlencode($group['Group']['name']), 'admin' => false), array('class'=>'link', 'target'=>'_blank')); ?>
                <p class="help-block">Use this to link directly to the group tracker page.</p>
            </div>
        </div>
	<button type="submit" class="btn btn-success">Edit Satellite Group</button>
	<a href="<?php echo $this->webroot; ?>admin/group/<?php echo $group['Group']['id']; ?>/delete" class="btn btn-danger">Delete Satellite Group</a>
	</form>
</div>
<?php
$this->end();
?>
