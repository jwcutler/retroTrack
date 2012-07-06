<?php
$this->extend('/Common/admin_panel');

$this->start('panel_content');
?>
<h3>Delete Group</h3>
Are you absolutely sure you want to delete the '<?php echo $group['Group']['name']; ?>' group?
<br /><br />
<form action="/admin/group/<?php echo $group['Group']['id']; ?>/delete" method="POST">
    <button type="submit" name="delete_confirmation" class="btn btn-danger">Yes, Delete Group</button>
    <a href="/admin/group" class="btn btn-primary">Nevermind</a>
</form>
<?php
$this->end();
?>
