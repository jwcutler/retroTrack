<?php
$this->extend('/Common/admin_panel');

$this->start('panel_content');
?>
<h3>Delete Ground Station</h3>
Are you absolutely sure you want to delete the '<?php echo $station['Station']['name']; ?>' ground station?
<br /><br />
<form action="/admin/station/<?php echo $station['Station']['id']; ?>/delete" method="POST">
    <button type="submit" name="delete_confirmation" class="btn btn-danger">Yes, Delete Ground Station</button>
    <a href="/admin/station" class="btn btn-primary">Nevermind</a>
</form>
<?php
$this->end();
?>
