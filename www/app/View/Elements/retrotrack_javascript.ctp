/*
Define global variables.
*/
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
var resource_base_url = null;

/*
retroTrack_interface

This javascript class is responsible for handling the retroTrack UI. Its responsibilities include:
- Handling menu interactions
- Populating menus
*/
var retroTrack_interface = {
  /*
  Resets all menu link classes and hides any open panels.
  
  @param trigger_pane_id  ID of the triggering element.
  */
  resetMenus: function(trigger_pane_id){
    // Remove active class
    $("[id^=rt_show_menu_]").removeClass('active');
    
    // Hide control panels
    $("[id^=rt_menu_][id!="+trigger_pane_id+"]").hide();
  },
  
  /*
  Populates the satellite selection menu.
  
  @param satellites         All satellites to display in the menu.
  @param active_satellites  All of the currently selected satellites.
  */
  populateSatellitesMenu: function(satellites, active_satellites){
    if (satellites.length==0){
      $('#rt_menu_satellites').html('No satellites are currently enabled for this tracker.');
    } else {
      // Loop through all of the satellites and add them to the list
      for (curr_satellite_id in satellites){
        $('#rt_satellite_list').append($("<option></option>").attr("value",satellites[curr_satellite_id]['name']).attr("rel",curr_satellite_id).attr("title",satellites[curr_satellite_id]['description']).text(satellites[curr_satellite_id]['name'])); 
      }
      
      // Enable jQuery Chosen
      $("#rt_satellite_list").chosen();
    }
  },
  
  /*
  Populates the stations selection menu.
  
  @param stations        All stations to display in the menu.
  @param active_station  The active ground station.
  */
  populateStationsMenu: function(stations, active_station){
    if (stations.length==0){
      $('#rt_menu_stations').html('No stations are currently enabled for this tracker.');
    } else {
      // Loop through all of the stations and add them to the list
      for (curr_station_id in stations){
        $("#rt_station_list").append($("<option></option>").attr("value",curr_station_id).attr("rel",stations[curr_station_id]['name']).attr("title",stations[curr_station_id]['description']).text(stations[curr_station_id]['name']));
      }
        
      // Enable jQuery Chosen
      $("#rt_station_list").chosen();
    }
  },
  
  /*
  Populates the group selection menu.
  
  @param groups  All groups to display in the menu.
  */
  populateGroupsMenu: function(groups){
    if (groups.length==0){
      $('#rt_menu_groups').html('No groups are currently enabled for this tracker.');
    } else {
      // Loop through all of the groups and add them to the list
      for (curr_group_id in groups){
        $("#rt_group_list").append($("<option></option>").attr("value",curr_group_id).attr("rel",curr_group_id).attr("title",groups[curr_group_id]['description']).text(groups[curr_group_id]['name']));
      }

      // Enable selectable
      $("#rt_group_list").chosen();
    }
  },
  
  /*
  Populates the option select menu. Because we don't want to show all options in the menu, just loop through and select the existing buttons based on default value.
  
  @param configuration  Object containing the configuration options.
  */
  populateOptionsMenu: function(configuration){
    // Loop through each menu item and check it's value in configuration
    $("#rt_option_list").children().each(function(){
      // Grab the option's ID
      option_name = $(this).attr('id');
      
      // Strip off the leading rt_
      option_name = option_name.substring(3);
      
      // Check the default value
      if (configuration[option_name]['value']=='1'){
        // Select it
        $(this).addClass('ui-selected');
      }
    });
  },
  
  /*
  Initializes the satellite selection state.
  */
  initializeActiveSatellites: function(){
    // Loop through all of the default satellites
    active_satellites = [];
    if (default_elements!=null && default_elements['satellites'].length!=0){
      for (curr_satellite_index in default_elements['satellites']){
        // Add to active_satellites
        if (default_elements['satellites'][curr_satellite_index]['id'] in satellites){
          active_satellites[active_satellites.length] = default_elements['satellites'][curr_satellite_index]['name'];
          
          // Add the item to the selection
          $("#rt_satellite_list option[value='"+default_elements['satellites'][curr_satellite_index]['name']+"']").attr("selected", "selected");
        }
      }
        
      // Rebuild the menu
      $("#rt_satellite_list").trigger("liszt:updated");
    } else {
      // Select the first satellite
      if (satellites.length!=0){
        for (curr_satellite_index in satellites){
          // Add to active_satellites
          active_satellites[active_satellites.length] = satellites[curr_satellite_index]['name'];
          
          // Add the item to the selection
          $("#rt_satellite_list option[value='"+satellites[curr_satellite_index]['name']+"']").attr("selected", "selected");
          
          // Rebuild the menu
          $("#rt_satellite_list").trigger("liszt:updated");
          
          break;
        }
      }
    }
    
    // Grab the last satellite in the selection
    selected_satellite = retroTrack_interface.getLastElement();
  },
  
  /*
  Initializes the satellite group selection state.
  */
  initializeActiveGroups: function(){
    // Loop through all of the default groups
    if (default_elements!=null){
      for (curr_group_index in default_elements['groups']){
        // Add the group to the selection
        $("#rt_group_list option[value='"+default_elements['groups'][curr_group_index]+"']").attr("selected", "selected");
      }
      
      // Rebuild the menu
      $("#rt_group_list").trigger("liszt:updated");
    }
  },
  
  /*
  Initializes the ground station selection state.
  
  @note To show every satellite instead of just the default, uncomment the blocks of code below.
  */
  initializeActiveStations: function(){
    // Loop through all of the available stations
    active_stations = [];
    /*for (curr_station_index in stations){
      // Add to active_stations
      active_stations[active_stations.length] = stations[curr_station_index]['name'];
    }*/
    active_stations[active_stations.length] = configuration['default_ground_station']['value'];
    
    // Set the active station
    if (configuration['default_ground_station']['value'] in stations){
      // Default present, use it
      selected_station = configuration['default_ground_station']['value'];
    } else {
      // Default not present, use last in list
      selected_station = retroTrack_interface.getLastElement(true);
    }
    
    // Select every ground station
    //$("#rt_station_list option").attr("selected", "selected");
    $("#rt_station_list option[value=\""+selected_station+"\"]").attr("selected", "selected");
    
    // Rebuild the menu
    $("#rt_station_list").trigger("liszt:updated");
  },
  
  /*
  Registers listeners to respond to menu interactions.
  */
  registerListeners: function(){
    // Handle menu expansions
    $("[id^=rt_show_menu_]").click(function(){
      // Clean up the menus
      retroTrack_interface.resetMenus($(this).attr('rel'));
      
      // Show the selected menu
      menu_div = $(this).attr('rel');
      if ($("#"+menu_div).is(":visible")){
          $(this).removeClass("active");
      } else {
          $(this).addClass("active");
      }
      $("#"+menu_div).slideToggle(400);
    });
    
    // Handle satellite selection menu changes
    $("#rt_satellite_list").change(function(event, ui) {
      // Clear active_satellites
      active_satellites = [];
      
      // Unselect all groups
      $("#rt_group_list").children().removeAttr("selected");
      
      // Rebuild the menu
      $("#rt_group_list").trigger("liszt:updated");
      
      // Loop through the selected children and add the rel value to 'active_satellites'
      $(this).children('option:selected').each(function(){
        // Add the satellite name to active_satellites
        active_satellites[active_satellites.length] = $(this).attr('value');
      });
      
      // Grab the last satellite in the selection
      selected_satellite = retroTrack_interface.getLastElement();
      
      // Reload the PLib Satellites
      retroTrack.setPlibSatellites();
      
      // Update plot
      retroTrack.updatePlot();
    });
    
    // Handle station selection menu changes
    $("#rt_station_list").change(function(event, ui) {
      // Clear active_stations
      active_stations = [];
      
      // Loop through the selected children and add the rel value to 'active_stations'
      $(this).children('option:selected').each(function(){
        // Add the station to active_stations
        active_stations[active_stations.length] = $(this).attr('rel');
      });
  
      // Set the active station to be the last one in the list
      selected_station = retroTrack_interface.getLastElement(true);
  
      // Update plot
      retroTrack.updatePlot();
    });
    
    // Handle group selection menu changes
    $("#rt_group_list").change(function(event, ui) {
      // Clear active_satellites
      active_satellites = [];
      selected_satellite = null;
      
      // Unselect all currently selected satellites
      $("#rt_satellite_list").children().removeAttr("selected");
      
      // Rebuild the menu
      $("#rt_satellite_list").trigger("liszt:updated");
      
      // Loop through the selected groups and load their satellites
      $(this).children('option:selected').each(function(){
        // Loop through the active group's satellites
        group_id = $(this).attr('rel');
        for (var satellite_index = 0; satellite_index < groups[group_id]['satellites'].length; satellite_index++){
          // Make sure the satellite is available
          if (groups[group_id]['satellites'][satellite_index]['id'] in satellites){
            // Add the satellite to active_satellites
            active_satellites[active_satellites.length] = groups[group_id]['satellites'][satellite_index]['name'];
            
            // Select the satellite in the menu
            $("#rt_satellite_list option[value='"+groups[group_id]['satellites'][satellite_index]['name']+"']").attr("selected", "selected");
          }
        }
        
        // Rebuild the menu
        $("#rt_satellite_list").trigger("liszt:updated");
        
        // Set the active satellite to the first one in the list
        selected_satellite = retroTrack_interface.getLastElement();
      });
      
      // Reload the PLib Satellites
      retroTrack.setPlibSatellites();
      
      // Update plot
      retroTrack.updatePlot();
    });
    
    // Handle group selection menu changes
    $("#rt_option_list").children().click(function(){
      option_key = $(this).attr('id');
      
      // Strip off the leading rt_
      option_key = option_key.substring(3);
      
      // Toggle the option in the configuration array
      if (configuration[option_key]['value']=='1'){
        // Disable
        configuration[option_key]['value'] = '0';
        $(this).removeClass('ui-selected');
        new_button_text = $(this).html().replace("Disable", "Enable");
        new_button_text = new_button_text.replace("Hide", "Show");
        $(this).html(new_button_text);
      } else {
        // Enable
        configuration[option_key]['value'] = '1';
        $(this).addClass('ui-selected');
        new_button_text = $(this).html().replace("Enable", "Disable");
        new_button_text = new_button_text.replace("Show", "Hide");
        $(this).html(new_button_text);
      }
      
      // Update plot
      retroTrack.updatePlot();
    });
  },
  
  /*
  Setup the tracker configuration.
  
  @param satellite_json         A JSON string containing the satellites to display in the tracker.
  @param group_json             A JSON string containing the satellite groups to display.
  @param station_json           A JSON string containing information about the ground stations to display.
  @param default_elements_json  A JSON string containing which elements should be selected by default.
  @param tle_json               A JSON string containing the satellite TLE settings to use.
  @param configuration_json     A JSON string containing the configuration options to use for the tracker.
  @param base_url               The URL to prefix all resources with.
  */
  setupConfiguration: function(satellite_json, group_json, station_json, default_elements_json, tle_json, configuration_json, base_url){
    // First, register the event listeners
    retroTrack_interface.registerListeners();
  
    // Set the base URL
    resource_base_url = base_url || '';
  
    // Make sure canvas is supported
    $('#rt_canvas_modal').dialog({
      position: 'center',
      width: 500,
      height: 'auto',
      modal: true,
      resizable: false,
      autoOpen: false,
      buttons: {
        "Continue Anyway": function() {
          $(this).dialog("close");
        }
      }
    });
    if (!Modernizr.canvas){
      $("#rt_canvas_modal").dialog("open");
    }
	
    /*
    Initialize the loading progress modal
    */
    $("#rt_load_modal").dialog({
      autoOpen: true,
      resizable: false,
      closeOnEscape: false,
      modal: true,
      width: 400
    });
    $("#rt_load_bar").progressbar({
      value: 0
    });
    
    // Tracker configuration
    $("#rt_load_progress_message").html('Loading configuration.');
    satellites = jQuery.parseJSON(satellite_json); // All satellites this page can display
    active_satellites = new Array(); // Array of the IDs of all active satellites (the IDs are also the indexes in satellites)
    groups = jQuery.parseJSON(group_json); // All groups this page can display
    stations = jQuery.parseJSON(station_json); // All ground stations this page can display
    default_elements = jQuery.parseJSON(default_elements_json); // Default elements
    active_station = null;
    tles = jQuery.parseJSON(tle_json);
    configuration = jQuery.parseJSON(configuration_json);
    $("#rt_load_bar").progressbar("value", 20);
    
    // Setup menus
    $("#rt_load_progress_message").html('Setting up application menus.');
    retroTrack_interface.populateSatellitesMenu(satellites, active_satellites);
    retroTrack_interface.populateGroupsMenu(groups);
    retroTrack_interface.populateStationsMenu(stations, active_station);
    retroTrack_interface.populateOptionsMenu(configuration);
    retroTrack_interface.initializeActiveSatellites();
    retroTrack_interface.initializeActiveGroups();
    retroTrack_interface.initializeActiveStations();
    $("#rt_load_bar").progressbar("value", 50);
  },
  
  /*
  Starts retroTrack. 
  
  @note The reason this isn't in setupConfiguration() is to allow any possible async functions to complete (i.e. loading TLE json) before starting retroTrack.
  */
  startTracker: function(){
    // Initialize retroTracker object
    $("#rt_load_progress_message").html('Setting up tracker object.');
    background_image_path = resource_base_url+"img/"+configuration['map_file']['value'];
    retroTrack.initialize('rt_tracker_canvas');
  },
  
  /*
  Updates the TLE JSON.
  
  This is used by the static version generator because the TLE's are fetched asynchronously and may not be immediately available.
  
  @param tle_json  The JSON string or object containing the satellite TLEs to parse.
  */
  updateTles: function(tle_json){
    if (typeof tle_json == "string"){
      tles = jQuery.parseJSON(tle_json);
    } else {
      tles = tle_json;
    }
  },
  
  /*
  Returns the name of the last station or satellite selected.
  
  @note Because jQuery Chosen triggers a change before it redraws the value when deleting values, we need to compare against the actual select field value to figure out what to select.
  
  @param find_station If set to true, the last station will be found. Otherwise the last satellite will be found.
  @return The name of the last station or satellite.
  */
  getLastElement: function(find_station){
    // Setup local variables
    find_station = (find_station)?true:false;
    temp_selected_element = false;
    select_names = false;
    select_list = null;
    chosen_list_elements = null;
    
    if (find_station){
      // Find the last station
      select_names = $("#rt_station_list").val();
      select_list = $("#rt_station_list");
      chosen_list_elements = $("#rt_station_list_chzn .chzn-choices .search-choice");
    } else {
      // Find the last satellite
      select_names = $("#rt_satellite_list").val();
      select_list = $("#rt_satellite_list");
      chosen_list_elements = $("#rt_satellite_list_chzn .chzn-choices .search-choice");
    }
    
    if (select_names!=null){
      // Compare the number of elements in the select field with the number in the Chosen interface field
      select_count = select_list.val().length;
      chosen_count = chosen_list_elements.length;
      
      // Get an array of elements currently in the Chosen field
      chosen_names = chosen_list_elements.map(function() {
        return $(this).children('span').last().text();
      }).get();
      
      // Decide which element to take
      if (select_count==chosen_count){
        // No difference so either just initialized or item was added to the list; use the true last chosen element
        temp_selected_element = chosen_names[chosen_names.length-1];
      } else if (select_count<chosen_count){
        // Element removed from the list; use the last chosen element that isn't the one that was deleted
        removed_element = false;
      
        // Element removed from the list; loop through Chosen elements and find what was removed
        for (var temp_chosen_name = 0; temp_chosen_name < chosen_names.length; temp_chosen_name++){
          // Check if the element is in the select field
          if (jQuery.inArray(chosen_names[temp_chosen_name], select_names) == -1){
            removed_element = chosen_names[temp_chosen_name];
          }
        }
        
        // Select the last item that's not the removed element
        search_last_element = chosen_list_elements.last();
        while(true){
          search_last_name = search_last_element.children('span').last().text();
          if (search_last_name != removed_element){
            // Found the last element (that wasn't just deleted)
            temp_selected_element = search_last_name;
            break;
          } else {
            // Satellite was just deleted, look at the one before it
            search_last_element = chosen_list_elements.last().prev();
          }
        }
      }
    }
    
    // Check for a null value
    if (temp_selected_element==null){
      temp_selected_element = false;
    }
    
    return temp_selected_element;
  },
  
  /*
  Populates 'container_div' with the retroTrack interface.
  
  This is used by the embed script and static version generators to generate the interface.
  
  @param container_div      The name of the container to load the retroTrack instance into.
  @param resource_base_url  The base URL to use when loading resources.
  */
  populateInterface: function(container_div, base_url){
    // Load the retroTrack CSS
    var rt_css = $("<link>", { 
      rel: "stylesheet", 
      type: "text/css", 
      href: base_url+"css/retrotrack_display.css" 
    });
    rt_css.appendTo('head');
    
    // Load the jQuery Chosen CSS
    var chosen_css = $("<link>", { 
      rel: "stylesheet", 
      type: "text/css", 
      href: base_url+"css/chosen.css" 
    });
    chosen_css.appendTo('head');
    
    // Load the jQuery UI CSS
    var jquery_ui_css = $("<link>", { 
      rel: "stylesheet", 
      type: "text/css", 
      href: base_url+"css/jquery-ui-1.9.1.custom.min.css" 
    });
    jquery_ui_css.appendTo('head');
    
    // Create the main retroTrack container
    var retroTrack_embed = $("#"+container_div);
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
    var rt_option_list = $("<ol id='rt_option_list' class='rt_menu_list' style='margin-left:0px;'></ol>");
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
    var rt_load_modal = $("<div id='rt_load_modal' title='Initializing retroTrack'></div>");
    
    var rt_load_modal_body = $("<p></p>");
    rt_load_modal_body.append("retroTrack is currently being initialized. Please stand by.<br /><br />");
    rt_load_modal_message = $("<div style='padding:10px 0px 10px 0px;'></div>");
    rt_load_modal_message.append("<span style='font-style:italic;'>Progress: </span> <span id='rt_load_progress_message'></span>");
    rt_load_modal_body.append(rt_load_modal_message);
    rt_load_modal_bar = $("<div id='rt_load_bar'></div>");
    rt_load_modal_body.append(rt_load_modal_bar);
    rt_load_modal.append(rt_load_modal_body);
    
    rt_tracker_container.append(rt_load_modal);      
    
    // Build the compatibility check modal
    var rt_canvas_modal = $("<div id='rt_canvas_modal' title='Your browser does not support HTML5 canvas.'></div>");
    rt_canvas_modal.append("<p>The browser you are currently using does not appear to support HTML5 canvas, which is required to render retroTrack. You may continue anyway, but be aware retroTrack may not behave as intended. We recommend switching to a more modern browser.</p>");
    
    var rt_canvas_modal_center = $("<center></center>");
    var rt_canvas_modal_chrome = $("<div class='rt_browser_warning_box'></div>");
    rt_canvas_modal_chrome.append("<a href='https://www.google.com/intl/en/chrome/browser/' style='color: #666666;'><img src='"+base_url+"img/browser_chrome.gif' /><br />Google Chrome 4.0+</a>");
    rt_canvas_modal_center.append(rt_canvas_modal_chrome);
    var rt_canvas_modal_firefox = $("<div class='rt_browser_warning_box'></div>");
    rt_canvas_modal_firefox.append("<a href='http://www.mozilla.org/en-US/firefox/new/' style='color: #666666;'><img src='"+base_url+"img/browser_firefox.gif' /><br />Mozilla Firefox 2.0+</a>");
    rt_canvas_modal_center.append(rt_canvas_modal_firefox);
    rt_canvas_modal.append(rt_canvas_modal_center);
    
    rt_tracker_container.append(rt_canvas_modal);
  }
}

/*
retroTrack

This class is responsible for interfacing with the SGP4 propagator and updating the tracker display.
*/
var retroTrack = {
  // Class variables
  tracker_canvas_context: null,
  tracker_canvas_width: null,
  tracker_canvas_height: null,
  map_image: null,
  footprint: null, // General purpose footprint array
  
  /*
  Sets up the retroTrack display.

  @param canvas  Primary canvas to draw the tracker on.
  */
  initialize: function(canvas){
    // Setup
    tracker_canvas_context = document.getElementById(canvas).getContext('2d');
    tracker_canvas_width = $("#"+canvas).width();
    tracker_canvas_height = $("#"+canvas).height();
    retroTrack.footprint = new Array(360); // This should always contain 360 elements (not step size), one for each degree
    
    // Initialize footprint array
    for (footprint_index=0; footprint_index < 360; footprint_index++){
        // Initialize satellite footprint objects
        retroTrack.footprint[footprint_index] = new retroTrack.positionElement();
    }
    
    // Load the map
    retroTrack.loadMap();
  },
  
  /*
  Continue the initialization. A seperate function is needed to allow the script to pause while the image is loaded.
  */
  continueInitialization: function(){
    // Initialize PLib
    retroTrack.setPlibSatellites();
    
    // Create the clock update loop
    retroTrack.updateClock();
    setInterval(retroTrack.updateClock, configuration['clock_update_period']['value']);
    
    // Create primary display update loop
    retroTrack.updatePlot();
    retroTrack.updatePlot(); // For some reason this needs to be called twice initially for the sun to display correctly
    setInterval(retroTrack.updatePlot, configuration['map_update_period']['value']);
    
    // Hide the modal
    $("#rt_load_bar").progressbar("value", 100);
    $("#rt_load_bar").css('width','100%');
    $("#rt_load_progress_message").html('Complete.');
    $('#rt_load_modal').dialog('close');
  },
  
  /*
  Loads TLE information into PLib and initialize.
  */
  setPlibSatellites: function(){
    // Reset the PLib TLE data
    PLib.tleData = [];
    PLib.sat = [];
    
    // Add everything in active_satellites
    temp_satellites = new Array();
    for (var curr_satellite_index=0; curr_satellite_index < active_satellites.length; curr_satellite_index++){ 
      // Load TLE data into PLib
      curr_satellite_name = active_satellites[curr_satellite_index];
      if (curr_satellite_name in tles){
        PLib.tleData[PLib.tleData.length] = [curr_satellite_name, tles[curr_satellite_name]['raw_l1'], tles[curr_satellite_name]['raw_l2']];
        temp_satellites.push(active_satellites[curr_satellite_index]);
      } else {
        // No TLE data exists for satellite, remove it from menus
        $("#rt_satellite_list option[value="+curr_satellite_name+"]").remove();
      }
    }

    active_satellites = temp_satellites.slice(0);
    
    selected_satellite = retroTrack_interface.getLastElement();
    
    // Initialize PLib
    PLib.InitializeData();
  },
  
  /*
  Loads the map image into the canvas.
  */
  loadMap: function(){
    // Update progress bar
    $("#rt_load_bar").css('width','70%');
    $("#rt_load_progress_message").html('Loading map image.');
    
    map_image = new Image();
    map_image.src = background_image_path;
    map_image.onload = function(){
      // Continue program execution once image has loaded
      retroTrack.continueInitialization();
    }
  },
  
  /*
  Updates the tracker clock at the specified frequency.
  */
  updateClock: function(){
    // Add time padding
    var curr_time = new Date();
    clock_hours = ((curr_time.getHours()+"").length==1)?'0'+curr_time.getHours():curr_time.getHours();
    clock_minutes = ((curr_time.getMinutes()+"").length==1)?'0'+curr_time.getMinutes():curr_time.getMinutes();
    clock_seconds = ((curr_time.getSeconds()+"").length==1)?'0'+curr_time.getSeconds():curr_time.getSeconds();
    
    // Update the new clock
    $("#rt_top_clock").html((curr_time.getMonth()+1)+"/"+curr_time.getDate()+"/"+curr_time.getFullYear()+" "+clock_hours+":"+clock_minutes+":"+clock_seconds+" (GMT - "+curr_time.getTimezoneOffset()/60+")");
  },
  
  /*
  Sets the initial position of points for the tracker display.
  */
  positionElement: function(){
    this.lat = 0;
    this.lon = 0;
  },
  
  /*
  Draws grid over the tracker display.
  */
  drawGrid: function(){
    // Draw horizontal grid lines
    tracker_canvas_context.fillStyle = '#'+configuration['grid_color']['value'];
    tracker_canvas_context.globalAlpha = configuration['grid_alpha']['value'];
    for (line_num = 1; line_num <= 6; line_num++){
        tracker_canvas_context.fillRect(0, line_num*(tracker_canvas_height/6), tracker_canvas_width, 1);
    }
    
    // Draw vertical grid lines
    for (line_num = 1; line_num <= 12; line_num++){
        tracker_canvas_context.fillRect(line_num*(tracker_canvas_width/12), 0, 1, tracker_canvas_height);
    }
    tracker_canvas_context.globalAlpha = 1.0;
  },
  
  /*
  Draws the sun and the day/night shadow.
  */
  drawSun: function(){
    // Setup
    tracker_canvas_context.fillStyle = "#"+configuration['sun_color']['value'];
    PLib.Calc();
        
    // Calculate the position of the sun
    if (PLib.sun_lon > 180){
      sun_longitude = PLib.sun_lon - 180;
    } else {
      sun_longitude = PLib.sun_lon + 180;
    }
    sun_latitude = 90 - PLib.sun_lat;
    sun_x_pos = Math.round((sun_longitude/360)*tracker_canvas_width);
    sun_y_pos = Math.round((sun_latitude/180)*tracker_canvas_height);
    
    // Set the sun indicator
    tracker_canvas_context.font = "18pt Arial";
    text_width = tracker_canvas_context.measureText("\u2600").width;
    tracker_canvas_context.fillText("\u2600", sun_x_pos-(text_width/2), sun_y_pos);
    
    // Calculate and draw the footprint
    PLib.calcFootPrint(retroTrack.footprint, 360, PLib.sun_lat, PLib.sun_lon, 149597892.0, 0.0);
    last_x_pos = null;
    last_y_pos = null;
    first_x_pos = null;
    first_y_pos = null;
    for (footprint_point = 0; footprint_point < 360; footprint_point++){
      footprint_x_pos = Math.round((retroTrack.footprint[footprint_point].lon + 180)/360*tracker_canvas_width);
      footprint_y_pos = Math.round((180-(retroTrack.footprint[footprint_point].lat+90))/180*tracker_canvas_height);
      
      // Check if we looped to the other side of the map and need to box in the shadow
      if (sun_y_pos>(tracker_canvas_height/2)){
        // Sun in southern hemisphere
        if (footprint_x_pos>last_x_pos){
          // Box in region above the terminator
          tracker_canvas_context.lineTo(0, last_y_pos);
          tracker_canvas_context.lineTo(0, 0);
          tracker_canvas_context.lineTo(tracker_canvas_width, 0);
          tracker_canvas_context.lineTo(tracker_canvas_width, footprint_y_pos);
        }
      } else {
        // Sun in northern hemisphere
        if (footprint_x_pos<last_x_pos){
          // Box in the region below the terminator
          tracker_canvas_context.lineTo(tracker_canvas_width, last_y_pos);
          tracker_canvas_context.lineTo(tracker_canvas_width, tracker_canvas_height);
          tracker_canvas_context.lineTo(0, tracker_canvas_height);
          tracker_canvas_context.lineTo(0, footprint_y_pos);
          tracker_canvas_context.lineTo(footprint_x_pos, footprint_y_pos);
        } else {            
          // Check if the sun is on the edge of the map
          if (footprint_point == 360-1){
            tracker_canvas_context.lineTo(tracker_canvas_width, footprint_y_pos);
            tracker_canvas_context.lineTo(tracker_canvas_width, tracker_canvas_height);
            tracker_canvas_context.lineTo(0, tracker_canvas_height);
            tracker_canvas_context.lineTo(0, first_y_pos);
            tracker_canvas_context.lineTo(first_x_pos, first_y_pos);
          }
        }
      }
  
      if (footprint_point==0){
        tracker_canvas_context.moveTo(footprint_x_pos, footprint_y_pos);
        tracker_canvas_context.beginPath();
        first_x_pos = footprint_x_pos;
        first_y_pos = footprint_y_pos;
      } else {
        tracker_canvas_context.lineTo(footprint_x_pos, footprint_y_pos);
      }
  
      last_x_pos = footprint_x_pos;
      last_y_pos = footprint_y_pos;
    }
    tracker_canvas_context.closePath();

    tracker_canvas_context.lineWidth = 2;
    tracker_canvas_context.globalAlpha = configuration['shadow_alpha']['value'];
    tracker_canvas_context.fillStyle = "#"+configuration['shadow_color']['value'];
    tracker_canvas_context.fill();
    tracker_canvas_context.globalAlpha = 1.0;
  },
  
  /*
  Calculates and draws the path for the active satellite.
  */
  drawSatellitePath: function(){
    // Make sure a satellite is selected
    if (selected_satellite){
      // This is needed so that PLib will calculate the orbit of the selected satellite, not the last one that was configured
      satInfo = PLib.QuickFind(selected_satellite);
      
      // Find the PLib sat object of the selected satellite
      selected_satellite_plib = null;
      for (plib_satellite_counter = 0; plib_satellite_counter < PLib.sat.length; plib_satellite_counter++){
        if(PLib.sat[plib_satellite_counter].name == selected_satellite){
          selected_satellite_plib = PLib.sat[plib_satellite_counter];
          break;
        }
      }
      
      // Calculate and display the path
      tb = PLib.daynum - 0.05 * (1 / selected_satellite_plib.meanmo);
      tf = PLib.daynum + 3 * (1 / selected_satellite_plib.meanmo);
      PLib.daynum = tb;
      tracker_canvas_context.beginPath();
      last_x_pos = null;
      first_x_pos = null;
      first_y_pos = null;
      first_loop = true;
      while (PLib.daynum < tf){
        // This works by running the simulation forwards to calculate the satellites position at various times
        PLib.Calc();
        
        PLib.daynum += 10 / (24 * 3600);
        
        pos_lat = PLib.sat_lat;
        pos_lon = PLib.sat_lon;
        pos_lon = 360 - PLib.isplong;
        if (pos_lon > 180){
          pos_lon = -PLib.isplong;
        }
        
        pos_x = Math.round((pos_lon + 180)/360 * tracker_canvas_width);
        pos_y = Math.round((180 - (pos_lat + 90))/180 * tracker_canvas_height);
        
        if (first_loop){
          tracker_canvas_context.moveTo(pos_x, pos_y);
          first_x_pos = pos_x;
          first_y_pos = pos_y;
          first_loop = false;
        } else {
          if (Math.abs(pos_x-last_x_pos)<=(tracker_canvas_width/3)){
            // Loop didn't have to jump to other side of map, so draw. 
            tracker_canvas_context.lineTo(pos_x, pos_y);
          }
          tracker_canvas_context.moveTo(pos_x, pos_y);
        }
        
        last_x_pos = pos_x;
      }
      tracker_canvas_context.lineWidth = 2;
      tracker_canvas_context.strokeStyle = "#"+configuration['path_color']['value'];
      tracker_canvas_context.stroke();
    }
  },
  
  /*
  Primary plot update loop function.

  - Plots the selected satellite (plus its path)
  - Plots all active satellites (markers)
  */
  updatePlot: function(){
    // Reload the map (clears the previous satellite positions, etc.)
    tracker_canvas_context.drawImage(map_image, 0, 0);

    // Clear the satellite info pane
    $("#rt_station_parameters").html("");
    
    // Plot the grid (if configured)
    if (configuration['show_grid']['value']=='1'){
      retroTrack.drawGrid();
    }
    
    // Plot the sun (if configured)
    if (configuration['show_sun']['value']=='1'){
      retroTrack.drawSun();
    }
    
    // Plot the selected satellite's predicted path
    if (configuration['show_path']['value']=='1'){
      retroTrack.drawSatellitePath();
    }
    
    // Plot each satellite marker, including the selected satellite
    for (var curr_satellite_index = 0; curr_satellite_index < PLib.sat.length; curr_satellite_index++){
      // Load the satellite from PLib
      curr_satellite_info = PLib.QuickFind(PLib.sat[curr_satellite_index].name);
      curr_satellite_name = PLib.sat[curr_satellite_index].name;
      retroTrack.plotSatellitePosition(curr_satellite_info, curr_satellite_name);
      
      // Update the information pane if needed
      if (curr_satellite_name==selected_satellite){
          retroTrack.updateSatelliteBar(curr_satellite_info, curr_satellite_name);
      }
    }
    
    // Update the bar if no satellite is selected
    if (!selected_satellite){
      retroTrack.updateSatelliteBar(null, null); 
    }
    
    // Plot the ground stations
    for (var curr_station_index = 0; curr_station_index < active_stations.length; curr_station_index++){
      // Plot the station
      retroTrack.plotStationPosition(active_stations[curr_station_index]);
            
      if (active_stations[curr_station_index]==selected_station){
        retroTrack.updateStationBar(selected_station);
      }
    }
  },
  
  /*
  Updates the satellite information bar with the specified satellite's parameters.
  
  @param curr_satellite_info  PLib satellite info object.
  @param curr_satellite_name  Name of the specified satellite.
  */
  updateSatelliteBar: function(curr_satellite_info, curr_satellite_name){
    // Display the satellite information.
    $("#rt_satellite_parameters").html("");
    
    if (!selected_satellite){
      // No satellite selected
      $("#rt_satellite_parameters").append("<li></li>");
    } else {
      // Calculate orbit number
      curr_satellite_orbit = parseInt(PLib.tle.revnum, 10) + curr_satellite_info.orbitNumber;
      curr_satellite_orbit = PLib.rv;
      
      // Load the satellite parameters
      $("#rt_satellite_parameters").append("<li id='rt_satellite_info_name'><span style='color: #"+configuration['satellite_selected_color']['value']+";'>"+curr_satellite_name+"</span></li>");
      $("#rt_satellite_parameters").append("<li>Lat: "+curr_satellite_info.latitude.toFixed(1)+"</li>");
      $("#rt_satellite_parameters").append("<li>Lon: "+curr_satellite_info.longitude.toFixed(1)+"</li>");
      $("#rt_satellite_parameters").append("<li>Alt: "+curr_satellite_info.altitude.toFixed(1)+" km</li>");
      $("#rt_satellite_parameters").append("<li>Orbit: #"+curr_satellite_orbit+"</li>");
    }
  },
  
  /*
  Updates the station information bar with the currently selected ground station's parameters.
  
  @param curr_station_name  Name of the ground station.
  */
  updateStationBar: function(curr_station_name){
    // Display the status bar
    if (selected_satellite){
      selected_satellite_trimmed = (selected_satellite.length<=10)?selected_satellite:selected_satellite.substring(0,10)+"...";
      $("#rt_station_parameters").append("<li id='rt_station_info_name'><span style='color: #"+configuration['station_selected_color']['value']+";'>"+curr_station_name+"</span> - <span style='color: #"+configuration['satellite_selected_color']['value']+";'>"+selected_satellite_trimmed+"</span></li>");
    } else {
       $("#rt_station_parameters").append("<li id='rt_station_info_name'><span style='color: #"+configuration['station_selected_color']['value']+";'>"+curr_station_name+"</span></li>");
    }
    $("#rt_station_parameters").append("<li>Lat: "+Number(stations[curr_station_name]['latitude']).toFixed(3)+"</li>");
    $("#rt_station_parameters").append("<li>Lon: "+Number(stations[curr_station_name]['longitude']).toFixed(3)+"</li>");
    if(selected_satellite){
      selected_satellite_info = PLib.QuickFind(selected_satellite);
      $("#rt_station_parameters").append("<li>Az: "+selected_satellite_info.azimuth+"</li>");
      $("#rt_station_parameters").append("<li>El: "+selected_satellite_info.elevation+"</li>");
      $("#rt_station_parameters").append("<li>Range: "+selected_satellite_info.slantRange.toFixed(2)+" km</li>");
    }
  },
  
  /*
  Plots the provided ground station on the canvas.
  
  @param curr_station_name  Name of the station to plot.
  */
  plotStationPosition: function(curr_station_name){
    // Load the station
    temp_station = stations[curr_station_name];
    
    // Calculate the position of the station
    station_x_pos = Math.round((Number(temp_station['longitude'])+180)/360*tracker_canvas_width);
    station_y_pos = Math.round((180-(Number(temp_station['latitude'])+90))/180*tracker_canvas_height);
    
    // Decide what color it should be
    if (curr_station_name==selected_station){
      tracker_canvas_context.fillStyle = "#"+configuration['station_selected_color']['value'];
    } else {
      tracker_canvas_context.fillStyle = "#"+configuration['station_color']['value'];
    }
    
    // Draw the station
    tracker_canvas_context.beginPath();
    tracker_canvas_context.arc(station_x_pos, station_y_pos, configuration['satellite_size']['value']/2, 0, Math.PI*2, true);
    tracker_canvas_context.closePath();
    tracker_canvas_context.fill();
    
    // Draw the labels
    if (configuration['show_station_names']['value']=='1'){
      tracker_canvas_context.font = "10px Arial";
      tracker_canvas_context.fillStyle = '#'+configuration['station_label_color']['value'];
      text_x_pos = station_x_pos + configuration['satellite_size']['value']/2 + 3; // Move label 3px to the right of indicator
      text_y_pos = station_y_pos + configuration['satellite_size']['value']/2;
      text_width = tracker_canvas_context.measureText(curr_station_name).width;
      if ((text_x_pos+text_width)>tracker_canvas_width){
        // Label off the page, flip it to the other side of the indicator
        text_x_pos = station_x_pos - configuration['satellite_size']['value']/2 - text_width - 3;
      }
      tracker_canvas_context.fillText(curr_station_name, text_x_pos, text_y_pos);
    }
    
    // Show the footprint for active station
    if (configuration['show_station_footprint']['value']=='1'){
      if (curr_station_name == selected_station){
        tracker_canvas_context.fillStyle = "#"+configuration['station_footprint_color']['value'];
        PLib.configureGroundStation(Number(temp_station['latitude']),Number(temp_station['longitude']));
        selected_satellite_info = PLib.QuickFind(selected_satellite);
        PLib.calcFootPrint(retroTrack.footprint, 360, Number(temp_station['latitude']), Number(temp_station['longitude']), selected_satellite_info.altitude, 0.0);
        tracker_canvas_context.beginPath();
        last_x_pos = null;
        first_x_pos = null;
        first_y_pos = null;
        for (footprint_counter=0; footprint_counter<360; footprint_counter++){
          x_pos = Math.round((retroTrack.footprint[footprint_counter].lon+180)/360*tracker_canvas_width);
          y_pos = Math.round((180-(retroTrack.footprint[footprint_counter].lat+90))/180*tracker_canvas_height);
          //tracker_canvas_context.fillRect(x_pos,y_pos,1,1);
          
          if (footprint_counter==0){
            tracker_canvas_context.moveTo(x_pos, y_pos);
            first_x_pos = x_pos;
            first_y_pos = y_pos;
          } else {
            if (Math.abs(x_pos-last_x_pos)<=(tracker_canvas_width/3)){
              // Loop didn't have to jump to other side of map, so draw. 
              tracker_canvas_context.lineTo(x_pos, y_pos);
            }
            tracker_canvas_context.moveTo(x_pos, y_pos);
          }
          
          last_x_pos = x_pos;
        }
        
        if (Math.abs(x_pos-first_x_pos)<=(tracker_canvas_width/3)){
          tracker_canvas_context.lineTo(first_x_pos, first_y_pos);
        }
        
        tracker_canvas_context.lineWidth = 1;
        tracker_canvas_context.strokeStyle = "#"+configuration['station_footprint_color']['value'];
        tracker_canvas_context.stroke();
      }
    }
  },
  
  /*
  Plots a marker on the canvas for the specified satellite.
  
  @param curr_satellite_info  PLib satellite info object.
  @param curr_satellite_name  Name of the current satellite.
  */
  plotSatellitePosition: function(curr_satellite_info, curr_satellite_name){
    // Determine color
    if (curr_satellite_name==selected_satellite){
      tracker_canvas_context.fillStyle = '#'+configuration['satellite_selected_color']['value'];
    } else {
      tracker_canvas_context.fillStyle = '#'+configuration['satellite_color']['value'];
    }
    
    // Calculate the pixel location of the satellite
    curr_satellite_lon = curr_satellite_info.longitude;
    curr_satellite_lat = curr_satellite_info.latitude;
    x_pos = Math.round((curr_satellite_lon+180)/360*tracker_canvas_width);
    y_pos = Math.round((180-(curr_satellite_lat+90))/180*tracker_canvas_height);
    
    // Add the indicator to tracker display
    tracker_canvas_context.fillRect(x_pos-configuration['satellite_size']['value']/2,y_pos-configuration['satellite_size']['value']/2, configuration['satellite_size']['value'], configuration['satellite_size']['value']);
    
    // Plot the satellite names
    if (configuration['show_satellite_names']['value']=='1'){
      tracker_canvas_context.font = "10px Arial";
      tracker_canvas_context.fillStyle = '#'+configuration['satellite_label_color']['value'];
      text_x_pos = x_pos + configuration['satellite_size']['value']/2 + 3; // Move label 3px to the right of indicator
      text_y_pos = y_pos + configuration['satellite_size']['value']/2;
      text_width = tracker_canvas_context.measureText(curr_satellite_name).width;
      if ((text_x_pos+text_width)>tracker_canvas_width){
        // Label off the page, flip it to the other side of the indicator
        text_x_pos = x_pos - configuration['satellite_size']['value']/2 - text_width - 3;
      }
      tracker_canvas_context.fillText(curr_satellite_name, text_x_pos, text_y_pos);
    }
    
    // Plot the satellite footprint if needed
    if (curr_satellite_name==selected_satellite){
      if (configuration['show_satellite_footprint']['value']=='1'){
        PLib.calcFootPrint(retroTrack.footprint, 360, curr_satellite_info.latitude, curr_satellite_info.longitude, curr_satellite_info.altitude, 0.0 );
        tracker_canvas_context.beginPath();
        last_x_pos = null;
        first_x_pos = null;
        first_y_pos = null;
        for (satellite_footprint_count=0; satellite_footprint_count<360; satellite_footprint_count++){
          footprint_x_pos = Math.round((retroTrack.footprint[satellite_footprint_count].lon + 180) / 360 * tracker_canvas_width);
          footprint_y_pos = Math.round((180 - (retroTrack.footprint[satellite_footprint_count].lat + 90))  / 180 * tracker_canvas_height);
          
          if (satellite_footprint_count==0){
            tracker_canvas_context.moveTo(footprint_x_pos, footprint_y_pos);
            first_x_pos = footprint_x_pos;
            first_y_pos = footprint_y_pos;
          } else {
            if (Math.abs(footprint_x_pos-last_x_pos)<=(tracker_canvas_width/3)){
              // Loop didn't have to jump to other side of map, so draw. 
              tracker_canvas_context.lineTo(footprint_x_pos, footprint_y_pos);
            }
            tracker_canvas_context.moveTo(footprint_x_pos, footprint_y_pos);
          }
          
          last_x_pos = footprint_x_pos;
        }
        
        if (Math.abs(footprint_x_pos-first_x_pos)<=(tracker_canvas_width/3)){
          tracker_canvas_context.lineTo(first_x_pos, first_y_pos);
        }
    
        tracker_canvas_context.lineWidth = 1;
        tracker_canvas_context.strokeStyle = "#"+configuration['satellite_footprint_color']['value'];
        tracker_canvas_context.stroke();
      }
    }
  }
}

/*
PLib

This class performs the SGP4 propagations needed to track satellites.

Copyright Andrew T. West, 2008
Based on PREDICT, Copyright John A. Magliacane, KD2BD 1991-2002 
Last update: 07-Jun-2008    

retroTrack Changes:
  - updated parseInt in Calc() for PLib.rv calcs to force base 10.  Was getting confused by a leading 0 in the TLE file.
  - Removed rounding of isplat and isplong so it can be customized by application code.
*/
var PLib = {
		deg2rad: 1.745329251994330E-2,
		pi: 3.14159265358979323846, pio2: 1.57079632679489656, twopi: 6.28318530717958623,
		e6a: 1.0E-6,
		tothrd: 6.6666666666666666E-1,
		xj3: -2.53881E-6, xke: 7.43669161E-2, xkmper: 6.378137E3, xmnpda: 1.44E3,
		ae: 1.0,
		ck2: 5.413079E-4, ck4: 6.209887E-7,
		f: 3.35281066474748E-3, s: 1.012229,
		qoms2t: 1.880279E-09,
		secday: 8.6400E4,
		omega_E: 1.00273790934,
		zns: 1.19459E-5, c1ss: 2.9864797E-6, zes: 1.675E-2, znl: 1.5835218E-4, c1l: 4.7968065E-7, zel: 5.490E-2,
		zcosis: 9.1744867E-1, zsinis: 3.9785416E-1, zsings: -9.8088458E-1, zcosgs: 1.945905E-1,
		q22: 1.7891679E-6, q31: 2.1460748E-6, q33: 2.2123015E-7,
		g22: 5.7686396, g32: 9.5240898E-1, g44: 1.8014998, g52: 1.0508330, g54: 4.4108898,
		root22: 1.7891679E-6, root32: 3.7393792E-7, root44: 7.3636953E-9, root52: 1.1428639E-7, root54: 2.1765803E-9,
		thdt: 4.3752691E-3,
		mfactor: 7.292115E-5,
		sr: 6.96000E5,
		AU: 1.49597870691E8,

		dpinit: 1, dpsec: 2, dpper: 3,

		ALL_FLAGS: -1,
		SGP4_INITIALIZED_FLAG: 0x000002,
		SDP4_INITIALIZED_FLAG: 0x000004,
		SIMPLE_FLAG: 0x000020,
		DEEP_SPACE_EPHEM_FLAG: 0x000040,
		LUNAR_TERMS_DONE_FLAG: 0x000080,
		DO_LOOP_FLAG: 0x000200,
		RESONANCE_FLAG: 0x000400,
		SYNCHRONOUS_FLAG: 0x000800,
		EPOCH_RESTART_FLAG: 0x001000,
		VISIBLE_FLAG: 0x002000,
		SAT_ECLIPSED_FLAG: 0x004000,

		sat_t: function()
			{
				this.line1 = "";
				this.line2 = "";
				this.name = "";
				this.catnum = 0;
				this.setnum = 0;
				this.designator = "";
				this.year = 0;
				this.refepoch = 0.0;
				this.incl = 0.0;
				this.raan = 0.0;
				this.eccn = 0.0;
				this.argper = 0.0;
				this.meanan = 0.0;
				this.meanmo = 0.0;
				this.drag = 0.0;
				this.nddot6 = 0.0;
				this.bstar = 0.0;
				this.orbitnum = 0.0;
				this.refepoch = 0;
			},

		sat: new Array(),

		qth:
			{
				callsign: "HOME",
				stnlat: 0.0,
				stnlong: 0.0,
				stnalt: 1000,
				utc_offset: new Date().getTimezoneOffset() / 60
			},

		tempstr: "", output: "",

		tsince: 0.0, jul_epoch: 0.0, jul_utc: 0.0, eclipse_depth: 0.0,
			sat_azi: 0.0, sat_ele: 0.0, sat_range: 0.0, sat_range_rate: 0.0,
			sat_lat: 0.0, sat_lon: 0.0, sat_alt: 0.0, sat_vel: 0.0, phase: 0.0,
			sun_azi: 0.0, sun_ele: 0.0, daynum: 0.0, fm: 0.0, fk: 0.0, age: 0.0, aostime: 0.0,
			lostime: 0.0, ax: 0.0, ay: 0.0, az: 0.0, rx: 0.0, ry: 0.0, rz: 0.0, squint: 0.0, alat: 0.0, alon: 0.0,

		ephem: "", sat_sun_status: "", findsun: "",

		indx: 0, iaz: 0, iel: 0, ma256: 0, isplat: 0, isplong: 0, Flags: 0,

		rv: 0, irk: 0,

		tle_t: function()
		{
			this.epoch = 0.0;
			this.xndt2o = 0.0;
			this.xndd6o = 0.0;
			this.bstar = 0.0;
			this.xincl = 0.0;
			this.xnodeo = 0.0;
			this.eo = 0.0;
			this.omegao = 0.0;
			this.xmo = 0.0;
			this.xno = 0.0;
			this.catnr = 0;
			this.elset = 0;
			this.revnum = 0;
			this.sat_name = "";
			this.idesg = "";
		},

		geodetic_t: function()
		{
			this.lat = 0.0;
			this.lon = 0.0;
			this.alt = 0.0;
			this.theta = 0.0;
		},

		vector_t: function()
		{
			this.x = 0.0;
			this.y = 0.0;
			this.z = 0.0;
			this.w = 0.0;
		},

		deep_arg_t: function()
		{
			this.eosq = 0.0;
			this.sinio = 0.0;
			this.cosio = 0.0;
			this.betao = 0.0;
			this.aodp = 0.0;
			this.theta2 = 0.0;
			this.sing = 0.0;
			this.cosg = 0.0;
			this.betao2 = 0.0;
			this.xmdot = 0.0;
			this.omgdot = 0.0;
			this.xnodot = 0.0;
			this.xnodp = 0.0;
			this.xll = 0.0;
			this.omgadf = 0.0;
			this.xnode = 0.0;
			this.em = 0.0;
			this.xinc = 0.0;
			this.xn = 0.0;
			this.t = 0.0;
			this.ds50 = 0.0;
		},

		obs_geodetic: new Object(),
		tle: new Object(),
		tleData: new Array(),

		isFlagSet: function(flag)
		{
			return (PLib.Flags & flag);
		},

		isFlagClear: function(flag)
		{
			return (~PLib.Flags & flag);
		},

		SetFlag: function(flag)
		{
			PLib.Flags |= flag;
		},

		ClearFlag: function (flag)
		{
			PLib.Flags &= ~flag;
		},

		Sqr: function(arg)
		{
			return (arg * arg);
		},

		Radians: function(arg)
		{
			return (arg * PLib.deg2rad);
		},

		Degrees: function(arg)
		{
			return (arg / PLib.deg2rad);
		},

		Magnitude: function(v)
		{
			v.w = Math.sqrt(PLib.Sqr(v.x) + PLib.Sqr(v.y) + PLib.Sqr(v.z));
		},

		Vec_Sub: function(v1, v2, v3)
		{
			v3.x = v1.x - v2.x;
			v3.y = v1.y - v2.y;
			v3.z = v1.z - v2.z;
			PLib.Magnitude(v3);
		},

		Scalar_Multiply: function(k, v1, v2)
		{
			v2.x = k * v1.x;
			v2.y = k * v1.y;
			v2.z = k * v1.z;
			v2.w = Math.abs(k) * v1.w;
		},

		Scale_Vector: function(k, v)
		{ 
			v.x *= k;
			v.y *= k;
			v.z *= k;
			PLib.Magnitude(v);
		},

		Dot: function(v1, v2)
		{
			return (v1.x * v2.x + v1.y * v2.y + v1.z * v2.z);
		},

		Angle: function(v1, v2)
		{
			PLib.Magnitude(v1);
			PLib.Magnitude(v2);
			return (Math.acos(PLib.Dot(v1, v2) / (v1.w * v2.w)));
		},

		FMod2p: function(x)
		{
			var i = 0;
			var ret_val = 0.0;

			ret_val = x;
			i = parseInt(ret_val / PLib.twopi);
			ret_val -= i * PLib.twopi;

			if (ret_val < 0.0)
				ret_val += PLib.twopi;

			return ret_val;
		},

		Modulus: function(arg1, arg2)
		{
			return arg1 - (parseInt(arg1 / arg2)) * arg2;
		},

		Frac: function(arg)
		{
			return(arg - Math.floor(arg));
		},

		Convert_Sat_State: function(pos, vel)
		{
			PLib.Scale_Vector(PLib.xkmper, pos);
			PLib.Scale_Vector(PLib.xkmper * PLib.xmnpda / PLib.secday, vel);
		},

		Julian_Date_of_Year: function(year)
		{
			var A = 0, B = 0, i = 0;
			var jdoy = 0.0;

			year = year - 1;
			i = parseInt(year / 100);
			A = i;
			i = parseInt(A / 4);
			B = 2 - A + i;
			i = parseInt(365.25 * year);
			i += parseInt(30.6001 * 14);
			jdoy = i + 1720994.5 + B;

			return jdoy;
		},

		Julian_Date_of_Epoch: function(epoch)
		{ 
			var year = 0.0, day = 0.0;
		
			year = parseInt(epoch * 1E-3);
			day = ((epoch * 1E-3) - year) * 1E3;
		
			if (year < 57)
				year = year + 2000;
			else
				year = year + 1900;
		
			return (PLib.Julian_Date_of_Year(year) + day);
		},

		Delta_ET: function(year)
		{
			var delta_et = 0.0;
		
			delta_et = 26.465 + 0.747622 * (year - 1950) + 1.886913 * Math.sin(PLib.twopi * (year - 1975) / 33);
		
			return delta_et;
		},

		ThetaG: function(epoch, deep_arg)
		{
			var year = 0.0, day = 0.0, UT = 0.0, jd = 0.0, TU = 0.0, GMST = 0.0, ThetaG = 0.0;
		
			year = parseInt(epoch * 1E-3);
			day = ((epoch * 1E-3) - year) * 1E3;
		
			if (year < 57)
				year += 2000;
			else
				year += 1900;
		
			UT = (day - parseInt(day));
			day = parseInt(day);
			jd = PLib.Julian_Date_of_Year(year) + day;
			TU = (jd - 2451545.0) / 36525;
			GMST = 24110.54841 + TU * (8640184.812866 + TU * (0.093104 - TU * 6.2E-6));
			GMST = PLib.Modulus(GMST + PLib.secday * PLib.omega_E * UT, PLib.secday);
			ThetaG = twopi * GMST / PLib.secday;
			deep_arg.ds50 = jd - 2433281.5 + UT;
			ThetaG = PLib.FMod2p(6.3003880987 * deep_arg.ds50 + 1.72944494);
		
			return ThetaG;
		},

		ThetaG_JD: function(jd)
		{
			var UT = 0.0, TU = 0.0, GMST = 0.0;

			UT = PLib.Frac(jd + 0.5);
			jd = jd - UT;
			TU = (jd - 2451545.0) / 36525;
			GMST = 24110.54841 + TU * (8640184.812866 + TU * (0.093104 - TU * 6.2E-6));
			GMST = PLib.Modulus(GMST + PLib.secday * PLib.omega_E * UT, PLib.secday);

			return (PLib.twopi * GMST / PLib.secday);
		},

		Calculate_Solar_Position: function(time, solar_vector)
		{
			var mjd = 0.0, year = 0.0, T = 0.0, M = 0.0, L = 0.0, e = 0.0, C = 0.0, O = 0.0, Lsa = 0.0, nu = 0.0, R = 0.0, eps = 0.0;

			mjd  =  time - 2415020.0;
			year  =  1900  +  mjd / 365.25;
			T = (mjd + PLib.Delta_ET(year) / PLib.secday) / 36525.0;
			M = PLib.Radians(PLib.Modulus(358.47583 + PLib.Modulus(35999.04975 * T, 360.0) - (0.000150 + 0.0000033 * T) * PLib.Sqr(T), 360.0));
			L = PLib.Radians(PLib.Modulus(279.69668 + PLib.Modulus(36000.76892 * T, 360.0) + 0.0003025 * PLib.Sqr(T),360.0));
			e = 0.01675104 - (0.0000418 + 0.000000126 * T) * T;
			C = PLib.Radians((1.919460 - (0.004789 + 0.000014 * T) * T) * Math.sin(M) + (0.020094 - 0.000100 * T) * Math.sin(2 * M) + 0.000293 * Math.sin(3 * M));
			O = PLib.Radians(PLib.Modulus(259.18-1934.142 * T, 360.0));
			Lsa = PLib.Modulus(L + C - PLib.Radians(0.00569-0.00479 * Math.sin(O)), PLib.twopi);
			nu = PLib.Modulus(M + C, PLib.twopi);
			R = 1.0000002 * (1.0 - PLib.Sqr(e)) / (1.0 + e * Math.cos(nu));
			eps = PLib.Radians(23.452294 - (0.0130125 + (0.00000164 - 0.000000503 * T) * T) * T + 0.00256 * Math.cos(O));
			R = PLib.AU * R;
			solar_vector.x = R * Math.cos(Lsa);
			solar_vector.y = R * Math.sin(Lsa) * Math.cos(eps);
			solar_vector.z = R * Math.sin(Lsa) * Math.sin(eps);
			solar_vector.w = R;
		},

		Sat_Eclipsed: function(pos, sol)
		{
			var sd_sun = 0.0, sd_earth = 0.0, delta = 0.0;
			var Rho = new PLib.vector_t();
			var earth = new PLib.vector_t();;

			sd_earth = Math.asin(PLib.xkmper / pos.w);
			PLib.Vec_Sub(sol, pos, Rho);
			sd_sun = Math.asin(PLib.sr / Rho.w);
			PLib.Scalar_Multiply(-1, pos, earth);
			delta = PLib.Angle(sol, earth);
			PLib.eclipse_depth = sd_earth - sd_sun - delta;

			if (sd_earth < sd_sun)
				return 0;
			else
				if (PLib.eclipse_depth >= 0)
					return 1;
			else
				return 0;
		},

		select_ephemeris: function(tle)
		{
			var ao = 0.0, xnodp = 0.0, dd1 = 0.0, dd2 = 0.0, delo = 0.0, temp = 0.0, a1 = 0.0, del1 = 0.0, r1 = 0.0;

			tle.xnodeo *= PLib.deg2rad;
			tle.omegao *= PLib.deg2rad;
			tle.xmo *= PLib.deg2rad;
			tle.xincl *= PLib.deg2rad;
			temp = PLib.twopi / PLib.xmnpda / PLib.xmnpda;
			tle.xno = tle.xno * temp * PLib.xmnpda;
			tle.xndt2o *= temp;
			tle.xndd6o = tle.xndd6o * temp / PLib.xmnpda;
			tle.bstar /= PLib.ae;

			dd1 = (PLib.xke / tle.xno);
			dd2 = PLib.tothrd;
			a1 = Math.pow(dd1, dd2);
			r1 = Math.cos(tle.xincl);
			dd1 = (1.0 - tle.eo * tle.eo);
			temp = PLib.ck2 * 1.5 * (r1 * r1 * 3.0 - 1.0) / Math.pow(dd1, 1.5);
			del1 = temp / (a1 * a1);
			ao = a1 * (1.0 - del1 * (PLib.tothrd * 0.5 + del1 * (del1 * 1.654320987654321 + 1.0)));
			delo = temp / (ao * ao);
			xnodp = tle.xno / (delo + 1.0);

			if (PLib.twopi / PLib.xnodp / PLib.xmnpda >= 0.15625)
				PLib.SetFlag(PLib.DEEP_SPACE_EPHEM_FLAG);
			else
				PLib.ClearFlag(PLib.DEEP_SPACE_EPHEM_FLAG);
		},

		SGP4: function(tsince, tle, pos, vel)
		{
			var cosuk = 0.0, sinuk = 0.0, rfdotk = 0.0, vx = 0.0, vy = 0.0, vz = 0.0, ux = 0.0, uy = 0.0, uz = 0.0, xmy = 0.0, xmx = 0.0, cosnok = 0.0,
				sinnok = 0.0, cosik = 0.0, sinik = 0.0, rdotk = 0.0, xinck = 0.0, xnodek = 0.0, uk = 0.0, rk = 0.0, cos2u = 0.0, sin2u = 0.0,
				u = 0.0, sinu = 0.0, cosu = 0.0, betal = 0.0, rfdot = 0.0, rdot = 0.0, r = 0.0, pl = 0.0, elsq = 0.0, esine = 0.0, ecose = 0.0, epw = 0.0,
				cosepw = 0.0, x1m5th = 0.0, xhdot1 = 0.0, tfour = 0.0, sinepw = 0.0, capu = 0.0, ayn = 0.0, xlt = 0.0, aynl = 0.0, xll = 0.0,
				axn = 0.0, xn = 0.0, beta = 0.0, xl = 0.0, e = 0.0, a = 0.0, tcube = 0.0, delm = 0.0, delomg = 0.0, templ = 0.0, tempe = 0.0, tempa = 0.0,
				xnode = 0.0, tsq = 0.0, xmp = 0.0, omega = 0.0, xnoddf = 0.0, omgadf = 0.0, xmdf = 0.0, a1 = 0.0, a3ovk2 = 0.0, ao = 0.0,
				betao = 0.0, betao2 = 0.0, c1sq = 0.0, c2 = 0.0, c3 = 0.0, coef = 0.0, coef1 = 0.0, del1 = 0.0, delo = 0.0, eeta = 0.0, eosq = 0.0,
				etasq = 0.0, perigee = 0.0, pinvsq = 0.0, psisq = 0.0, qoms24 = 0.0, s4 = 0.0, temp = 0.0, temp1 = 0.0, temp2 = 0.0,
				temp3 = 0.0, temp4 = 0.0, temp5 = 0.0, temp6 = 0.0, theta2 = 0.0, theta4 = 0.0, tsi = 0.0;

			var i = 0;

			if (PLib.isFlagClear(PLib.SGP4_INITIALIZED_FLAG))
			{
				PLib.SetFlag(PLib.SGP4_INITIALIZED_FLAG);

				a1 = Math.pow(PLib.xke / tle.xno, PLib.tothrd);
				PLib.SGP4.cosio = Math.cos(tle.xincl);
				theta2 = PLib.SGP4.cosio * PLib.SGP4.cosio;
				PLib.SGP4.x3thm1 = 3 * theta2 - 1.0;
				eosq = tle.eo * tle.eo;
				betao2 = 1.0 - eosq;
				betao = Math.sqrt(betao2);
				del1 = 1.5 * PLib.ck2 * PLib.SGP4.x3thm1 / (a1 * a1 * betao * betao2);
				ao = a1 * (1.0 - del1 * (0.5 * PLib.tothrd + del1 * (1.0 + 134.0 / 81.0 * del1)));
				delo = 1.5 * PLib.ck2 * PLib.SGP4.x3thm1 / (ao * ao * betao * betao2);
				PLib.SGP4.xnodp = tle.xno / (1.0 + delo);
				PLib.SGP4.aodp = ao / (1.0 - delo);

				if ((PLib.SGP4.aodp * (1 - tle.eo) / PLib.ae) < (220 / PLib.xkmper + PLib.ae))
				    PLib.SetFlag(PLib.SIMPLE_FLAG);
				else
				    PLib.ClearFlag(PLib.SIMPLE_FLAG);

				s4 = PLib.s;
				PLib.qoms24 = PLib.qoms2t;
				PLib.perigee = (PLib.SGP4.aodp * (1 - tle.eo) - PLib.ae) * PLib.xkmper;

				if (PLib.perigee < 156.0)
				{
					if (PLib.perigee <= 98.0)
						s4 = 20;
					else
						s4 = PLib.perigee - 78.0;

					PLib.qoms24 = Math.pow((120 - s4) * PLib.ae / PLib.xkmper, 4);
					s4 = s4 / PLib.xkmper + PLib.ae;
				}

				pinvsq = 1 / (PLib.SGP4.aodp * PLib.SGP4.aodp * betao2 * betao2);
				tsi = 1 / (PLib.SGP4.aodp - s4);
				PLib.SGP4.eta = PLib.SGP4.aodp * tle.eo * tsi;
				etasq = PLib.SGP4.eta * PLib.SGP4.eta;
				eeta = tle.eo * PLib.SGP4.eta;
				psisq = Math.abs(1 - etasq);
				coef = qoms24 * Math.pow(tsi, 4);
				coef1 = coef / Math.pow(psisq, 3.5);
				c2 = coef1 * PLib.SGP4.xnodp * (PLib.SGP4.aodp * (1 + 1.5 * etasq + eeta * (4 + etasq)) + 0.75 * PLib.ck2 * tsi / psisq * PLib.SGP4.x3thm1 * (8 + 3 * etasq * (8 + etasq)));
				PLib.SGP4.c1 = tle.bstar * c2;
				PLib.SGP4.sinio = Math.sin(tle.xincl);
				a3ovk2 = -PLib.xj3 / PLib.ck2 * Math.pow(PLib.ae, 3);
				c3 = coef * tsi * a3ovk2 * PLib.SGP4.xnodp * PLib.ae * PLib.SGP4.sinio / tle.eo;
				PLib.SGP4.x1mth2 = 1 - theta2;

				PLib.SGP4.c4 = 2 * PLib.SGP4.xnodp * coef1 * PLib.SGP4.aodp * betao2 * (PLib.SGP4.eta * (2 + 0.5 * etasq)+tle.eo * (0.5 + 2 * etasq) - 2 * PLib.ck2 * tsi / (PLib.SGP4.aodp * psisq) * (-3 * PLib.SGP4.x3thm1 * (1 - 2 * eeta + etasq * (1.5 - 0.5 * eeta)) + 0.75 * PLib.SGP4.x1mth2 * (2 * etasq-eeta * (1 + etasq)) * Math.cos(2 * tle.omegao)));
				PLib.SGP4.c5 = 2 * coef1 * PLib.SGP4.aodp * betao2 * (1 + 2.75 * (etasq + eeta) + eeta * etasq);

				theta4 = theta2 * theta2;
				temp1 = 3 * PLib.ck2 * pinvsq * PLib.SGP4.xnodp;
				temp2 = temp1 * PLib.ck2 * pinvsq;
				temp3 = 1.25 * PLib.ck4 * pinvsq * pinvsq * PLib.SGP4.xnodp;
				PLib.SGP4.xmdot = PLib.SGP4.xnodp + 0.5 * temp1 * betao * PLib.SGP4.x3thm1 + 0.0625 * temp2 * betao * (13 - 78 * theta2 + 137 * theta4);
				x1m5th = 1 - 5 * theta2;
				PLib.SGP4.omgdot = -0.5 * temp1 * x1m5th + 0.0625 * temp2 * (7 - 114 * theta2 + 395 * theta4) + temp3 * (3 - 36 * theta2 + 49 * theta4);
				xhdot1 = -temp1 * PLib.SGP4.cosio;
				PLib.SGP4.xnodot = xhdot1 + (0.5 * temp2 * (4 - 19 * theta2) + 2 * temp3 * (3 - 7 * theta2)) * PLib.SGP4.cosio;
				PLib.SGP4.omgcof = tle.bstar * c3 * Math.cos(tle.omegao);
				PLib.SGP4.xmcof = -PLib.tothrd * coef * tle.bstar * PLib.ae / eeta;
				PLib.SGP4.xnodcf = 3.5 * betao2 * xhdot1 * PLib.SGP4.c1;
				PLib.SGP4.t2cof = 1.5 * PLib.SGP4.c1;
				PLib.SGP4.xlcof = 0.125 * a3ovk2 * PLib.SGP4.sinio * (3 + 5 * PLib.SGP4.cosio) / (1 + PLib.SGP4.cosio);
				PLib.SGP4.aycof = 0.25 * a3ovk2 * PLib.SGP4.sinio;
				PLib.SGP4.delmo = Math.pow(1 + PLib.SGP4.eta * Math.cos(tle.xmo), 3);
				PLib.SGP4.sinmo = Math.sin(tle.xmo);
				PLib.SGP4.x7thm1 = 7 * theta2 - 1;

				if (PLib.isFlagClear(PLib.SIMPLE_FLAG))
				{
					c1sq = PLib.SGP4.c1 * PLib.SGP4.c1;
					PLib.SGP4.d2 = 4 * PLib.SGP4.aodp * tsi * c1sq;
					temp = PLib.SGP4.d2 * tsi * PLib.SGP4.c1/3;
					PLib.SGP4.d3 = (17 * PLib.SGP4.aodp + s4) * temp;
					PLib.SGP4.d4 = 0.5 * temp * PLib.SGP4.aodp * tsi * (221 * PLib.SGP4.aodp + 31 * s4) * PLib.SGP4.c1;
					PLib.SGP4.t3cof = PLib.SGP4.d2 + 2 * c1sq;
					PLib.SGP4.t4cof = 0.25 * (3 * PLib.SGP4.d3 + PLib.SGP4.c1 * (12 * PLib.SGP4.d2 + 10 * c1sq));
					PLib.SGP4.t5cof = 0.2 * (3 * PLib.SGP4.d4 + 12 * PLib.SGP4.c1 * PLib.SGP4.d3 + 6 * PLib.SGP4.d2 * PLib.SGP4.d2 + 15 * c1sq * (2 * PLib.SGP4.d2 + c1sq));
				}
			}

			xmdf = tle.xmo + PLib.SGP4.xmdot * tsince;
			omgadf = tle.omegao + PLib.SGP4.omgdot * tsince;
			xnoddf = tle.xnodeo + PLib.SGP4.xnodot * tsince;
			omega = omgadf;
			xmp = xmdf;
			tsq = tsince * tsince;
			xnode = xnoddf + PLib.SGP4.xnodcf * tsq;
			tempa = 1 - PLib.SGP4.c1 * tsince;
			tempe = tle.bstar * PLib.SGP4.c4 * tsince;
			templ = PLib.SGP4.t2cof * tsq;

			if (PLib.isFlagClear(PLib.SIMPLE_FLAG))
			{
				delomg = PLib.SGP4.omgcof * tsince;
				delm = PLib.SGP4.xmcof * (Math.pow(1 + PLib.SGP4.eta * Math.cos(xmdf), 3) - PLib.SGP4.delmo);
				temp = delomg + delm;
				xmp = xmdf + temp;
				omega = omgadf - temp;
				tcube = tsq * tsince;
				tfour = tsince * tcube;
				tempa = tempa - PLib.SGP4.d2 * tsq - PLib.SGP4.d3 * tcube - PLib.SGP4.d4 * tfour;
				tempe = tempe + tle.bstar * PLib.SGP4.c5 * (Math.sin(xmp) - PLib.SGP4.sinmo);
				templ = templ + PLib.SGP4.t3cof * tcube + tfour * (PLib.SGP4.t4cof + tsince * PLib.SGP4.t5cof);
			}

			a = PLib.SGP4.aodp * Math.pow(tempa, 2);
			e = tle.eo - tempe;
			xl = xmp + omega + xnode + PLib.SGP4.xnodp * templ;
			beta = Math.sqrt(1 - e * e);
			xn = PLib.xke / Math.pow(a, 1.5);

			axn = e * Math.cos(omega);
			temp = 1 / (a * beta * beta);
			xll = temp * PLib.SGP4.xlcof * axn;
			aynl = temp * PLib.SGP4.aycof;
			xlt = xl + xll;
			ayn = e * Math.sin(omega) + aynl;

			capu = PLib.FMod2p(xlt - xnode);
			temp2 = capu;
			i = 0;

			do
			{
				sinepw = Math.sin(temp2);
				cosepw = Math.cos(temp2);
				temp3 = axn * sinepw;
				temp4 = ayn * cosepw;
				temp5 = axn * cosepw;
				temp6 = ayn * sinepw;
				epw = (capu - temp4 + temp3 - temp2) / (1 - temp5 - temp6) + temp2;

				if (Math.abs(epw - temp2) <= PLib.e6a)
					break;

				temp2 = epw;
			} while (i++ < 10);

			ecose = temp5 + temp6;
			esine = temp3 - temp4;
			elsq = axn * axn + ayn * ayn;
			temp = 1 - elsq;
			pl = a * temp;
			r = a * (1 - ecose);
			temp1 = 1 / r;
			rdot = PLib.xke * Math.sqrt(a) * esine * temp1;
			rfdot = PLib.xke * Math.sqrt(pl) * temp1;
			temp2 = a * temp1;
			betal = Math.sqrt(temp);
			temp3 = 1 / (1 + betal);
			cosu = temp2 * (cosepw - axn + ayn * esine * temp3);
			sinu = temp2 * (sinepw - ayn - axn * esine * temp3);
			u = Math.atan2(sinu, cosu);
			sin2u = 2 * sinu * cosu;
			cos2u = 2 * cosu * cosu - 1;
			temp = 1 / pl;
			temp1 = PLib.ck2 * temp;
			temp2 = temp1 * temp;

			rk = r * (1 - 1.5 * temp2 * betal * PLib.SGP4.x3thm1) + 0.5 * temp1 * PLib.SGP4.x1mth2 * cos2u;
			uk = u - 0.25 * temp2 * PLib.SGP4.x7thm1 * sin2u;
			xnodek = xnode + 1.5 * temp2 * PLib.SGP4.cosio * sin2u;
			xinck = tle.xincl + 1.5 * temp2 * PLib.SGP4.cosio * PLib.SGP4.sinio * cos2u;
			rdotk = rdot - xn * temp1 * PLib.SGP4.x1mth2 * sin2u;
			rfdotk = rfdot + xn * temp1 * (PLib.SGP4.x1mth2 * cos2u + 1.5 * PLib.SGP4.x3thm1);

			sinuk = Math.sin(uk);
			cosuk = Math.cos(uk);
			sinik = Math.sin(xinck);
			cosik = Math.cos(xinck);
			sinnok = Math.sin(xnodek);
			cosnok = Math.cos(xnodek);
			xmx = -sinnok * cosik;
			xmy = cosnok * cosik;
			ux = xmx * sinuk+cosnok * cosuk;
			uy = xmy * sinuk+sinnok * cosuk;
			uz = sinik * sinuk;
			vx = xmx * cosuk-cosnok * sinuk;
			vy = xmy * cosuk-sinnok * sinuk;
			vz = sinik * cosuk;

			pos.x = rk * ux;
			pos.y = rk * uy;
			pos.z = rk * uz;
			vel.x = rdotk * ux + rfdotk * vx;
			vel.y = rdotk * uy + rfdotk * vy;
			vel.z = rdotk * uz + rfdotk * vz;

			PLib.phase = xlt - xnode - omgadf + PLib.twopi;

			if (PLib.phase < 0.0)
				PLib.phase += PLib.twopi;

			PLib.phase = PLib.FMod2p(PLib.phase);
		},

		Deep: function(ientry, tle, deep_arg)
		{
			var a1 = 0.0, a2 = 0.0, a3 = 0.0, a4 = 0.0, a5 = 0.0, a6 = 0.0, a7 = 0.0, a8 = 0.0, a9 = 0.0, a10 = 0.0, ainv2 = 0.0, alfdp = 0.0, aqnv = 0.0,
				sgh = 0.0, sini2 = 0.0, sinis = 0.0, sinok = 0.0, sh = 0.0, si = 0.0, sil = 0.0, day = 0.0, betdp = 0.0, dalf = 0.0, bfact = 0.0, c = 0.0,
				cc = 0.0, cosis = 0.0, cosok = 0.0, cosq = 0.0, ctem = 0.0, f322 = 0.0, zx = 0.0, zy = 0.0, dbet = 0.0, dls = 0.0, eoc = 0.0, eq = 0.0, f2 = 0.0,
				f220 = 0.0, f221 = 0.0, f3 = 0.0, f311 = 0.0, f321 = 0.0, xnoh = 0.0, f330 = 0.0, f441 = 0.0, f442 = 0.0, f522 = 0.0, f523 = 0.0,
				f542 = 0.0, f543 = 0.0, g200 = 0.0, g201 = 0.0, g211 = 0.0, pgh = 0.0, ph = 0.0, s1 = 0.0, s2 = 0.0, s3 = 0.0, s4 = 0.0, s5 = 0.0, s6 = 0.0, s7 = 0.0,
				se = 0.0, sel = 0.0, ses = 0.0, xls = 0.0, g300 = 0.0, g310 = 0.0, g322 = 0.0, g410 = 0.0, g422 = 0.0, g520 = 0.0, g521 = 0.0, g532 = 0.0,
				g533 = 0.0, gam = 0.0, sinq = 0.0, sinzf = 0.0, sis = 0.0, sl = 0.0, sll = 0.0, sls = 0.0, stem = 0.0, temp = 0.0, temp1 = 0.0, x1 = 0.0,
				x2 = 0.0, x2li = 0.0, x2omi = 0.0, x3 = 0.0, x4 = 0.0, x5 = 0.0, x6 = 0.0, x7 = 0.0, x8 = 0.0, xl = 0.0, xldot = 0.0, xmao = 0.0, xnddt = 0.0,
				xndot = 0.0, xno2 = 0.0, xnodce = 0.0, xnoi = 0.0, xomi = 0.0, xpidot = 0.0, z1 = 0.0, z11 = 0.0, z12 = 0.0, z13 = 0.0, z2 = 0.0,
				z21 = 0.0, z22 = 0.0, z23 = 0.0, z3 = 0.0, z31 = 0.0, z32 = 0.0, z33 = 0.0, ze = 0.0, zf = 0.0, zm = 0.0, zmo = 0.0, zn = 0.0, zsing = 0.0,
				zsinh = 0.0, zsini = 0.0, zcosg = 0.0, zcosh = 0.0, zcosi = 0.0, delt = 0.0, ft = 0.0;
		
			switch (ientry)
			{
				case dpinit:
				PLib.Deep.thgr = PLib.ThetaG(tle.epoch, deep_arg);
				eq = tle.eo;
				PLib.Deep.xnq = deep_arg.xnodp;
				aqnv = 1 / deep_arg.aodp;
				PLib.Deep.xqncl = tle.xincl;
				xmao = tle.xmo;
				xpidot = deep_arg.omgdot + deep_arg.xnodot;
				sinq = Math.sin(tle.xnodeo);
				cosq = Math.cos(tle.xnodeo);
				PLib.Deep.omegaq = tle.omegao;
		
				day = deep_arg.ds50 + 18261.5;
		
				if (day != PLib.Deep.preep)
				{
					PLib.Deep.preep = day;
					xnodce = 4.5236020 - 9.2422029E-4 * day;
					stem = Math.sin(xnodce);
					ctem = Math.cos(xnodce);
					PLib.Deep.zcosil = 0.91375164 - 0.03568096 * ctem;
					PLib.Deep.zsinil = Math.sqrt(1 - PLib.Deep.zcosil * PLib.Deep.zcosil);
					PLib.Deep.zsinhl = 0.089683511 * stem / PLib.Deep.zsinil;
					PLib.Deep.zcoshl = Math.sqrt(1 - PLib.Deep.zsinhl * PLib.Deep.zsinhl);
					c = 4.7199672 + 0.22997150 * day;
					gam = 5.8351514 + 0.0019443680 * day;
					PLib.Deep.zmol = PLib.FMod2p(c - gam);
					zx = 0.39785416 * stem / PLib.Deep.zsinil;
					zy = PLib.Deep.zcoshl * ctem + 0.91744867 * PLib.Deep.zsinhl * stem;
					zx = Math.atan2(zx, zy);
					zx = gam + zx - xnodce;
					PLib.Deep.zcosgl = Math.cos(zx);
					PLib.Deep.zsingl = Math.sin(zx);
					PLib.Deep.zmos = 6.2565837 + 0.017201977 * day;
					PLib.Deep.zmos = PLib.FMod2p(PLib.Deep.zmos);
				    }
		
				  PLib.Deep.savtsn = 1E20;
				  zcosg = PLib.zcosgs;
				  zsing = PLib.zsings;
				  zcosi = PLib.zcosis;
				  zsini = PLib.zsinis;
				  zcosh = cosq;
				  zsinh = sinq;
				  cc = PLib.c1ss;
				  zn = PLib.zns;
				  ze = PLib.zes;
				  zmo = PLib.Deep.zmos;
				  xnoi = 1 / PLib.Deep.xnq;
		
				for (;;)
				{
					a1 = zcosg * zcosh + zsing * zcosi * zsinh;
					a3 = -zsing * zcosh + zcosg * zcosi * zsinh;
					a7 = -zcosg * zsinh + zsing * zcosi * zcosh;
					a8 = zsing * zsini;
					a9 = zsing * zsinh + zcosg * zcosi * zcosh;
					a10 = zcosg * zsini;
					a2 = deep_arg.cosio * a7 + deep_arg.sinio * a8;
					a4 = deep_arg.cosio * a9 + deep_arg.sinio * a10;
					a5 = -deep_arg.sinio * a7 + deep_arg.cosio * a8;
					a6 = -deep_arg.sinio * a9 + deep_arg.cosio * a10;
					x1 = a1 * deep_arg.cosg + a2 * deep_arg.sing;
					x2 = a3 * deep_arg.cosg + a4 * deep_arg.sing;
					x3 = -a1 * deep_arg.sing + a2 * deep_arg.cosg;
					x4 = -a3 * deep_arg.sing + a4 * deep_arg.cosg;
					x5 = a5 * deep_arg.sing;
					x6 = a6 * deep_arg.sing;
					x7 = a5 * deep_arg.cosg;
					x8 = a6 * deep_arg.cosg;
					z31 = 12 * x1 * x1 - 3 * x3 * x3;
					z32 = 24 * x1 * x2 - 6 * x3 * x4;
					z33 = 12 * x2 * x2 - 3 * x4 * x4;
					z1 = 3 * (a1 * a1 + a2 * a2) + z31 * deep_arg.eosq;
					z2 = 6 * (a1 * a3 + a2 * a4) + z32 * deep_arg.eosq;
					z3 = 3 * (a3 * a3 + a4 * a4) + z33 * deep_arg.eosq;
					z11 = -6 * a1 * a5 + deep_arg.eosq * (-24 * x1 * x7 - 6 * x3 * x5);
					z12 = -6 * (a1 * a6 + a3 * a5) + deep_arg.eosq * (-24 * (x2 * x7 + x1 * x8) - 6 * (x3 * x6 + x4 * x5));
					z13 = -6 * a3 * a6 + deep_arg.eosq * (-24 * x2 * x8 - 6 * x4 * x6);
					z21 = 6 * a2 * a5 + deep_arg.eosq * (24 * x1 * x5 - 6 * x3 * x7);
					z22 = 6 * (a4 * a5 + a2 * a6) + deep_arg.eosq * (24 * (x2 * x5 + x1 * x6) - 6 * (x4 * x7 + x3 * x8));
					z23 = 6 * a4 * a6 + deep_arg.eosq * (24 * x2 * x6 - 6 * x4 * x8);
					z1 = z1 + z1 + deep_arg.betao2 * z31;
					z2 = z2 + z2 + deep_arg.betao2 * z32;
					z3 = z3 + z3 + deep_arg.betao2 * z33;
					s3 = cc * xnoi;
					s2 = -0.5 * s3 / deep_arg.betao;
					s4 = s3 * deep_arg.betao;
					s1 = -15 * eq * s4;
					s5 = x1 * x3 + x2 * x4;
					s6 = x2 * x3 + x1 * x4;
					s7 = x2 * x4 - x1 * x3;
					se = s1 * zn * s5;
					si = s2 * zn * (z11 + z13);
					sl = -zn * s3 * (z1 + z3 - 14 - 6 * deep_arg.eosq);
					sgh = s4 * zn * (z31 + z33 - 6);
					sh = -zn * s2 * (z21 + z23);
		
					if (PLib.Deep.xqncl < 5.2359877E-2)
						sh = 0;
		
					PLib.Deep.ee2 = 2 * s1 * s6;
					PLib.Deep.e3 = 2 * s1 * s7;
					PLib.Deep.xi2 = 2 * s2 * z12;
					PLib.Deep.xi3 = 2 * s2 * (z13 - z11);
					PLib.Deep.xl2 = -2 * s3 * z2;
					PLib.Deep.xl3 = -2 * s3 * (z3 - z1);
					PLib.Deep.xl4 = -2 * s3 * (-21 - 9 * deep_arg.eosq) * ze;
					PLib.Deep.xgh2 = 2 * s4 * z32;
					PLib.Deep.xgh3 = 2 * s4 * (z33 - z31);
					PLib.Deep.xgh4 = -18 * s4 * ze;
					PLib.Deep.xh2 = -2 * s2 * z22;
					PLib.Deep.xh3 = -2 * s2  * (z23 - z21);
		
					if (PLib.isFlagSet(PLib.LUNAR_TERMS_DONE_FLAG))
						break;
		
					PLib.Deep.sse = se;
					PLib.Deep.ssi = si;
					PLib.Deep.ssl = sl;
					PLib.Deep.ssh = sh / deep_arg.sinio;
					PLib.Deep.ssg = sgh - deep_arg.cosio * PLib.Deep.ssh;
					PLib.Deep.se2 = PLib.Deep.ee2;
					PLib.Deep.si2 = PLib.Deep.xi2;
					PLib.Deep.sl2 = PLib.Deep.xl2;
					PLib.Deep.sgh2 = PLib.Deep.xgh2;
					PLib.Deep.sh2 = PLib.Deep.xh2;
					PLib.Deep.se3 = PLib.Deep.e3;
					PLib.Deep.si3 = PLib.Deep.xi3;
					PLib.Deep.sl3 = PLib.Deep.xl3;
					PLib.Deep.sgh3 = PLib.Deep.xgh3;
					PLib.Deep.sh3 = PLib.Deep.xh3;
					PLib.Deep.sl4 = PLib.Deep.xl4;
					PLib.Deep.sgh4 = PLib.Deep.xgh4;
					zcosg = PLib.Deep.zcosgl;
					zsing = PLib.Deep.zsingl;
					zcosi = PLib.Deep.zcosil;
					zsini = PLib.Deep.zsinil;
					zcosh = PLib.Deep.zcoshl * cosq + PLib.Deep.zsinhl * sinq;
					zsinh = sinq * PLib.Deep.zcoshl - cosq * PLib.Deep.zsinhl;
					zn = PLib.znl;
					cc = PLib.c1l;
					ze = PLib.zel;
					zmo = PLib.Deep.zmol;
					PLib.SetFlag(PLib.LUNAR_TERMS_DONE_FLAG);
				}
		
				PLib.Deep.sse = PLib.Deep.sse + se;
				PLib.Deep.ssi = PLib.Deep.ssi + si;
				PLib.Deep.ssl = PLib.Deep.ssl + sl;
				PLib.Deep.ssg = PLib.Deep.ssg + sgh - deep_arg.cosio / deep_arg.sinio * sh;
				PLib.Deep.ssh = PLib.Deep.ssh + sh / deep_arg.sinio;
		
				PLib.ClearFlag(PLib.RESONANCE_FLAG);
				PLib.ClearFlag(PLib.SYNCHRONOUS_FLAG);
		
				if (!((PLib.Deep.xnq < 0.0052359877) && (PLib.Deep.xnq > 0.0034906585)))
				{
					if ((PLib.Deep.xnq < 0.00826) || (PLib.Deep.xnq > 0.00924))
					    return;
		
					if (eq < 0.5)
					    return;
		
					PLib.SetFlag(PLib.RESONANCE_FLAG);
					eoc = eq * deep_arg.eosq;
					g201 = -0.306 - (eq - 0.64) * 0.440;
		
					if (eq <= 0.65)
					{
						g211 = 3.616 - 13.247 * eq + 16.290 * deep_arg.eosq;
						g310 = -19.302 + 117.390 * eq - 228.419 * deep_arg.eosq + 156.591 * eoc;
						g322 = -18.9068 + 109.7927 * eq - 214.6334 * deep_arg.eosq + 146.5816 * eoc;
						g410 = -41.122 + 242.694 * eq - 471.094 * deep_arg.eosq + 313.953 * eoc;
						g422 = -146.407 + 841.880 * eq - 1629.014 * deep_arg.eosq + 1083.435 * eoc;
						g520 = -532.114 + 3017.977 * eq - 5740 * deep_arg.eosq + 3708.276 * eoc;
					}
		
					else
					{
						g211 = -72.099 + 331.819 * eq - 508.738 * deep_arg.eosq + 266.724 * eoc;
						g310 = -346.844 + 1582.851 * eq - 2415.925 * deep_arg.eosq + 1246.113 * eoc;
						g322 = -342.585 + 1554.908 * eq - 2366.899 * deep_arg.eosq + 1215.972 * eoc;
						g410 = -1052.797 + 4758.686 * eq - 7193.992 * deep_arg.eosq + 3651.957 * eoc;
						g422 = -3581.69 + 16178.11 * eq - 24462.77 * deep_arg.eosq + 12422.52 * eoc;
		
						if (eq <= 0.715)
							g520 = 1464.74 - 4664.75 * eq + 3763.64 * deep_arg.eosq;
		
						else
							g520 = -5149.66 + 29936.92 * eq - 54087.36 * deep_arg.eosq + 31324.56 * eoc;
					}
		
					if (eq < 0.7)
					{
						g533 = -919.2277 + 4988.61 * eq - 9064.77 * deep_arg.eosq + 5542.21 * eoc;
						g521 = -822.71072 + 4568.6173 * eq - 8491.4146 * deep_arg.eosq + 5337.524 * eoc;
						g532 = -853.666 + 4690.25 * eq - 8624.77 * deep_arg.eosq + 5341.4 * eoc;
					}
		
					else
					{
						g533 = -37995.78 + 161616.52 * eq - 229838.2 * deep_arg.eosq + 109377.94 * eoc;
						g521 = -51752.104 + 218913.95 * eq - 309468.16 * deep_arg.eosq + 146349.42 * eoc;
						g532 = -40023.88 + 170470.89 * eq - 242699.48 * deep_arg.eosq + 115605.82 * eoc;
					}
		
					sini2 = deep_arg.sinio * deep_arg.sinio;
					f220 = 0.75 * (1 + 2 * deep_arg.cosio + deep_arg.theta2);
					f221 = 1.5 * sini2;
					f321 = 1.875 * deep_arg.sinio * (1 - 2 * deep_arg.cosio - 3 * deep_arg.theta2);
					f322 = -1.875 * deep_arg.sinio * (1 + 2 * deep_arg.cosio - 3 * deep_arg.theta2);
					f441 = 35 * sini2 * f220;
					f442 = 39.3750 * sini2 * sini2;
					f522 = 9.84375 * deep_arg.sinio * (sini2 * (1 - 2 * deep_arg.cosio - 5 * deep_arg.theta2) + 0.33333333 * (-2 + 4 * deep_arg.cosio + 6 * deep_arg.theta2));
					f523 = deep_arg.sinio * (4.92187512 * sini2 * (-2 - 4 * deep_arg.cosio + 10 * deep_arg.theta2) + 6.56250012 * (1 + 2 * deep_arg.cosio-3 * deep_arg.theta2));
					f542 = 29.53125 * deep_arg.sinio * (2 - 8 * deep_arg.cosio + deep_arg.theta2 * (-12 + 8 * deep_arg.cosio+10 * deep_arg.theta2));
					f543 = 29.53125 * deep_arg.sinio * (-2 - 8 * deep_arg.cosio + deep_arg.theta2 * (12 + 8 * deep_arg.cosio-10 * deep_arg.theta2));
					xno2 = PLib.Deep.xnq * PLib.Deep.xnq;
					ainv2 = aqnv * aqnv;
					temp1 = 3 * xno2 * ainv2;
					temp = temp1 * PLib.root22;
					PLib.Deep.d2201 = temp * f220 * g201;
					PLib.Deep.d2211 = temp * f221 * g211;
					temp1 = temp1 * aqnv;
					temp = temp1 * PLib.root32;
					PLib.Deep.d3210 = temp * f321 * g310;
					PLib.Deep.d3222 = temp * f322 * g322;
					temp1 = temp1 * aqnv;
					temp = 2 * temp1 * PLib.root44;
					PLib.Deep.d4410 = temp * f441 * g410;
					PLib.Deep.d4422 = temp * f442 * g422;
					temp1 = temp1 * aqnv;
					temp = temp1 * PLib.root52;
					PLib.Deep.d5220 = temp * f522 * g520;
					PLib.Deep.d5232 = temp * f523 * g532;
					temp = 2 * temp1 * PLib.root54;
					PLib.Deep.d5421 = temp * f542 * g521;
					PLib.Deep.d5433 = temp * f543 * g533;
					PLib.Deep.xlamo = xmao + tle.xnodeo + tle.xnodeo - PLib.Deep.thgr - PLib.Deep.thgr;
					bfact = deep_arg.xmdot + deep_arg.xnodot + deep_arg.xnodot - PLib.thdt - PLib.thdt;
					bfact = bfact + PLib.Deep.ssl + PLib.Deep.ssh + PLib.Deep.ssh;
				}
		
				else
				{
					PLib.SetFlag(PLib.RESONANCE_FLAG);
					PLib.SetFlag(PLib.SYNCHRONOUS_FLAG);
		
					g200 = 1 + deep_arg.eosq * (-2.5 + 0.8125 * deep_arg.eosq);
					g310 = 1 + 2 * deep_arg.eosq;
					g300 = 1 + deep_arg.eosq * (-6 + 6.60937 * deep_arg.eosq);
					f220 = 0.75 * (1 + deep_arg.cosio) * (1 + deep_arg.cosio);
					f311 = 0.9375 * deep_arg.sinio * deep_arg.sinio * (1 + 3 * deep_arg.cosio) - 0.75 * (1 + deep_arg.cosio);
					f330 = 1 + deep_arg.cosio;
					f330 = 1.875 * f330 * f330 * f330;
					PLib.Deep.del1 = 3 * PLib.Deep.xnq * PLib.Deep.xnq * aqnv * aqnv;
					PLib.Deep.del2 = 2 * PLib.Deep.del1 * f220 * g200 * q22;
					PLib.Deep.del3 = 3 * PLib.Deep.del1 * f330 * g300 * q33 * aqnv;
					PLib.Deep.del1 = PLib.Deep.del1 * f311 * g310 * q31 * aqnv;
					PLib.Deep.fasx2 = 0.13130908;
					PLib.Deep.fasx4 = 2.8843198;
					PLib.Deep.fasx6 = 0.37448087;
					PLib.Deep.xlamo = xmao + tle.xnodeo + tle.omegao - PLib.Deep.thgr;
					bfact = deep_arg.xmdot + xpidot - PLib.thdt;
					bfact = bfact + PLib.Deep.ssl + PLib.Deep.ssg + PLib.Deep.ssh;
				}
		
				PLib.Deep.xfact = bfact - PLib.Deep.xnq;
		
				PLib.Deep.xli = PLib.Deep.xlamo;
				PLib.Deep.xni = PLib.Deep.xnq;
				PLib.Deep.atime = 0;
				PLib.Deep.stepp = 720;
				PLib.Deep.stepn = -720;
				PLib.Deep.step2 = 259200;
		
				return;
		
				case dpsec:  /* Entrance for deep space secular effects */
				deep_arg.xll = deep_arg.xll + PLib.Deep.ssl * deep_arg.t;
				deep_arg.omgadf = deep_arg.omgadf + PLib.Deep.ssg * deep_arg.t;
				deep_arg.xnode = deep_arg.xnode + PLib.Deep.ssh * deep_arg.t;
				deep_arg.em = tle.eo + PLib.Deep.sse * deep_arg.t;
				deep_arg.xinc = tle.xincl + PLib.Deep.ssi * deep_arg.t;
		
				if (deep_arg.xinc < 0)
				{
					deep_arg.xinc = -deep_arg.xinc;
					deep_arg.xnode = deep_arg.xnode + pi;
					deep_arg.omgadf = deep_arg.omgadf - pi;
				}
		
				if (PLib.isFlagClear(PLib.RESONANCE_FLAG))
				      return;
		
				do
				{
					if ((PLib.Deep.atime == 0) || ((deep_arg.t >= 0) && (PLib.Deep.atime < 0)) || ((deep_arg.t < 0) && (PLib.Deep.atime >= 0)))
					{
						if (deep_arg.t >= 0)
							delt = PLib.Deep.stepp;
						else
							delt = PLib.Deep.stepn;
		
						PLib.Deep.atime = 0;
						PLib.Deep.xni = PLib.Deep.xnq;
						PLib.Deep.xli = PLib.Deep.xlamo;
					}
		
					else
					{
						if (Math.abs(deep_arg.t) >= Math.abs(PLib.Deep.atime))
						{
							if (deep_arg.t > 0)
								delt = PLib.Deep.stepp;
							else
								delt = PLib.Deep.stepn;
						}
					}
		
					do
					{
						if (Math.abs(deep_arg.t - PLib.Deep.atime) >= PLib.Deep.stepp)
						{
							PLib.SetFlag(PLib.DO_LOOP_FLAG);
							PLib.ClearFlag(PLib.EPOCH_RESTART_FLAG);
						}
		
						else
						{
							ft = deep_arg.t - PLib.Deep.atime;
							PLib.ClearFlag(PLib.DO_LOOP_FLAG);
						}
		
						if (Math.abs(deep_arg.t) < Math.abs(PLib.Deep.atime))
						{
							if (deep_arg.t >= 0)
								delt = PLib.Deep.stepn;
							else
								delt = PLib.Deep.stepp;
		
							PLib.SetFlag(PLib.DO_LOOP_FLAG | PLib.EPOCH_RESTART_FLAG);
						}
		
						if (PLib.isFlagSet(PLib.SYNCHRONOUS_FLAG))
						{
							xndot = PLib.Deep.del1 * Math.sin(PLib.Deep.xli - PLib.Deep.fasx2) + PLib.Deep.del2 * Math.sin(2 * (PLib.Deep.xli - PLib.Deep.fasx4)) + PLib.Deep.del3 * Math.sin(3 * (PLib.Deep.xli - PLib.Deep.fasx6));
							xnddt = PLib.Deep.del1 * Math.cos(PLib.Deep.xli - PLib.Deep.fasx2) + 2 * PLib.Deep.del2 * Math.cos(2 * (PLib.Deep.xli - PLib.Deep.fasx4)) + 3 * PLib.Deep.del3 * Math.cos(3 * (PLib.Deep.xli - PLib.Deep.fasx6));
						}
		
						else
						{
							xomi = PLib.Deep.omegaq + deep_arg.omgdot * PLib.Deep.atime;
							x2omi = xomi + xomi;
							x2li = PLib.Deep.xli + PLib.Deep.xli;
							xndot = PLib.Deep.d2201 * Math.sin(x2omi + PLib.Deep.xli - g22) + PLib.Deep.d2211 * Math.sin(PLib.Deep.xli - g22) + PLib.Deep.d3210 * Math.sin(xomi + PLib.Deep.xli - g32) + PLib.Deep.d3222 * Math.sin(-xomi + PLib.Deep.xli - g32) + PLib.Deep.d4410 * sin(x2omi + x2li - g44) + PLib.Deep.d4422 * sin(x2li - g44) + PLib.Deep.d5220 * sin(xomi + PLib.Deep.xli - g52) + PLib.Deep.d5232 * sin(-xomi + PLib.Deep.xli - g52) + PLib.Deep.d5421 * sin(xomi + x2li - g54) + PLib.Deep.d5433 * sin(-xomi + x2li - g54);
							xnddt = PLib.Deep.d2201 * Math.cos(x2omi + PLib.Deep.xli - g22) + PLib.Deep.d2211 * Math.cos(PLib.Deep.xli - g22) + PLib.Deep.d3210 * Math.cos(xomi + PLib.Deep.xli - g32) + PLib.Deep.d3222 * Math.cos(-xomi + PLib.Deep.xli - g32) + PLib.Deep.d5220 * cos(xomi + PLib.Deep.xli - g52) + PLib.Deep.d5232 * cos(-xomi + PLib.Deep.xli - g52) + 2 * (PLib.Deep.d4410 * cos(x2omi + x2li - g44) + PLib.Deep.d4422 * cos(x2li - g44) + PLib.Deep.d5421 * cos(xomi + x2li - g54) + PLib.Deep.d5433 * cos(-xomi + x2li - g54));
						}
		
						xldot = PLib.Deep.xni + PLib.Deep.xfact;
						xnddt = xnddt * xldot;
		
						if (PLib.isFlagSet(PLib.DO_LOOP_FLAG))
						{
							PLib.Deep.xli = PLib.Deep.xli + xldot * delt + xndot * PLib.Deep.step2;
							PLib.Deep.xni = PLib.Deep.xni + xndot * delt + xnddt * PLib.Deep.step2;
							PLib.Deep.atime = PLib.Deep.atime + delt;
						}
					} while (PLib.isFlagSet(PLib.DO_LOOP_FLAG) && PLib.isFlagClear(PLib.EPOCH_RESTART_FLAG));
				} while (PLib.isFlagSet(PLib.DO_LOOP_FLAG) && PLib.isFlagSet(PLib.EPOCH_RESTART_FLAG));
		
				deep_arg.xn = PLib.Deep.xni + xndot * ft + xnddt * ft * ft * 0.5;
				xl = PLib.Deep.xli + xldot * ft + xndot * ft * ft * 0.5;
				temp = -deep_arg.xnode + PLib.Deep.thgr + deep_arg.t * PLib.thdt;
		
				if (PLib.isFlagClear(PLib.SYNCHRONOUS_FLAG))
					deep_arg.xll = xl + temp + temp;
				else
					deep_arg.xll = xl - deep_arg.omgadf + temp;
		
				return;
		
				case dpper:
				sinis = Math.sin(deep_arg.xinc);
				cosis = Math.cos(deep_arg.xinc);
		
				if (Math.abs(PLib.Deep.savtsn - deep_arg.t) >= 30)
				{
					PLib.Deep.savtsn = deep_arg.t;
					zm = PLib.Deep.zmos + PLib.zns * deep_arg.t;
					zf = zm + 2 * PLib.zes * Math.sin(zm);
					sinzf = Math.sin(zf);
					f2 = 0.5 * sinzf * sinzf-0.25;
					f3 = -0.5 * sinzf * Math.cos(zf);
					ses = PLib.Deep.se2 * f2 + PLib.Deep.se3 * f3;
					sis = PLib.Deep.si2 * f2 + PLib.Deep.si3 * f3;
					sls = PLib.Deep.sl2 * f2 + PLib.Deep.sl3 * f3 + PLib.Deep.sl4 * sinzf;
					PLib.Deep.sghs = PLib.Deep.sgh2 * f2 + PLib.Deep.sgh3 * f3 + PLib.Deep.sgh4 * sinzf;
					PLib.Deep.shs = PLib.Deep.sh2 * f2 + PLib.Deep.sh3 * f3;
					zm = PLib.Deep.zmol + PLib.znl * deep_arg.t;
					zf = zm + 2 * zel * Math.sin(zm);
					sinzf = Math.sin(zf);
					f2 = 0.5 * sinzf * sinzf - 0.25;
					f3 = -0.5 * sinzf * Math.cos(zf);
					sel = PLib.Deep.ee2 * f2 + PLib.Deep.e3 * f3;
					sil = PLib.Deep.xi2 * f2 + PLib.Deep.xi3 * f3;
					sll = PLib.Deep.xl2 * f2 + PLib.Deep.xl3 * f3 + PLib.Deep.xl4 * sinzf;
					PLib.Deep.sghl = PLib.Deep.xgh2 * f2 + PLib.Deep.xgh3 * f3 + PLib.Deep.xgh4 * sinzf;
					PLib.Deep.sh1 = PLib.Deep.xh2 * f2 + PLib.Deep.xh3 * f3;
					PLib.Deep.pe = ses + sel;
					PLib.Deep.pinc = sis + sil;
					PLib.Deep.pl = sls + sll;
				}
		
				pgh = PLib.Deep.sghs + PLib.Deep.sghl;
				ph = PLib.Deep.shs + PLib.Deep.sh1;
				deep_arg.xinc = deep_arg.xinc + PLib.Deep.pinc;
				deep_arg.em = deep_arg.em + PLib.Deep.pe;
		
				if (PLib.Deep.xqncl >= 0.2)
				{
					ph = ph / deep_arg.sinio;
					pgh = pgh - deep_arg.cosio * ph;
					deep_arg.omgadf = deep_arg.omgadf + pgh;
					deep_arg.xnode = deep_arg.xnode + ph;
					deep_arg.xll = deep_arg.xll + PLib.Deep.pl;
				}
		
				else
				{
					sinok = Math.sin(deep_arg.xnode);
					cosok = Math.cos(deep_arg.xnode);
					alfdp = sinis * sinok;
					betdp = sinis * cosok;
					dalf = ph * cosok + PLib.Deep.pinc * cosis * sinok;
					dbet = -ph * sinok + PLib.Deep.pinc * cosis * cosok;
					alfdp = alfdp + dalf;
					betdp = betdp + dbet;
					deep_arg.xnode = FMod2p(deep_arg.xnode);
					xls = deep_arg.xll + deep_arg.omgadf + cosis * deep_arg.xnode;
					dls = PLib.Deep.pl + pgh - PLib.Deep.pinc * deep_arg.xnode * sinis;
					xls = xls + dls;
					xnoh = deep_arg.xnode;
					deep_arg.xnode = Math.atan2(alfdp, betdp);
		
					if (Math.abs(xnoh - deep_arg.xnode) > PLib.pi)
					{
					      if (deep_arg.xnode < xnoh)
						  deep_arg.xnode += PLib.twopi;
					      else
						  deep_arg.xnode -= PLib.twopi;
					}
		
					deep_arg.xll = deep_arg.xll + PLib.Deep.pl;
					deep_arg.omgadf = xls - deep_arg.xll - Math.cos(deep_arg.xinc) * deep_arg.xnode;
				}
				return;
			}
		},
		
		SDP4: function(tsince, tle, pos, vel)
		{
			var i = 0;
		
			var a = 0.0, axn = 0.0, ayn = 0.0, aynl = 0.0, beta = 0.0, betal = 0.0, capu = 0.0, cos2u = 0.0, cosepw = 0.0, cosik = 0.0,
				cosnok = 0.0, cosu = 0.0, cosuk = 0.0, ecose = 0.0, elsq = 0.0, epw = 0.0, esine = 0.0, pl = 0.0, theta4 = 0.0, rdot = 0.0,
				rdotk = 0.0, rfdot = 0.0, rfdotk = 0.0, rk = 0.0, sin2u = 0.0, sinepw = 0.0, sinik = 0.0, sinnok = 0.0, sinu = 0.0,
				sinuk = 0.0, tempe = 0.0, templ = 0.0, tsq = 0.0, u = 0.0, uk = 0.0, ux = 0.0, uy = 0.0, uz = 0.0, vx = 0.0, vy = 0.0, vz = 0.0, xinck = 0.0, xl = 0.0,
				xlt = 0.0, xmam = 0.0, xmdf = 0.0, xmx = 0.0, xmy = 0.0, xnoddf = 0.0, xnodek = 0.0, xll = 0.0, a1 = 0.0, a3ovk2 = 0.0, ao = 0.0, c2 = 0.0,
				coef = 0.0, coef1 = 0.0, x1m5th = 0.0, xhdot1 = 0.0, del1 = 0.0, r = 0.0, delo = 0.0, eeta = 0.0, eta = 0.0, etasq = 0.0,
				perigee = 0.0, psisq = 0.0, tsi = 0.0, qoms24 = 0.0, s4 = 0.0, pinvsq = 0.0, temp = 0.0, tempa = 0.0, temp1 = 0.0,
				temp2 = 0.0, temp3 = 0.0, temp4 = 0.0, temp5 = 0.0, temp6 = 0.0, bx = 0.0, by = 0.0, bz = 0.0, cx = 0.0, cy = 0.0, cz = 0.0;
		
			PLib.SDP4.deep_arg = PLib.SDP4.deep_arg || new PLib.deep_arg_t();
		
			if (PLib.isFlagClear(PLib.SDP4_INITIALIZED_FLAG))
			{
				PLib.SetFlag(PLib.SDP4_INITIALIZED_FLAG);
		
				a1 = Math.pow(PLib.xke / tle.xno, PLib.tothrd);
				PLib.SDP4.deep_arg.cosio = Math.cos(tle.xincl);
				PLib.SDP4.deep_arg.theta2 = PLib.SDP4.deep_arg.cosio * PLib.SDP4.deep_arg.cosio;
				PLib.SDP4.x3thm1 = 3 * PLib.SDP4.deep_arg.theta2 - 1;
				PLib.SDP4.deep_arg.eosq = tle.eo * tle.eo;
				PLib.SDP4.deep_arg.betao2 = 1 - PLib.SDP4.deep_arg.eosq;
				PLib.SDP4.deep_arg.betao = Math.sqrt(PLib.SDP4.deep_arg.betao2);
				del1 = 1.5 * PLib.ck2 * PLib.SDP4.x3thm1 / (a1 * a1 * PLib.SDP4.deep_arg.betao * PLib.SDP4.deep_arg.betao2);
				ao = a1 * (1 - del1 * (0.5 * PLib.tothrd + del1 * (1 + 134 / 81 * del1)));
				delo = 1.5 * PLib.ck2 * PLib.SDP4.x3thm1 / (ao * ao * PLib.SDP4.deep_arg.betao * PLib.SDP4.deep_arg.betao2);
				PLib.SDP4.deep_arg.xnodp = tle.xno / (1 + delo);
				PLib.SDP4.deep_arg.aodp = ao / (1 - delo);
		
				s4 = s;
				qoms24 = PLib.qoms2t;
				perigee = (PLib.SDP4.deep_arg.aodp * (1 - tle.eo) - PLib.ae) * PLib.xkmper;
			 
				if (perigee < 156.0)
				{
					if (perigee <= 98.0)
						s4 = 20.0;
					else
						s4 = perigee - 78.0;
		
					qoms24 = Math.pow((120 - s4) * PLib.ae / xkmper,4);
					s4 = s4 / PLib.xkmper + PLib.ae;
				}
		
				pinvsq = 1 / (PLib.SDP4.deep_arg.aodp * PLib.SDP4.deep_arg.aodp * PLib.SDP4.deep_arg.betao2 * PLib.SDP4.deep_arg.betao2);
				PLib.SDP4.deep_arg.sing = Math.sin(tle.omegao);
				PLib.SDP4.deep_arg.cosg = Math.cos(tle.omegao);
				tsi = 1 / (PLib.SDP4.deep_arg.aodp - s4);
				eta = PLib.SDP4.deep_arg.aodp * tle.eo * tsi;
				etasq = eta * eta;
				eeta = tle.eo * eta;
				psisq = Math.abs(1 - etasq);
				coef = qoms24 * Math.pow(tsi, 4);
				coef1 = coef / Math.pow(psisq, 3.5);
				c2 = coef1 * PLib.SDP4.deep_arg.xnodp * (PLib.SDP4.deep_arg.aodp * (1 + 1.5 * etasq + eeta * (4 + etasq)) + 0.75 * PLib.ck2 * tsi / psisq * PLib.SDP4.x3thm1 * (8 + 3 * etasq * (8 + etasq)));
				PLib.SDP4.c1 = tle.bstar * c2;
				PLib.SDP4.deep_arg.sinio = Math.sin(tle.xincl);
				a3ovk2 = -xj3 / ck2 * Math.pow(ae, 3);
				PLib.SDP4.x1mth2 = 1 -PLib.SDP4.deep_arg.theta2;
				PLib.SDP4.c4 = 2 * PLib.SDP4.deep_arg.xnodp * coef1 * PLib.SDP4.deep_arg.aodp * PLib.SDP4.deep_arg.betao2 * (eta * (2 + 0.5 * etasq) + tle.eo * (0.5 + 2 * etasq) - 2 * PLib.ck2 * tsi / (PLib.SDP4.deep_arg.aodp * psisq) * (-3 * PLib.SDP4.x3thm1 * (1 - 2 * eeta + etasq * (1.5 - 0.5 * eeta)) + 0.75 * PLib.SDP4.x1mth2 * (2 * etasq - eeta * (1 + etasq)) * Math.cos(2 * tle.omegao)));
				theta4 = PLib.SDP4.deep_arg.theta2 * PLib.SDP4.deep_arg.theta2;
				temp1 = 3 * PLib.ck2 * pinvsq * PLib.SDP4.deep_arg.xnodp;
				temp2 = temp1 * PLib.ck2 * pinvsq;
				temp3 = 1.25 * PLib.ck4 * pinvsq * pinvsq * PLib.SDP4.deep_arg.xnodp;
				PLib.SDP4.deep_arg.xmdot = PLib.SDP4.deep_arg.xnodp + 0.5 * temp1 * PLib.SDP4.deep_arg.betao * PLib.SDP4.x3thm1 + 0.0625 * temp2 * PLib.SDP4.deep_arg.betao * (13 - 78 * PLib.SDP4.deep_arg.theta2 + 137 * theta4);
				x1m5th = 1 - 5 * PLib.SDP4.deep_arg.theta2;
				PLib.SDP4.deep_arg.omgdot = -0.5 * temp1 * x1m5th + 0.0625 * temp2 * (7 - 114 * PLib.SDP4.deep_arg.theta2 + 395 * theta4) + temp3 * (3 - 36 * PLib.SDP4.deep_arg.theta2 + 49 * theta4);
				xhdot1 = -temp1 * PLib.SDP4.deep_arg.cosio;
				PLib.SDP4.deep_arg.xnodot = xhdot1 + (0.5 * temp2 * (4 - 19 * PLib.SDP4.deep_arg.theta2) + 2 * temp3 * (3 - 7 * PLib.SDP4.deep_arg.theta2)) * PLib.SDP4.deep_arg.cosio;
				PLib.SDP4.xnodcf = 3.5 * PLib.SDP4.deep_arg.betao2 * xhdot1 * PLib.SDP4.c1;
				PLib.SDP4.t2cof = 1.5 * PLib.SDP4.c1;
				PLib.SDP4.xlcof = 0.125 * a3ovk2 * PLib.SDP4.deep_arg.sinio * (3 + 5 * PLib.SDP4.deep_arg.cosio) / (1 + PLib.SDP4.deep_arg.cosio);
				PLib.SDP4.aycof = 0.25 * a3ovk2 * PLib.SDP4.deep_arg.sinio;
				PLib.SDP4.x7thm1 = 7 * PLib.SDP4.deep_arg.theta2 - 1;
		
				Deep(dpinit, tle, PLib.SDP4.deep_arg);
			}
		
			xmdf = tle.xmo + PLib.SDP4.deep_arg.xmdot * tsince;
			PLib.SDP4.deep_arg.omgadf = tle.omegao + PLib.SDP4.deep_arg.omgdot * tsince;
			xnoddf = tle.xnodeo + PLib.SDP4.deep_arg.xnodot * tsince;
			tsq = tsince * tsince;
			PLib.SDP4.deep_arg.xnode = xnoddf + PLib.SDP4.xnodcf * tsq;
			tempa = 1 - PLib.SDP4.c1 * tsince;
			tempe = tle.bstar * PLib.SDP4.c4 * tsince;
			templ = PLib.SDP4.t2cof * tsq;
			PLib.SDP4.deep_arg.xn = PLib.SDP4.deep_arg.xnodp;
		
			PLib.SDP4.deep_arg.xll = xmdf;
			PLib.SDP4.deep_arg.t = tsince;
		
			Deep(dpsec, tle, PLib.SDP4.deep_arg);
		
			xmdf = PLib.SDP4.deep_arg.xll;
			a = Math.pow(PLib.xke / PLib.SDP4.deep_arg.xn, PLib.tothrd) * tempa * tempa;
			PLib.SDP4.deep_arg.em = PLib.SDP4.deep_arg.em - tempe;
			xmam = xmdf + PLib.SDP4.deep_arg.xnodp * templ;
		
			PLib.SDP4.deep_arg.xll = xmam;
		
			Deep(PLib.dpper, tle, PLib.SDP4.deep_arg);
		
			xmam = PLib.SDP4.deep_arg.xll;
			xl = xmam + PLib.SDP4.deep_arg.omgadf + PLib.SDP4.deep_arg.xnode;
			beta = Math.sqrt(1 - PLib.SDP4.deep_arg.em * PLib.SDP4.deep_arg.em);
			PLib.SDP4.deep_arg.xn = PLib.xke / Math.pow(a, 1.5);
		
			axn = PLib.SDP4.deep_arg.em * Math.cos(PLib.SDP4.deep_arg.omgadf);
			temp = 1 / (a * beta * beta);
			xll = temp * PLib.SDP4.xlcof * axn;
			aynl = temp * PLib.SDP4.aycof;
			xlt = xl + xll;
			ayn = PLib.SDP4.deep_arg.em * Math.sin(PLib.SDP4.deep_arg.omgadf) + aynl;
		
			capu = FMod2p(xlt - PLib.SDP4.deep_arg.xnode);
			temp2 = capu;
			i = 0;
		
			do
			{
				sinepw = Math.sin(temp2);
				cosepw = Math.cos(temp2);
				temp3 = axn * sinepw;
				temp4 = ayn * cosepw;
				temp5 = axn * cosepw;
				temp6 = ayn * sinepw;
				epw = (capu - temp4 + temp3 - temp2) / (1 - temp5 - temp6) + temp2;
		
				if (Math.abs(epw - temp2) <= e6a)
					break;
		
				temp2 = epw;
		
			} while (i++ < 10);
		
			ecose = temp5 + temp6;
			esine = temp3 - temp4;
			elsq = axn * axn + ayn * ayn;
			temp = 1 - elsq;
			pl = a * temp;
			r = a * (1 - ecose);
			temp1 = 1 / r;
			rdot = PLib.xke * Math.sqrt(a) * esine * temp1;
			rfdot = PLib.xke * Math.sqrt(pl) * temp1;
			temp2 = a * temp1;
			betal = Math.sqrt(temp);
			temp3 = 1 / (1 + betal);
			cosu = temp2 * (cosepw - axn + ayn * esine * temp3);
			sinu = temp2 * (sinepw - ayn - axn * esine * temp3);
			u = Math.atan2(sinu, cosu);
			sin2u = 2 * sinu * cosu;
			cos2u = 2 * cosu * cosu - 1;
			temp = 1 / pl;
			temp1 = PLib.ck2 * temp;
			temp2 = temp1 * temp;
		
			rk = r * (1 - 1.5 * temp2 * betal * PLib.SDP4.x3thm1) + 0.5 * temp1 * PLib.SDP4.x1mth2 * cos2u;
			uk = u - 0.25 * temp2 * PLib.SDP4.x7thm1 * sin2u;
			xnodek = PLib.SDP4.deep_arg.xnode + 1.5 * temp2 * PLib.SDP4.deep_arg.cosio * sin2u;
			xinck = PLib.SDP4.deep_arg.xinc + 1.5 * temp2 * PLib.SDP4.deep_arg.cosio * PLib.SDP4.deep_arg.sinio * cos2u;
			rdotk = rdot - PLib.SDP4.deep_arg.xn * temp1 * PLib.SDP4.x1mth2 * sin2u;
			rfdotk = rfdot + PLib.SDP4.deep_arg.xn * temp1 * (PLib.SDP4.x1mth2 * cos2u + 1.5 * PLib.SDP4.x3thm1);
		
			sinuk = Math.sin(uk);
			cosuk = Math.cos(uk);
			sinik = Math.sin(xinck);
			cosik = Math.cos(xinck);
			sinnok = Math.sin(xnodek);
			cosnok = Math.cos(xnodek);
			xmx = -sinnok * cosik;
			xmy = cosnok * cosik;
			ux = xmx * sinuk + cosnok * cosuk;
			uy = xmy * sinuk + sinnok * cosuk;
			uz = sinik * sinuk;
			vx = xmx * cosuk - cosnok * sinuk;
			vy = xmy * cosuk - sinnok * sinuk;
			vz = sinik * cosuk;
		
			pos.x = rk * ux;
			pos.y = rk * uy;
			pos.z = rk * uz;
			vel.x = rdotk * ux + rfdotk * vx;
			vel.y = rdotk * uy + rfdotk * vy;
			vel.z = rdotk * uz + rfdotk * vz;
		
			PLib.phase = xlt - PLib.SDP4.deep_arg.xnode - PLib.SDP4.deep_arg.omgadf + PLib.twopi;
		
			if (PLib.phase < 0.0)
				PLib.phase += PLib.twopi;
		
			PLib.phase = PLib.FMod2p(PLib.phase);
		},
		
		Calculate_User_PosVel: function(time, geodetic, obs_pos, obs_vel)
		{
			var c = 0.0, sq = 0.0, achcp = 0.0;
		
			geodetic.theta = PLib.FMod2p(PLib.ThetaG_JD(time) + geodetic.lon);
			c = 1 / Math.sqrt(1 + PLib.f * (PLib.f - 2) * PLib.Sqr(Math.sin(geodetic.lat)));
			sq = PLib.Sqr(1 - PLib.f) * c;
			achcp = (PLib.xkmper * c + geodetic.alt) * Math.cos(geodetic.lat);
			obs_pos.x = achcp * Math.cos(geodetic.theta);
			obs_pos.y = achcp * Math.sin(geodetic.theta);
			obs_pos.z = (PLib.xkmper * sq + geodetic.alt) * Math.sin(geodetic.lat);
			obs_vel.x = -PLib.mfactor * obs_pos.y;
			obs_vel.y = PLib.mfactor * obs_pos.x;
			obs_vel.z = 0;
			PLib.Magnitude(obs_pos);
			PLib.Magnitude(obs_vel);
		},

		// Calculate the latitude and longitude of the sun if projected on the
		// the map.  Longitude is degrees East, 0 - 2pi.
		Calculate_Solar_LatLon: function(time, pos, geodetic)
		{
			var r = 0.0, e2 = 0.0, phi = 0.0, c = 0.0;
		
			geodetic.theta = Math.atan2(pos.y, pos.x);
			geodetic.lon = PLib.FMod2p(geodetic.theta - PLib.ThetaG_JD(time));


			r = Math.sqrt(PLib.Sqr(pos.x) + PLib.Sqr(pos.y));
			e2 = PLib.f * (2 - PLib.f);
			geodetic.lat = Math.atan2(pos.z, r);

			do
			{
				phi = geodetic.lat;
				c = 1 / Math.sqrt(1 - e2 * PLib.Sqr(Math.sin(phi)));
				geodetic.lat = Math.atan2(pos.z + PLib.xkmper * c * e2 * Math.sin(phi), r);
		
			} while (Math.abs(geodetic.lat - phi) >= 1E-10);
		
			if (geodetic.lat > PLib.pio2)
				geodetic.lat -= PLib.twopi;
		},
		
		
		Calculate_LatLonAlt: function(time, pos, geodetic)
		{
			var r = 0.0, e2 = 0.0, phi = 0.0, c = 0.0;
		
			geodetic.theta = Math.atan2(pos.y, pos.x);
			geodetic.lon = PLib.FMod2p(geodetic.theta - PLib.ThetaG_JD(time));
			r = Math.sqrt(PLib.Sqr(pos.x) + PLib.Sqr(pos.y));
			e2 = PLib.f * (2 - PLib.f);
			geodetic.lat = Math.atan2(pos.z, r);
		
			do
			{
				phi = geodetic.lat;
				c = 1 / Math.sqrt(1 - e2 * PLib.Sqr(Math.sin(phi)));
				geodetic.lat = Math.atan2(pos.z + PLib.xkmper * c * e2 * Math.sin(phi), r);
		
			} while (Math.abs(geodetic.lat - phi) >= 1E-10);
		
			geodetic.alt = r / Math.cos(geodetic.lat) - PLib.xkmper * c;
		
			if (geodetic.lat > PLib.pio2)
				geodetic.lat -= PLib.twopi;
		},
		
		Calculate_Obs: function(time, pos, vel, geodetic, obs_set)
		{
			var sin_lat = 0.0, cos_lat = 0.0, sin_theta = 0.0, cos_theta = 0.0, 
			    el = 0.0, azim = 0.0, top_s = 0.0, top_e = 0.0, top_z = 0.0;
		
			var obs_pos = new PLib.vector_t();
			var obs_vel = new PLib.vector_t();
			var range = new PLib.vector_t();
			var rgvel = new PLib.vector_t();
		
			PLib.Calculate_User_PosVel(time, geodetic, obs_pos, obs_vel);
		
			range.x = pos.x - obs_pos.x;
			range.y = pos.y - obs_pos.y;
			range.z = pos.z - obs_pos.z;
		
			rx = range.x;
			ry = range.y;
			rz = range.z;
		
			rgvel.x = vel.x - obs_vel.x;
			rgvel.y = vel.y - obs_vel.y;
			rgvel.z = vel.z - obs_vel.z;
		
			PLib.Magnitude(range);
		
			sin_lat = Math.sin(geodetic.lat);
			cos_lat = Math.cos(geodetic.lat);
			sin_theta = Math.sin(geodetic.theta);
			cos_theta = Math.cos(geodetic.theta);
			top_s = sin_lat * cos_theta * range.x + sin_lat * sin_theta * range.y 
			        - cos_lat * range.z;
			top_e = -sin_theta * range.x + cos_theta * range.y;
			top_z = cos_lat * cos_theta * range.x+cos_lat * sin_theta * range.y 
			        + sin_lat * range.z;
			azim = Math.atan(-top_e / top_s);
		
			if (top_s > 0.0)
				azim = azim + PLib.pi;
		
			if (azim < 0.0)
				azim = azim + PLib.twopi;
		
			el = Math.asin(top_z / range.w);
			obs_set.x = azim;
			obs_set.y = el;
			obs_set.z = range.w;
		
			obs_set.w = PLib.Dot(range, rgvel) / range.w;
		
			obs_set.y = el;
		
			if (obs_set.y >= 0.0)
				PLib.SetFlag(PLib.VISIBLE_FLAG);
			else
			{
				obs_set.y = el;
				PLib.ClearFlag(PLib.VISIBLE_FLAG);
			}
		},
		
		InternalUpdate: function(x)
		{
			var tempnum;
		
			PLib.sat[x].designator = PLib.sat[x].line1.substring(9, 17);
			PLib.sat[x].catnum = PLib.sat[x].line1.substring(2, 7);
			PLib.sat[x].year = PLib.sat[x].line1.substring(18, 20);
			PLib.sat[x].refepoch = PLib.sat[x].line1.substring(20, 32);
			tempnum = 1.0e-5 * PLib.sat[x].line1.substring(44, 50);
			PLib.sat[x].nddot6 = tempnum / Math.pow(10.0, PLib.sat[x].line1.charAt(51));
			tempnum = 1.0e-5 * PLib.sat[x].line1.substring(53, 59);
			PLib.sat[x].bstar = tempnum / Math.pow(10.0, PLib.sat[x].line1.charAt(60));
			PLib.sat[x].setnum = PLib.sat[x].line1.substring(64, 68);
			PLib.sat[x].incl = PLib.sat[x].line2.substring(8, 16);
			PLib.sat[x].raan = PLib.sat[x].line2.substring(17, 25);
			PLib.sat[x].eccn = 1.0e-07 * PLib.sat[x].line2.substring(26,33);
			PLib.sat[x].argper = PLib.sat[x].line2.substring(34, 42);
			PLib.sat[x].meanan = PLib.sat[x].line2.substring(43, 51);
			PLib.sat[x].meanmo = PLib.sat[x].line2.substring(52, 63);
			PLib.sat[x].drag = PLib.sat[x].line1.substring(33, 43);
			PLib.sat[x].orbitnum = PLib.sat[x].line2.substring(63, 68);
		},

		InitializeData: function()
		{
			for (var z = 0; z < PLib.tleData.length; z++)
			{
				PLib.sat[z] = new PLib.sat_t();
				PLib.sat[z].name = PLib.tleData[z][0];
				PLib.sat[z].line1 = PLib.tleData[z][1];
				PLib.sat[z].line2 = PLib.tleData[z][2];
				PLib.InternalUpdate(z);				
			}

		},

		DayNum: function(m, d, y)
		{
			var dn = 0;
			var mm = 0.0, yy = 0.0;
		
			if (m < 3)
			{
				y--;
				m += 12;
			}
		
			if (y <= 50)
				y += 100;
		
			yy = y;
			mm = m;
			dn = (Math.floor(365.25 * (yy - 80.0)) - Math.floor(19.0 + yy / 100.0) + Math.floor(4.75 + yy / 400.0) - 16.0);
			dn += d + 30 * m + Math.floor(0.6 * mm - 0.3);
			
			return dn;
		},
		
		CurrentDaynum: function()
		{
			var d = new Date();
			return (d.getTime() - 315446400000) / 86400000;
		},
		
		Daynum2Date: function(daynum)
		{
			var d = new Date();
			d.setTime(daynum * 86400000 + 315446400000);
			var x = d + 1;
			return d;
		},

		PreCalc: function(x)
		{
			PLib.tle.sat_name = PLib.sat[x].name
			PLib.tle.idesg = PLib.sat[x].designator;
			PLib.tle.catnr = PLib.sat[x].catnum;
			PLib.tle.epoch = (1000.0 * PLib.sat[x].year) + PLib.sat[x].refepoch * 1;
			PLib.tle.xndt2o = PLib.sat[x].drag;
			PLib.tle.xndd6o = PLib.sat[x].nddot6;
			PLib.tle.bstar = PLib.sat[x].bstar;
			PLib.tle.xincl = PLib.sat[x].incl;
			PLib.tle.xnodeo = PLib.sat[x].raan;
			PLib.tle.eo = PLib.sat[x].eccn;
			PLib.tle.omegao = PLib.sat[x].argper;
			PLib.tle.xmo = PLib.sat[x].meanan;
			PLib.tle.xno = PLib.sat[x].meanmo;
			PLib.tle.revnum = PLib.sat[x].orbitnum;
		
			PLib.ClearFlag(PLib.ALL_FLAGS);
			PLib.select_ephemeris(PLib.tle);
		},

		Calc: function()
		{
			var zero_vector = new PLib.vector_t();
			var vel = new PLib.vector_t();
			var pos = new PLib.vector_t();
			var obs_set = new PLib.vector_t();
			var solar_vector = new PLib.vector_t();
			var solar_set = new PLib.vector_t();
		
			var sat_geodetic = new PLib.geodetic_t();
			var sun_geodetic = new PLib.geodetic_t();
		
			PLib.jul_utc = PLib.daynum + 2444238.5;
		
			PLib.jul_epoch = PLib.Julian_Date_of_Epoch(PLib.tle.epoch);
			PLib.tsince = (PLib.jul_utc - PLib.jul_epoch) * PLib.xmnpda;
			PLib.age = PLib.jul_utc - PLib.jul_epoch;
		
			if (PLib.isFlagSet(PLib.DEEP_SPACE_EPHEM_FLAG))
				PLib.ephem = "SDP4";
			else
				PLib.ephem = "SGP4";
		
			if (PLib.isFlagSet(PLib.DEEP_SPACE_EPHEM_FLAG))
				PLib.SDP4(PLib.tsince, PLib.tle, pos, vel);
			else
				PLib.SGP4(PLib.tsince, PLib.tle, pos, vel);
		
			PLib.Convert_Sat_State(pos, vel);
		
			PLib.Magnitude(vel);
			PLib.sat_vel = vel.w;
		
			PLib.Calculate_Obs(      PLib.jul_utc, pos, vel, PLib.obs_geodetic, obs_set );
			PLib.Calculate_LatLonAlt(PLib.jul_utc, pos, sat_geodetic);
		
			PLib.Calculate_Solar_Position( PLib.jul_utc, solar_vector);
			PLib.Calculate_Obs(            PLib.jul_utc, solar_vector, zero_vector, 
			                               PLib.obs_geodetic, solar_set);
			PLib.Convert_Sat_State(        solar_vector, zero_vector);
			PLib.Calculate_Solar_LatLon(   PLib.jul_utc, solar_vector, sun_geodetic );

			if (PLib.Sat_Eclipsed(pos, solar_vector))
				PLib.SetFlag(PLib.SAT_ECLIPSED_FLAG);
			else
				PLib.ClearFlag(PLib.SAT_ECLIPSED_FLAG);
		
			if (PLib.isFlagSet(PLib.SAT_ECLIPSED_FLAG))
				PLib.sat_sun_status = 0;
			else
				PLib.sat_sun_status = 1;
		
			PLib.sat_azi = PLib.Degrees(obs_set.x);
			PLib.sat_ele = PLib.Degrees(obs_set.y);
			PLib.sat_range = obs_set.z;
			PLib.sat_range_rate = obs_set.w;
			PLib.sat_lat = PLib.Degrees(sat_geodetic.lat);
			PLib.sat_lon = PLib.Degrees(sat_geodetic.lon);
			PLib.sat_alt = sat_geodetic.alt;

			PLib.sun_lat = PLib.Degrees(   sun_geodetic.lat );
			PLib.sun_lon = PLib.Degrees(   sun_geodetic.lon );
		
			PLib.fk = 12756.33 * Math.acos(PLib.xkmper / (PLib.xkmper + PLib.sat_alt));
			PLib.fm = PLib.fk / 1.609344;
		
			PLib.rv = Math.floor((PLib.tle.xno * PLib.xmnpda / PLib.twopi + PLib.age * PLib.tle.bstar * PLib.ae) * PLib.age + PLib.tle.xmo / PLib.twopi) + parseInt(PLib.tle.revnum,10);
		
			PLib.sun_azi = PLib.Degrees(solar_set.x);
			PLib.sun_ele = PLib.Degrees(solar_set.y);
		
			PLib.irk = Math.round(PLib.sat_range);
			// jwc - removed rounding so we can do later as we need to.
			//PLib.isplat = Math.round(PLib.sat_lat);
			//PLib.isplong = Math.round(360.0 - PLib.sat_lon);
			PLib.isplat = PLib.sat_lat;
			PLib.isplong = 360.0 - PLib.sat_lon;
			PLib.iaz = Math.round(PLib.sat_azi);
			PLib.iel = Math.round(PLib.sat_ele);
			PLib.ma256 = Math.round(256.0 * (PLib.phase / PLib.twopi));
		
			if (PLib.sat_sun_status)
			{
				if (PLib.sun_ele <= -12.0 && PLib.sat_ele >= 0.0)
					PLib.findsun = '+';
				else
					PLib.findsun = '*';
			}
			else
				PLib.findsun = ' ';
		},
		
		AosHappens: function(x)
		{
			var lin = 0.0, sma = 0.0, apogee = 0.0;
		
			if (PLib.sat[x].meanmo == 0.0)
				return 0;
			else
			{
				lin = PLib.sat[x].incl;
		
				if (lin >= 90.0)
					lin = 180.0 - lin;
		
				sma = 331.25 * Math.exp(Math.log(1440.0 / PLib.sat[x].meanmo) * (2.0 / 3.0));
				apogee = sma * (1.0 + PLib.sat[x].eccn) - PLib.xkmper;
		
				if ((Math.acos(PLib.xkmper / (apogee + PLib.xkmper)) + (lin * PLib.deg2rad)) > Math.abs(PLib.qth.stnlat * PLib.deg2rad))
					return 1;
				else
					return 0;
			}
		},
	
		Decayed: function(x, time)
		{
			var satepoch = 0.0;
		
			if (time == 0.0)
				time = PLib.CurrentDaynum();
		
			satepoch = PLib.DayNum(1, 0, PLib.sat[x].year) + PLib.sat[x].refepoch;
		
			if (satepoch + ((16.666666 - PLib.sat[x].meanmo) / (10.0 * Math.abs(PLib.sat[x].drag))) < time)
				return 1;
			else
				return 0;
		},
	
		Geostationary: function(x)
		{
			if (Math.abs(PLib.sat[x].meanmo - 1.0027) < 0.0002) 
				return 1;
			else
				return 0;
		},
	
		FindAOS: function()
		{
			PLib.aostime = 0.0;
		
			if (PLib.AosHappens(indx) && PLib.Geostationary(indx) == 0 && PLib.Decayed(indx, PLib.daynum) == 0)
			{
				PLib.Calc();
		
				while (PLib.sat_ele < -1.0)
				{
					PLib.daynum -= 0.00035 * (PLib.sat_ele * (((PLib.sat_alt / 8400.0) + 0.46)) - 2.0);
					PLib.Calc();
				}
		
				while (PLib.aostime == 0.0)
				{
					if (Math.abs(PLib.sat_ele) < 0.03)
						PLib.aostime = PLib.daynum;
					else
					{
						PLib.daynum -= PLib.sat_ele * Math.sqrt(PLib.sat_alt) / 530000.0;
						PLib.Calc();
					}
				}
			}
		
			return PLib.aostime;
		},
	
		FindLOS: function()
		{
			PLib.lostime = 0.0;
		
			if (PLib.Geostationary(indx) == 0 && PLib.AosHappens(indx) == 1 && PLib.Decayed(indx, PLib.daynum) == 0)
			{
				PLib.Calc();
		
				do
				{
					PLib.daynum += PLib.sat_ele * Math.sqrt(PLib.sat_alt) / 502500.0;
					PLib.Calc();
		
					if (Math.abs(PLib.sat_ele) < 0.03)
						PLib.lostime = PLib.daynum;
		
				} while (PLib.lostime == 0.0);
			}
		
			return PLib.lostime;
		},

		QuickFind: function(satname)
		{
			var satInfo = new Object();

			for (var z = 0; z < PLib.sat.length; z++)
			{
				if ((PLib.sat[z].name == satname) || (satname == PLib.sat[z].catnum))
				{
					PLib.daynum = PLib.CurrentDaynum();
					PLib.PreCalc(z);
					PLib.Calc();
	
					if (PLib.Decayed(z, PLib.daynum) == 0)
					{
						satInfo.dateTime = PLib.Daynum2Date(PLib.daynum);
						satInfo.elevation = PLib.iel;
						satInfo.azimuth = PLib.iaz;
						satInfo.orbitalPhase = PLib.ma256;
						satInfo.latitude = PLib.isplat;
		
						var lng = 360 - PLib.isplong;
						if (lng > 180) lng = -PLib.isplong;
						satInfo.longitude = lng;

						satInfo.slantRange = PLib.irk;
						satInfo.orbitNumber = PLib.rv;
						satInfo.visibility = PLib.findsun;
						satInfo.altitude = PLib.sat_alt;
					}
		
					break;
				}
			}
		
			return satInfo;
		},

		formatDateOnly: function(dt)
		{
			var months = [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ];
			return months[dt.getMonth()] + " " + dt.getDate();
		},

		formatTimeOnly: function(dt)
		{
			var h = dt.getHours();
			var m = dt.getMinutes();

			var amPm = h < 12 ? "AM" : "PM"

			h = h > 12 ? h - 12 : h;
			h = h == 0 ? h = 12 : h;
			m = m < 10 ? "0" + m : m;

			return h + ":" + m + amPm;
		},

		extractDate: function(dt)
		{
			var d = new Date();
			d.setTime(dt.valueOf());
			d.setHours(0);
			d.setMinutes(0);
			d.setSeconds(0);
			d.setMilliseconds(0);

			return d;
		},

		addDay: function(dt)
		{
		    var d = new Date(dt.getTime() + 86400000);
		    return d;
		},

		getTodaysPasses: function()
		{
			var satInfoColl = new Array();
			var arrIdx = 0;
			var x = 0, y = 0, z = 0, lastel = 0;
			var start = 0, now = 0;

			for (z = 0; z < PLib.sat.length; z++)
			{
				indx = z;

				now = (3651.0 + PLib.CurrentDaynum()) * 86400.0;

				if (start == 0)
					start = now;

				if ((start >= now - 31557600) && (start <= now + 31557600))
				{
					PLib.daynum = (start / 86400.0) - 3651.0;
					PLib.PreCalc(indx);
					PLib.Calc();

					var d = new Date();
					var passNo = 1;

					if (PLib.AosHappens(indx) && PLib.Geostationary(indx) == 0 && PLib.Decayed(indx, PLib.daynum) == 0)
					{
						PLib.daynum = PLib.FindAOS();

						while (PLib.Daynum2Date(PLib.daynum) < PLib.addDay(d))
						{
							var satInfo = new Object();

							satInfo.number = z + 1;
							satInfo.name = PLib.sat[z].name;
							satInfo.passNo = passNo++;
							satInfo.dateTimeStart = PLib.Daynum2Date(PLib.daynum);
							satInfo.peakElevation = PLib.iel;
							satInfo.riseAzimuth
								= satInfo.peakAzimuth
								= PLib.iaz;
							satInfo.orbitalPhase = PLib.ma256;
							satInfo.latitude = PLib.isplat;

							var lng = 360 - PLib.isplong;
							if (lng > 180) lng = -PLib.isplong;
							satInfo.longitude = lng;

							satInfo.riseRange
								= satInfo.peakRange
								= PLib.irk;
							satInfo.orbitNumber = PLib.rv;

							var plusCount = 0;
							var asteriskCount = 0;

							while (PLib.iel >= 0)
							{
								if (PLib.iel > satInfo.peakElevation)
								{
									satInfo.peakElevation = PLib.iel;
									satInfo.peakAzimuth = PLib.iaz;
									satInfo.peakRange = PLib.irk;
								}

								if (PLib.findsun == '+')
									plusCount++;
								else if (PLib.findsun == '*')
									asteriskCount++;

								lastel = PLib.iel;
								PLib.daynum += Math.cos((PLib.sat_ele - 1.0) * PLib.deg2rad) * Math.sqrt(PLib.sat_alt) / 25000.0;
								PLib.Calc();
							}

							if (lastel != 0)
							{
								PLib.daynum = PLib.FindLOS();
								PLib.Calc();
							}

							satInfo.dateTimeEnd = PLib.Daynum2Date(PLib.daynum);
							satInfo.decayAzimuth = PLib.iaz;
							satInfo.decayRange = PLib.irk;

							if ((plusCount > 3) || (plusCount > 2 && asteriskCount > 2))
							{
								satInfo.visibility = '+';
							}
							else if (asteriskCount > 2)
							{
								satInfo.visibility = '*';
							}

							satInfoColl[arrIdx++] = satInfo;

							PLib.daynum += (1 / 24 / 6);
							PLib.daynum = PLib.FindAOS();
						} 
					}
				}
			}

			return satInfoColl;
		},

		/******************************************************************************/
		/* Sattrack 3.5                                                               */
		/* multMatVec: multiplies a matrix with a vector in 3D                        */
		/******************************************************************************/

		multMatVec: function (amVec,q,mtx)
		{
 			var i;

			for (i = 0; i <= 2; i++)
			{
					q[i] = mtx[i][0]*amVec[0] + mtx[i][1]*amVec[1] + mtx[i][2]*amVec[2];
			}
		},
		
		/******************************************************************************/
		/* Sattrack 3.5                                                               */
		/* absol: calculates length (absolute value) of a vector in 3D                */
		/******************************************************************************/

		absol: function (absVec)
		{
			var absVal;
			var sum;
			sum = absVec[0]*absVec[0] + absVec[1]*absVec[1] + absVec[2]*absVec[2];
			//sum = absVec[0]*absVec[0] + absVec[1]*absVec[1] + absVec[2]*absVec[3];
			absVal = Math.sqrt( sum );
			return( absVal );
		},
		

		/******************************************************************************/
		/******************************************************************************/
		calcFootPrint: function( footprint, stepSize, lat, lon, alt, exHgt )
		{
			var lat, lon;
			var descMat    = new Array(3);
			descMat[0] = new Array(3);
			descMat[1] = new Array(3);
			descMat[2] = new Array(3);
			var HALFPI = 3.14159265 / 2;
			var CDR = 4 * HALFPI / 360;
			var CRD = 1 / CDR;
			var u, gammax, gamma;
			var q = new Array(3);

    	var the    = HALFPI - lat*CDR;
	    var psi    = HALFPI - lon*CDR;

    	var cosThe = Math.cos(the);
    	var sinThe = Math.sin(the);
    	var cosPsi = Math.cos(psi);
    	var sinPsi = Math.sin(psi);

			descMat[0][0] =  sinPsi*cosThe;
			descMat[0][1]  = -cosPsi;
			descMat[0][2]  =  sinPsi*sinThe;
			descMat[1][0] =  cosPsi*cosThe;
			descMat[1][1]  =  sinPsi;
			descMat[1][2]  =  cosPsi*sinThe;
			descMat[2][0] = -sinThe;
			descMat[2][1]  =  0.0;
			descMat[2][2]  =  cosThe;

			var arg  = PLib.xkmper / (PLib.xkmper + alt);

			if (arg >  1.0) arg =  1.0;
			if (arg < -1.0) arg = -1.0;

    	var gamma = Math.acos(arg);

			if (exHgt > 1e-6)                         /* for calculation of the ZOE */
			{
				arg = xkmper / (xkmper + exHgt);

				if (arg >  1.0) arg =  1.0;
				if (arg < -1.0) arg = -1.0;

				gammax = Math.acos(arg);
				gamma += gammax;
			}

			beta     = HALFPI - gamma;
			cosBeta  = Math.cos(beta);
			sinBeta  = Math.sin(beta);

			var circStep = 360.0 / 360.0;
			// TBD, check this.

			for (k = 0; k < 360;  k++)
			{
				var lambda    = (circStep * k) * CDR;
				var cosLambda = Math.cos(lambda);
				var sinLambda = Math.sin(lambda);
				var u = new Array();
						
				u[0] = cosLambda*cosBeta;
				u[1] = sinLambda*cosBeta;
				u[2] = sinBeta;


				PLib.multMatVec(u,q,descMat);

				var qabs = PLib.absol(q);

				for (i = 0; i <= 2; i++) 
							q[i] /= qabs;

				arg   = q[2];

				if (arg >  1.0) arg =  1.0;
				if (arg < -1.0) arg = -1.0;

				lat   = Math.asin(arg) * CRD;

				q[2]  = 0.0;
				qabs  = PLib.absol(q);

				q[0] /= qabs;
				q[1] /= qabs;

				lng   = Math.atan2(q[1],q[0]) * CRD;

				footprint[k].lat = lat;
				footprint[k].lon = lng;

				/*
				lng   = reduce(lng,-MAPWIDTHD/2.0,MAPWIDTHD/2.0);

				posX  = (int) ((MAPWIDTHD  / 2.0 - lng) * GRIDSCALEX + MARGINLFT - 0.5);
				posY  = (int) ((MAPHEIGHTD / 2.0 - lat) * GRIDSCALEY + MARGINTOP - 0.5);

				visibCirclePix[k].x1 = (short int) posX;
				visibCirclePix[k].y1 = (short int) posY;

				if (k > 0)
				{
					visibCirclePix[k-1].x2 = visibCirclePix[k].x1;
					visibCirclePix[k-1].y2 = visibCirclePix[k].y1;
				}
				*/
			}

			return;

		},


		configureGroundStation: function(lat, lng)
		{
			PLib.qth.stnlat = lat;

			if (lng < 0) PLib.qth.stnlong = -lng;
				else PLib.qth.stnlong = 360 - lng;

			PLib.obs_geodetic.lat = PLib.qth.stnlat * PLib.deg2rad;
			PLib.obs_geodetic.lon = -PLib.qth.stnlong * PLib.deg2rad;
			PLib.obs_geodetic.alt = PLib.qth.stnalt / 1000.0;
			PLib.obs_geodetic.theta = 0.0;
		}
};

PLib.obs_geodetic = new PLib.geodetic_t();
PLib.tle = new PLib.tle_t();

