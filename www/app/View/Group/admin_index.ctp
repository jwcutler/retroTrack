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
		<th width="17%">Name</th>
		<th width="23%">Description</th>
		<th width="20%">On Homepage</th>
		<th width="20%">Satellites</th>
                <th width="20%">Actions</th>
	    </tr>
	</thead>
	<tbody>
	    <?php foreach($groups as $group): ?>
		<tr>
		    <td colspan='1'><?php echo $group['Group']['name']; ?></td>
		    <td colspan='1'><?php echo $group['Group']['description']; ?></td>
		    <td colspan='1'>
			<?php if($group['Group']['show_on_home']=='1'): ?>
			    Yes
			<?php else: ?>
			    No
			<?php endif; ?>
		    </td>
                    <td colspan='1' style="font-size: 8px;line-height: 1.2;">
                        <?php $group_string = ""; ?>
                        <?php foreach($group['Satellite'] as $satellite): ?>
                            <?php echo $satellite['name'].'<br />'; ?>
                        <?php endforeach; ?>
                    </td>
		    <td colspan='1'><a href="<?php echo $this->webroot; ?>admin/group/<?php echo $group['Group']['id']; ?>/delete" class="btn btn-mini btn-primary">Delete</a> <a href="<?php echo $this->webroot; ?>admin/group/<?php echo $group['Group']['id']; ?>/edit" class="btn btn-mini btn-primary">Edit</a></td>
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
