<?php
$this->extend('/Common/admin_panel');

$this->start('panel_content');
?>
<h3>Satellite Group Management</h3>
<?php if (empty($groups)): ?>
    No groups are currently configured. Try adding some.
<?php else: ?>
    All currently configured satellite groups are displayed below.
    <table class="table table-condensed" width="100%">
	<thead>
	    <tr>
		<th width="20%">Name</th>
		<th width="30%">Description</th>
		<th width="25%">Satellites</th>
                <th width="25%">Actions</th>
	    </tr>
	</thead>
	<tbody>
	    <?php foreach($groups as $group): ?>
		<tr>
		    <td width="20%"><?php echo $group['Group']['name']; ?></td>
		    <td width="30%"><?php echo $group['Group']['description']; ?></td>
                    <td width="25%">
                        <?php $group_string = ""; ?>
                        <?php foreach($group['Satellite'] as $satellite): ?>
                            <?php $group_string .= $satellite['name'].', '; ?>
                        <?php endforeach; ?>
                        <?php echo substr($group_string, 0, -2); ?>
                    </td>
		    <td width="25%"><a href="<?php echo $this->webroot; ?>admin/group/<?php echo $group['Group']['id']; ?>/delete" class="btn btn-mini btn-primary">Delete</a> <a href="<?php echo $this->webroot; ?>admin/group/<?php echo $group['Group']['id']; ?>/edit" class="btn btn-mini btn-primary">Edit</a></td>
		</tr>
	    <?php endforeach; ?>
	</tbody>
    </table>
<?php endif; ?>
<div style="padding-top: 5px;">
    <a href="<?php echo $this->Html->url(array('controller' => 'group', 'action' => 'add', 'admin' => true)); ?>" class="btn btn-primary">Add New Group</a>
</div>
<?php
$this->end();
?>
