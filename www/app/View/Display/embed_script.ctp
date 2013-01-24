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
  
  echo $this->element('retrotrack_javascript');
  ?>
  
  // Starts retroTrack
  function start_retroTrack(){
    $(document).ready(function(){
      // First setup all of the HTML needed to run retroTrack
      retroTrack_interface.populateInterface('retroTrack_embed', '<?php echo Router::url('/', true); ?>');
      
      // Configure retroTrack
      retroTrack_interface.setupConfiguration('<?php echo $satellite_json; ?>', '<?php echo $group_json; ?>', '<?php echo $station_json; ?>', '<?php echo $default_elements; ?>', '<?php echo $tle_json; ?>', '<?php echo $configuration_json; ?>', '<?php echo Router::url('/', true); ?>');
      
      // Start retroTrack
      retroTrack_interface.startTracker();
    });
  }
})();
