<?php
$this->extend('/Common/admin_panel');

$this->start('panel_content');
?>
<h3>Create New Ground Station</h3>
To create a new ground station, enter its name and location below.
<script type="text/javascript">
$().ready(function() {
    // Form validation
    var container = $("div.form_errors");
    $("#new_station_form").validate({
        errorContainer: container,
        errorLabelContainer: $("ul", container),
        errorClass: "form_error",
        wrapper: 'li',
        rules: {
            station_name: {
                required: true
            },
            station_longitude: {
                required: true
            },
            station_latitude: {
                required: true
            }
        },
        messages: {
            station_name: {
                required: "Please enter a name for the ground station."
            },
            station_longitude: {
                required: "Please enter the ground station's longitude."
            },
            station_latitude: {
                required: "Please enter the ground stations' latitude."
            }
        }
    });
});
</script>
<div class="form_errors">
    <ul>
    </ul>
</div>
<div style="width: 70%; margin-top: 10px;">
    <form action="<?php echo $this->Html->url(array('controller' => 'station', 'action' => 'add', 'admin' => true)); ?>" method="POST" class="form-horizontal" id="new_station_form">
        <div class="control-group">
            <label class="control-label" for="station_name">Ground Station Name*</label>
            <div class="controls">
                <input type="text" name="station_name" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="station_description">Station Description</label>
            <div class="controls">
                <textarea name="station_description"></textarea>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="station_longitude">Longitude*</label>
            <div class="controls">
                <input type="text" name="station_longitude" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="station_latitude">Latitude*</label>
            <div class="controls">
                <input type="text" name="station_latitude" />
            </div>
        </div>
        <button type="submit" class="btn btn-success">Add Ground Station</button>
    </form>
</div>
<?php
$this->end();
?>

