<?php
$this->extend('/Common/admin_panel');

$this->start('panel_content');
?>
<h3>Ground Station Management</h3>
<?php if (empty($stations)): ?>
    No ground stations are currently configured. Try adding one.
<?php else: ?>
    All currently configured ground stations are displayed below.
    <table class="table" width="100%">
	<thead>
	    <tr>
		<th width="20%">Name</th>
		<th width="30%">Description</th>
		<th width="25%">Location</th>
		<th width="25%">Actions</th>
	    </tr>
	</thead>
	<tbody>
	    <?php foreach($stations as $station): ?>
		<tr>
		    <td width="20%"><?php echo $station['Station']['name']; ?></td>
		    <td width="30%"><?php echo $station['Station']['description']; ?></td>
		    <td width="25%">
			Long.: <?php echo $station['Station']['longitude']; ?><br />
			Lat.: <?php echo $station['Station']['latitude']; ?><br />
			<a href="https://maps.google.com/maps?q=<?php echo $station['Station']['latitude']; ?>,<?php echo $station['Station']['longitude']; ?>" target="_blank" class="link">View Map</a>
		    </td>
		    <td width="25%"><a href="/admin/station/<?php echo $station['Station']['id']; ?>/delete" class="btn btn-mini btn-primary">Delete</a> <a href="/admin/station/<?php echo $station['Station']['id']; ?>/edit" class="btn btn-mini btn-primary">Edit</a></td>
		</tr>
	    <?php endforeach; ?>
	</tbody>
    </table>
<?php endif; ?>
<div style="padding-top: 5px;">
    <a href="/admin/station/add" class="btn btn-primary">Add New Ground Station</a>
</div>
<?php
$this->end();
?>
