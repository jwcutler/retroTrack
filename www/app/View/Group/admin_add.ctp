<?php
$this->extend('/Common/admin_panel');

$this->start('panel_content');
?>
<h3>Create New Satellite Group</h3>
<?php if (empty($satellites)): ?>
	You can not create a new group because no satellites are currently configured. Go to the <a href="/admin/satellite/add" class="link">satellite creation page</a> to add one.
<?php else: ?>
	<script type="text/javascript">
	$().ready(function() {
		// Form validation
		var container = $("div.form_errors");
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
	To create a new satellite group, select all of the satellites you would like to have in the group below.
	<div class="form_errors">
        <ul>
        </ul>
	</div>
	<div style="width: 70%; margin-top: 10px;">
		<form action="<?php echo $this->Html->url(array('controller' => 'group', 'action' => 'add', 'admin' => true)); ?>" method="POST" class="form-horizontal" id="new_group_form">
			<div class="control-group">
				<label class="control-label" for="group_name">Group Name*</label>
				<div class="controls">
					<input type="text" name="group_name" id="group_name" /> 
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="group_description">Group Description</label>
				<div class="controls">
					<textarea name="group_description"></textarea>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="show_on_home">Show on Homepage</label>
				<div class="controls">
					<input type="checkbox" name="show_on_home" />
					<p class="help-block">If this is checked, the group and all of its satellites will be shown on the homepage tracker (regardless of their individual settings).</p>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="satellites">Satellites*</label>
				<div class="controls">
					<select name="satellites[]" multiple="multiple">
						<?php foreach($satellites as $satellite): ?>
							<option value="<?php echo $satellite['Satellite']['id']; ?>"><?php echo $satellite['Satellite']['name']; ?></option>
						<?php endforeach; ?>
					</select>
					<p class="help-block">Select the satellites that you want in the group. Press CTRL to select multiple satellites.</p>
				</div>
			</div>
			<button type="submit" class="btn btn-success">Add Satellite Group</button>
		</form>
	</div>
<?php endif; ?>

<?php
$this->end();
?>
