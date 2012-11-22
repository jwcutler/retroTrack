/*
This file contains an anonymous function that is responsible for setting up and running a retroTrack 
instance with the attributes provided by DisplayController.
*/

(function() {
  // Setup global variables
  var $; // Used to store local jQuery instance
  var satellites = null;
  var active_satellites = new Array();
  var groups = null;
  var stations = null;
  var active_stations = new Array();
  var active_station = null;
  var tles = null;
  var configuration = null;
  var selected_satellite = null;
  var selected_station = null;
  var background_image_path = null;
  var default_elements = null;
  
  // Check if an appropriate version of jQuery is loaded on the page. If not, load our own.
  if (window.jQuery === undefined || window.jQuery.fn.jquery !== '1.7.2') {
    // Need to load our own instance of jQuery
    var jquery_tag = document.createElement('script');
    jquery_tag.setAttribute("type", "text/javascript");
    jquery_tag.setAttribute("src", "http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js");
    
    // Wait for the script to load
    if (jquery_tag.readyState){
      // For older versions of IE
      jquery_tag.onreadystatechange = function () {
        if (this.readyState == 'complete' || this.readyState == 'loaded') {
          jquery_loaded();
        }
      };
    } else {
      jquery_tag.onload = jquery_loaded;
    }
    
    // Try to find the head, otherwise default to the documentElement
    (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(jquery_tag);
  } else {
    // jQuery all ready loaded on page will suffice
    jQuery = window.jQuery;
    load_libraries();
  }

  // Gets called when jQuery is loaded
  function jquery_loaded() {
    // Restore the page's $ and window.jQuery and save our instance of jQuery
    $ = window.jQuery.noConflict(true);
    jQuery = $;
    
    // jQuery loaded, load the required external libraries
    load_libraries();
  }
  
  // Loads required external libraries
  function load_libraries(){
    // Load external javascript
    $.when(
      $.getScript("<?php echo Router::url('/', true); ?>js/modernizr.custom.js"),
      $.getScript("<?php echo Router::url('/', true); ?>js/jquery-ui-1.9.1.custom.min.js"),
      $.getScript("<?php echo Router::url('/', true); ?>js/chosen.jquery.min.js")
    ).done(function(){
      // External scripts loaded, setup retroTrack
      start_retroTrack();
    });
  }
  
  /*
  Load the retroTrack interface Javascript. This code is responsible for managing the retroTrack 
  interface (menus, clocks, etc.)
  */
  <?php 
  $default_elements = (isset($default_elements))?$default_elements:'';
  
  echo $this->element('retrotrack_javascript', array(
    "satellite_json" => $satellite_json,
    "group_json" => $group_json,
    "station_json" => $station_json,
    "default_elements" => $default_elements,
    "tle_json" => $tle_json,
    "configuration_json" => $configuration_json
  ));
  ?>
  
  // Extend retroTrack_interface by adding function to build required base HTML
  retroTrack_interface.populateInterface = function(){
    // Load the retroTrack CSS
    var rt_css = $("<link>", { 
      rel: "stylesheet", 
      type: "text/css", 
      href: "<?php echo Router::url('/', true); ?>css/retrotrack_display.css" 
    });
    rt_css.appendTo('head');
    
    // Load the jQuery Chosen CSS
    var chosen_css = $("<link>", { 
      rel: "stylesheet", 
      type: "text/css", 
      href: "<?php echo Router::url('/', true); ?>css/chosen.css" 
    });
    chosen_css.appendTo('head');
    
    // Load the jQuery UI CSS
    var jquery_ui_css = $("<link>", { 
      rel: "stylesheet", 
      type: "text/css", 
      href: "<?php echo Router::url('/', true); ?>css/jquery-ui-1.9.1.custom.min.css" 
    });
    jquery_ui_css.appendTo('head');
    
    // Create the main retroTrack container
    var retroTrack_embed = $("#retroTrack_embed");
    var rt_tracker_container = $("<div id='rt_tracker_container'></div>");
    retroTrack_embed.append(rt_tracker_container);
    
    // Build the top menu bar
    var rt_top_menu = $("<div id='rt_top_menu'></div>");
    
    var rt_top_menu_float_left = $("<div style='float:left;'></div>");
    var rt_top_controls = $("<ul id='rt_top_controls'></ul>");
    rt_top_controls.append("<li><a id='rt_show_menu_satellites' rel='rt_menu_satellites'>Satellites</a></li>");
    rt_top_controls.append("<li><a id='rt_show_menu_groups' rel='rt_menu_groups'>Satellite Groups</a></li>");
    rt_top_controls.append("<li><a id='rt_show_menu_options' rel='rt_menu_options'>Options</a></li>");
    rt_top_menu_float_left.append(rt_top_controls);
    rt_top_menu.append(rt_top_menu_float_left);
    
    var rt_top_menu_float_right = $("<div style='float:right;'></div>");
    var rt_satellite_parameters = $("<ol id='rt_satellite_parameters'></ol>");
    rt_top_menu_float_right.append(rt_satellite_parameters);
    rt_top_menu.append(rt_top_menu_float_right);
    
    rt_top_menu.append("<div style='clear:both;'></div>");
    
    rt_tracker_container.append(rt_top_menu);
    
    // Build the top menu panels
    var rt_menu_satellites = $("<div id='rt_menu_satellites' class='rt_menu_pane'></div>");
    rt_menu_satellites.append("<div class='rt_menu_pane_header'>Select the satellites you would like to display.</div>");
    rt_menu_satellites.append("<select name='rt_satellite_list' multiple='multiple' id='rt_satellite_list' data-placeholder='Select some satellites' style='width: 835px;'></select>");
    rt_menu_satellites.append("<div style='clear:both;'></div>");
    rt_tracker_container.append(rt_menu_satellites);
    
    var rt_menu_groups = $("<div id='rt_menu_groups' class='rt_menu_pane'></div>");
    rt_menu_groups.append("<div class='rt_menu_pane_header'>Select the groups you would like to display.</div>");
    rt_menu_groups.append("<select name='rt_group_list' multiple='multiple' id='rt_group_list' data-placeholder='Select some satellite groups' style='width: 835px;'></select>");
    rt_menu_groups.append("<div style='clear:both;'></div>");
    rt_tracker_container.append(rt_menu_groups);
    
    var rt_menu_options = $("<div id='rt_menu_options' class='rt_menu_pane'></div>");
    rt_menu_options.append("<div class='rt_menu_pane_header'>Click on any of the options below to toggle them.</div>");
    var rt_option_list = $("<ol id='rt_option_list' class='rt_menu_list'></ol>");
    rt_option_list.append("<li id='rt_show_sun'>Disable Sun</li>");
    rt_option_list.append("<li id='rt_show_grid'>Disable Grid</li>");
    rt_option_list.append("<li id='rt_show_satellite_names'>Hide Satellite Names</li>");
    rt_option_list.append("<li id='rt_show_path'>Hide Satellite Path</li>");
    rt_option_list.append("<li id='rt_show_satellite_footprint'>Hide Satellite Footprint</li>");
    rt_option_list.append("<li id='rt_show_station_footprint'>Hide Station Footprint</li>");
    rt_option_list.append("<li id='rt_show_station_names'>Hide Station Names</li>");
    rt_menu_options.append(rt_option_list);
    rt_menu_options.append("<div style='clear:both;'></div>");
    rt_tracker_container.append(rt_menu_options);
    
    // Add the canvas display
    rt_tracker_container.append("<canvas id='rt_tracker_canvas' width='860px' height='430px' style='border: 1px solid #071831;border-width: 0px 1px 0px 1px;display: block;'></canvas>");
    
    // Build the bottom menu panels
    var rt_menu_stations = $("<div id='rt_menu_stations' class='rt_menu_pane'></div>");
    rt_menu_stations.append("<div class='rt_menu_pane_header'>Select the ground stations you would like to display.</div>");
    rt_menu_stations.append("<select name='rt_station_list' multiple='multiple' id='rt_station_list' data-placeholder='Select some ground stations' style='width: 835px;'></select>");
    rt_menu_stations.append("<div style='clear:both;'></div>");
    rt_tracker_container.append(rt_menu_stations);
    
    // Build the bottom menu bar
    var rt_bottom_menu = $("<div id='rt_bottom_menu'></div>");
    
    var rt_bottom_menu_float_left = $("<div style='float: left;'></div>");
    var rt_bottom_controls = $("<ul id='rt_bottom_controls'></ul>");
    rt_bottom_controls.append("<li><a id='rt_show_menu_stations' rel='rt_menu_stations'>Ground Stations</a></li>");
    rt_bottom_menu_float_left.append(rt_bottom_controls);
    rt_bottom_menu.append(rt_bottom_menu_float_left);
    
    var rt_bottom_menu_float_left_2 = $("<div style='float: left; margin-left: 20px;'></div>");
    rt_bottom_menu_float_left_2.append("<ol id='rt_station_parameters'></ol>");
    rt_bottom_menu.append(rt_bottom_menu_float_left_2);
    
    var rt_bottom_menu_float_right = $("<div style='float: right;'></div>");
    rt_bottom_menu_float_right.append("<div id='rt_top_clock'>-</div>");
    rt_bottom_menu.append(rt_bottom_menu_float_right);
    
    rt_bottom_menu.append("<div style='clear:both;'></div>");
    
    rt_tracker_container.append(rt_bottom_menu);
  
    /*
    Setup the modals
    */
    // Add some custom CSS to get the dialogs to display correctly
    var rt_style_overrides = $("<style type='text/css'></style>");
    rt_style_overrides.append(".ui-widget{font-size:11px !important;} .ui-dialog{position:absolute;} .ui-widget-overlay{position:fixed;width: 100% !important;}");
    
    rt_tracker_container.append(rt_style_overrides);
    
    // Build the loading modal
    var rt_load_modal = $("<div id='rt_load_modal' title='Initializing <?php echo Configure::read('Website.name'); ?>'></div>");
    
    var rt_load_modal_body = $("<p></p>");
    rt_load_modal_body.append("<?php echo Configure::read('Website.name'); ?> is currently being initialized. Please stand by.<br /><br />");
    rt_load_modal_message = $("<div style='padding:10px 0px 10px 0px;'></div>");
    rt_load_modal_message.append("<span style='font-style:italic;'>Progress: </span> <span id='rt_load_progress_message'></span>");
    rt_load_modal_body.append(rt_load_modal_message);
    rt_load_modal_bar = $("<div id='rt_load_bar'></div>");
    rt_load_modal_body.append(rt_load_modal_bar);
    rt_load_modal.append(rt_load_modal_body);
    
    rt_tracker_container.append(rt_load_modal);      
    
    // Build the compatibility check modal
    var rt_canvas_modal = $("<div id='rt_canvas_modal' title='Your browser does not support HTML5 canvas.'></div>");
    rt_canvas_modal.append("<p>The browser you are currently using does not appear to support HTML5 canvas, which is required to render <?php echo Configure::read('Website.name'); ?>. You may continue anyway, but be aware retroTrack may not behave as intended. We recommend switching to a more modern browser.</p>");
    
    var rt_canvas_modal_center = $("<center></center>");
    var rt_canvas_modal_chrome = $("<div class='rt_browser_warning_box'></div>");
    rt_canvas_modal_chrome.append("<a href='https://www.google.com/intl/en/chrome/browser/' style='color: #666666;'><img src='<?php echo Router::url('/', true); ?>img/browser_chrome.gif' /><br />Google Chrome 4.0+</a>");
    rt_canvas_modal_center.append(rt_canvas_modal_chrome);
    var rt_canvas_modal_firefox = $("<div class='rt_browser_warning_box'></div>");
    rt_canvas_modal_firefox.append("<a href='http://www.mozilla.org/en-US/firefox/new/' style='color: #666666;'><img src='<?php echo Router::url('/', true); ?>img/browser_firefox.gif' /><br />Mozilla Firefox 2.0+</a>");
    rt_canvas_modal_center.append(rt_canvas_modal_firefox);
    rt_canvas_modal.append(rt_canvas_modal_center);
    
    rt_tracker_container.append(rt_canvas_modal);
  }
  
  // Starts retroTrack
  function start_retroTrack(){
    $(document).ready(function(){
      // First setup all of the HTML needed to run retroTrack
      retroTrack_interface.populateInterface();
      
      // Register the menu listeners
      retroTrack_interface.registerListeners();
      
      // Configure retroTrack
      retroTrack_interface.setupConfiguration();
    });
  }
})();
