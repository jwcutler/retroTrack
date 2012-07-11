<?php if(!empty($page_title)): ?>
    <div class="page_title"><?php echo $page_title; ?></div>
<?php endif; ?>
<?php echo $this->Html->script('retroTrack.js'); ?>
<?php echo $this->Html->script('retroTrack_interface.js'); ?>
<?php echo $this->Html->script('jquery-ui-1.8.21.custom.min.js'); ?>
<?php echo $this->Html->script('modernizr.custom.js'); ?>
<script type="text/javascript">
// Global Variables
var satellites = null;
var active_satellites = new Array();
var groups = null;
var stations = null;
var active_station = null;
var tles = null;
var configuration = null;

$().ready(function(){
    /*
    Initialize retroTrack
    */
	
	// Make sure canvas is supported
	$('#canvas_modal').modal({
        keyboard: false,
        backdrop: 'static',
        show: false
    });
	if (!Modernizr.canvas){
		$("#canvas_modal").modal('show');
	}
	
    // Initialize the loading progress modal
    $('#load_modal').modal({
        keyboard: false,
        backdrop: 'static',
        show: true
    });
    
    // Tracker Configuration
    $("#load_progress_message").html('Loading configuration.');
    satellites = jQuery.parseJSON('<?php echo $satellite_json; ?>'); // All satellites this page can display
    active_satellites = new Array(); // Array of the IDs of all active satellites (the IDs are also the indexes in satellites)
    groups = jQuery.parseJSON('<?php echo $group_json; ?>'); // All groups this page can display
    stations = jQuery.parseJSON('<?php echo $station_json; ?>'); // All ground stations this page can display
    active_station = null;
    tles = jQuery.parseJSON('<?php echo $tle_json; ?>');
    configuration = jQuery.parseJSON('<?php echo $configuration_json; ?>');
    $("#load_bar").css('width','20%');
    
    // Setup menus
    $("#load_progress_message").html('Setting up application menus.');
    populateSatellitesMenu(satellites, active_satellites);
    populateGroupsMenu(groups);
    populateStationsMenu(stations, active_station);
    populateOptionsMenu();
    initializeActiveSatellites();
    $("#load_bar").css('width', '50%');
    
    // Initialize retroTracker object
    $("#load_progress_message").html('Setting up retroTracker object.');
    $("#load_progress_message").html('Complete.');
    $("#load_bar").css('width','100%');
    
    // Hide the load progress modal
    $('#load_modal').modal('hide')
    
    // Create main program loops
    
});
</script>

<!-- START retroTrack Display -->
<div id="tracker_container">
    <!-- START top menu bar -->
    <div id="top_menu">
        <ul id="top_controls">
            <li><a id="show_menu_satellites" rel="menu_satellites">Satellites</a></li>
            <li><a id="show_menu_groups" rel="menu_groups">Satellite Groups</a></li>
            <li><a id="show_menu_options" rel="menu_options">Options</a></li>
        </ul>
    </div>
    <div id="menu_satellites" class="menu_pane">
        <div class="menu_pane_header">Select the satellites you would like to display. Use CTRL to select multiple satellites.</div>
        <ol id="satellite_list" class="menu_list"></ol>
    </div>
    <div id="menu_groups" class="menu_pane">
        <div class="menu_pane_header">Select the groups you would like to display. Use CTRL to select multiple groups.</div>
        <ol id="group_list" class="menu_list"></ol>
    </div>
    <div id="menu_options" class="menu_pane">
        <div class="menu_pane_header">Click on any of the options below to toggle them.</div>
        <ol id="option_list" class="menu_list">
            <li id="show_sun">Enable Sun</li>
            <li id="show_grid">Enable Grid</li>
        </ol>
    </div>
    <!-- END top menu bar -->
    
    <!-- START primary display canvas -->
    test
    <!-- END primary display canvas -->
    
    <!-- START bottom menu bar -->
    <div id="menu_stations" class="menu_pane">
        <div class="menu_pane_header">Select the ground stations you would like to display.</div>
        <ol id="station_list" class="menu_list"></ol>
    </div>
    <div id="bottom_menu">
        <ul id="bottom_controls">
            <li><a id="show_menu_stations" rel="menu_stations">Ground Stations</a></li>
        </ul>
    </div>
    <!-- END bottom menu bar -->
</div>
<!-- END retroTrack Display -->

<!-- START Loading Modal -->
<div class="modal hide" id="load_modal" style="width: 400px;margin-left: -200px;">
    <div class="modal-header">
        <h3>Initializing retroTrack</h3>
    </div>
    <div class="modal-body">
        retroTrack is currently being initialized. Please stand by.
        <div style="padding: 10px 0px 10px 0px;">
            <span style="font-style: italic;">Progress: </span> <span id="load_progress_message"></span>
        </div>
        <div class="progress progress-striped active">
            <div class="bar" id="load_bar" style="width: 0%;"></div>
        </div>
    </div>
</div>
<!-- END Loading Modal -->

<!-- START Canvas Support Modal -->
<div class="modal hide" id="canvas_modal" style="width: 400px;margin-left: -200px;display: none;">
    <div class="modal-header">
        <h3>Your browser does not support HTML5 canvas.</h3>
    </div>
    <div class="modal-body">
		<p>The browser you are currently using does not appear to support HTML5 canvas, which is required to render retroTracker. You may continue anyway, but be aware retroTrack may not behave as intended. We recommend switching to a more modern browser.</p>
		<center>
			<div class="browser_warning_box">
				<a href="https://www.google.com/intl/en/chrome/browser/" style="color: #666666;">
					<?php echo $this->Html->image('browser_chrome.gif'); ?><br />Google Chrome 4.0+
				</a>
			</div>
			<div class="browser_warning_box">
				<a href="http://www.mozilla.org/en-US/firefox/new/" style="color: #666666;">
					<?php echo $this->Html->image('browser_firefox.gif'); ?><br />Mozilla Firefox 2.0+
				</a>
			</div>
		</center>
    </div>
	<div class="modal-footer">
	<a href="#" class="btn" data-dismiss="modal">Continue Anyway</a>
</div>
</div>
<!-- END Canvas Support Modal -->

