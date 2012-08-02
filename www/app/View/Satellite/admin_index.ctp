<?php
$this->extend('/Common/admin_panel');

$this->start('panel_content');
?>
<h3>Satellite Management</h3>
<?php if (empty($satellites)): ?>
	No satellites are currently configured. Try adding some.
<?php else: ?>
	All currently configured satellites are displayed below.
	<table class="table table-condensed" width="100%">
		<thead>
			<tr>
				<th width="26%">Name</th>
				<th width="34%">Description</th>
				<th width="20%">On Homepage</th>
				<th width="20%">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($satellites as $satellite): ?>
				<tr>
					<td width="26%"><?php echo $satellite['Satellite']['name']; ?></td>
					<td width="34%"><?php echo $satellite['Satellite']['description']; ?></td>
					<td width="20%">
						<?php if ($satellite['Satellite']['show_on_home']=='1'): ?>
							Yes
						<?php else: ?>
							<?php
							$show_on_home = false;
							foreach($satellite['Group'] as $temp_group){
								// Check if the group is on the homepage
								if ($temp_group['show_on_home']){
									$show_on_home = true;
									break;
								}
							}
							?>
							<?php if ($show_on_home): ?>
								Yes (via group)
							<?php else: ?>
								No
							<?php endif; ?>
						<?php endif; ?>
					</td>
					<td width="20%"><a href="<?php echo $this->webroot; ?>admin/satellite/<?php echo $satellite['Satellite']['id']; ?>/delete" class="btn btn-mini btn-primary">Delete</a> <a href="<?php echo $this->webroot; ?>admin/satellite/<?php echo $satellite['Satellite']['id']; ?>/edit" class="btn btn-mini btn-primary">Edit</a></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
<div style="padding-top: 5px;">
	<a href="<?php echo $this->Html->url(array('controller' => 'satellite', 'action' => 'add', 'admin' => true)); ?>" class="btn btn-primary">Add New Satellite</a>
</div>
<?php
$this->end();
?>
