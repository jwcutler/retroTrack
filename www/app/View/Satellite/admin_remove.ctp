<?php
$this->extend('/Common/admin_panel');

$this->start('panel_content');
?>
<h3>Delete Satellite</h3>
Are you absolutely sure you want to delete the '<?php echo $satellite['Satellite']['name']; ?>' satellite?
<br /><br />
<form action="/admin/satellite/<?php echo $satellite['Satellite']['id']; ?>/delete" method="POST">
    <button type="submit" name="delete_confirmation" class="btn btn-danger">Yes, Delete Satellite</button>
    <a href="/admin/satellite" class="btn btn-primary">Nevermind</a>
</form>
<?php
$this->end();
?>
