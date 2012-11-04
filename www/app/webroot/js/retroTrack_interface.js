/*
This file handles most retroTrack interface interactions (e.g. menu, clock, etc.).
*/

/*
Handle menu interactions.
*/
function resetMenus(trigger_pane_id){
    /*
    Resets all menu link classes and hides any open panels.
    
    @param trigger_pane_id: ID of the triggering element.
    */
    
    // Remove active class
    $("[id^=show_menu_]").removeClass('active');
    
    // Hide control panels
    $("[id^=menu_][id!="+trigger_pane_id+"]").hide();
}

function populateSatellitesMenu(satellites, active_satellites){
    /*
    Populate the satellite selection menu.
    
    @param satellites: All satellites to display in the menu.
    @param active_satellites: All of the currently selected satellites.
    */
    
    if (satellites.length==0){
        $('#menu_satellites').html('No satellites are currently enabled for this tracker.');
    } else {
        // Loop through all of the satellites and add them to the list
        for (curr_satellite_id in satellites){
            // Append the satellite to the selection
            $('#satellite_list').append($("<option></option>").attr("value",curr_satellite_id).attr("rel",satellites[curr_satellite_id]['name']).attr("title",satellites[curr_satellite_id]['description']).text(satellites[curr_satellite_id]['name'])); 
        }
        
        // Enable jQuery Chosen
        $("#satellite_list").chosen();
    }
}

function getLastSatellite(){
  /*
  Returns the name of the last satellite currently in the selection.
  
  @return The name of the last satellite.
  */
  
  temp_selected_satellite = false;
  selected_satellites_form = $("#satellite_list").val();
  if (selected_satellites_form && selected_satellites_form.length > 0){
    // Use the satellite ID to get the name
    selected_satellite_temp_id = selected_satellites_form[selected_satellites_form.length-1];
    selected_satellite_temp_name = $("#satellite_list option[value='"+selected_satellite_temp_id+"']").attr("rel");
    temp_selected_satellite = selected_satellite_temp_name;
  }
  
  return temp_selected_satellite;
}

function getLastStation(){
  /*
  Returns the name of the last station currently in the selection.
  
  @return The name of the last station.
  */
  
  temp_selected_station = false;
  selected_stations_form = $("#station_list").val();
  if (selected_stations_form && selected_stations_form.length > 0){
    // Set the station name
    temp_selected_station = selected_stations_form[selected_stations_form.length-1];
  }
  return temp_selected_station;
}

function populateStationsMenu(stations, active_station){
    /*
    Populate the stations selection menu.
    
    @param stations: All stations to display in the menu.
    @param active_station: The active ground station.
    */
    
    if (stations.length==0){
        $('#menu_stations').html('No stations are currently enabled for this tracker.');
    } else {
        // Loop through all of the stations and add them to the list
        for (curr_station_id in stations){
            // Append the station to the selection
            $("#station_list").append($("<option></option>").attr("value",curr_station_id).attr("rel",stations[curr_station_id]['name']).attr("title",stations[curr_station_id]['description']).text(stations[curr_station_id]['name']));
        }
        
        // Enable jQuery Chosen
        $("#station_list").chosen();
    }
}

function populateGroupsMenu(groups){
    /*
    Populate the group selection menu.
    
    @param groups: All groups to display in the menu.
    */
    
    if (groups.length==0){
        $('#menu_groups').html('No groups are currently enabled for this tracker.');
    } else {
        // Loop through all of the groups and add them to the list
        for (curr_group_id in groups){
            // Append the group to the selection
            $("#group_list").append($("<option></option>").attr("value",curr_group_id).attr("rel",curr_group_id).attr("title",groups[curr_group_id]['description']).text(groups[curr_group_id]['name']));
        }

        // Enable selectable
        $("#group_list").chosen();
    }
}

function populateOptionsMenu(configuration){
    /*
    Populate the option select menu. Because we don't want to show all options in the menu, just loop through and select the existing buttons based on default value.
    
    @param configuration: Object containing the configuration options.
    */
    
    // Loop through each menu item and check it's value in configuration
    $("#option_list").children().each(function(){
        // Grab the option's ID
        option_name = $(this).attr('id');
        // Check the default value
        if (configuration[option_name]['value']=='1'){
            // Select it
            $(this).addClass('ui-selected');
        }
    });
}

/*
Random functions.
*/
function initializeActiveSatellites(){
    /*
    Initializes active_satellites to be all available satellites.
    */
    
    // Loop through all of the default satellites
    active_satellites = [];
    if (default_elements['satellites'].length!=0){
        for (curr_satellite_index in default_elements['satellites']){
            // Add to active_satellites
            if (default_elements['satellites'][curr_satellite_index]['id'] in satellites){
                active_satellites[active_satellites.length] = default_elements['satellites'][curr_satellite_index]['name'];
                
                // Add the item to the selection
                $("#satellite_list option[value='"+default_elements['satellites'][curr_satellite_index]['id']+"']").attr("selected", "selected");
            }
        }
        
        // Rebuild the menu
        $("#satellite_list").trigger("liszt:updated");
    } else {
        // Select the first satellite
        if (satellites.length!=0){
            for (curr_satellite_index in satellites){
                // Add to active_satellites
                active_satellites[active_satellites.length] = satellites[curr_satellite_index]['name'];
                
                // Add the item to the selection
                $("#satellite_list option[value='"+satellites[curr_satellite_index]['id']+"']").attr("selected", "selected");
                
                // Rebuild the menu
                $("#satellite_list").trigger("liszt:updated");
                
                break;
            }
        }
    }
    
    // Grab the last satellite in the selection
    selected_satellite = getLastSatellite();
}

function initializeActiveGroups(){
    /*
    Selects the default groups.
    */
    
    // Loop through all of the default groups
    for (curr_group_index in default_elements['groups']){
        // Add the group to the selection
        $("#group_list option[value='"+default_elements['groups'][curr_group_index]+"']").attr("selected", "selected");
    }
    
    // Rebuild the menu
    $("#group_list").trigger("liszt:updated");
}

function initializeActiveStations(){
    /*
    Initializes active_stations to be just the default ground station.
    */
    
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
        selected_station = getLastStation();
    }
    
    // Select every ground station
    $("#station_list option").attr("selected", "selected");
                
    // Rebuild the menu
    $("#station_list").trigger("liszt:updated");
}

/*
This document ready handles most interface interactions (menus, clock, etc).
*/
$().ready(function(){
    // Handle menu expansions
    $("[id^=show_menu_]").click(function(){
        // Clean up the menus
        resetMenus($(this).attr('rel'));
        
        // Show the selected menu
        if ($("#"+$(this).attr('rel')).is(":visible")){
            $(this).removeClass("active");
        } else {
            $(this).addClass("active");
        }
        menu_div = $(this).attr('rel');
        $("#"+menu_div).slideToggle(400);
    });
    
    // Handle satellite selection menu changes
    $("#satellite_list").change(function(event, ui) {
        // Clear active_satellites
        active_satellites = [];
        
        // Unselect all groups
        $("#group_list").children().removeAttr("selected");
        
        // Rebuild the menu
        $("#group_list").trigger("liszt:updated");
        
        // Loop through the selected children and add the rel value to 'active_satellites'
        $(this).children('option:selected').each(function(){
            // Add the satellite ID to active_satellites
            active_satellites[active_satellites.length] = $(this).attr('rel');
        });
        
        // Grab the last satellite in the selection
        selected_satellite = getLastSatellite();
        
        // Reload the PLib Satellites
        retroTrack.setPlibSatellites();
        
        // Update plot
        retroTrack.updatePlot();
    });
    
    // Handle station selection menu changes
    $("#station_list").change(function(event, ui) {
        // Clear active_stations
        active_stations = [];
        
        // Loop through the selected children and add the rel value to 'active_stations'
        $(this).children('option:selected').each(function(){
            // Add the station to active_stations
            active_stations[active_stations.length] = $(this).attr('rel');
        });
		
        // Set the active station to be the last one in the list
        selected_station = getLastStation();
		
        // Update plot
        retroTrack.updatePlot();
    });
    
    // Handle group selection menu changes
    $("#group_list").change(function(event, ui) {
        // Clear active_satellites
        active_satellites = [];
        selected_satellite = null;
        
        // Unselect all currently selected satellites
        $("#satellite_list").children().removeAttr("selected");
        
        // Rebuild the menu
        $("#satellite_list").trigger("liszt:updated");
        
        // Loop through the selected groups and load their satellites
        $(this).children('option:selected').each(function(){
            // Loop through the active group's satellites
            group_id = $(this).attr('rel');
            for (satellite_index in groups[group_id]['satellites']){
                // Make sure the satellite is available
                if (groups[group_id]['satellites'][satellite_index]['id'] in satellites){
                    // Add the satellite to active_satellites
                    active_satellites[active_satellites.length] = groups[group_id]['satellites'][satellite_index]['name'];
                    
                    // Select the satellite in the menu
                    $("#satellite_list option[value='"+groups[group_id]['satellites'][satellite_index]['id']+"']").attr("selected", "selected");
                }
            }
            
            // Rebuild the menu
            $("#satellite_list").trigger("liszt:updated");
            
            // Set the active satellite to the first one in the list
            selected_satellite = getLastSatellite();
        });
        
        // Reload the PLib Satellites
        retroTrack.setPlibSatellites();
        
        // Update plot
        retroTrack.updatePlot();
    });
    
    // Handle group selection menu changes
    $("#option_list").children().click(function(){
        option_key = $(this).attr('id');
        
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
});
