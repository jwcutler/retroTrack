<?php
$this->extend('/Common/admin_panel');

$this->start('panel_content');
?>
<h3>Satellite Management</h3>
<?php if (empty($satellites)): ?>
	No satellites are currently configured. Try adding some.
<?php else: ?>
	All currently configured satellites are displayed below.
	<table class="table" width="100%">
		<thead>
			<tr>
				<th width="26%">Name</th>
				<th width="37%">Description</th>
				<th width="37%">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($satellites as $satellite): ?>
				<tr>
					<td width="26%"><?php echo $satellite['Satellite']['name']; ?></td>
					<td width="37%"><?php echo $satellite['Satellite']['description']; ?></td>
					<td width="37%"><a href="/admin/satellite/<?php echo $satellite['Satellite']['id']; ?>/delete" class="btn btn-mini btn-primary">Delete</a> <a href="/admin/satellite/<?php echo $satellite['Satellite']['id']; ?>/edit" class="btn btn-mini btn-primary">Edit</a></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
<div style="padding-top: 5px;">
	<a href="/admin/satellite/add" class="btn btn-primary">Add New Satellite</a>
</div>
<?php
$this->end();
?>
