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
            // Append the satellite to the menu
            $("#satellite_list").append('<li id="select_satellite_'+curr_satellite_id+'" rel="'+satellites[curr_satellite_id]['name']+'" title="'+satellites[curr_satellite_id]['description']+'">'+satellites[curr_satellite_id]['name']+'</li>');
        }
        
        // Enable selectable
        $("#satellite_list").selectable({
        });
    }
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
            // Append the satellite to the menu
            $("#station_list").append('<li id="select_station_'+curr_station_id+'" rel="'+stations[curr_station_id]['name']+'" title="'+stations[curr_station_id]['description']+'">'+stations[curr_station_id]['name']+'</li>');
        }
        
        // Enable selectable
        $("#station_list").selectable({
        });
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
            // Append the group to the menu
            $("#group_list").append('<li id="select_group_'+curr_group_id+'" rel="'+curr_group_id+'" title="'+groups[curr_group_id]['description']+'">'+groups[curr_group_id]['name']+'</li>');
        }

        // Enable selectable
        $("#group_list").selectable({
        });
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
                $('#select_satellite_'+default_elements['satellites'][curr_satellite_index]['id']).addClass('ui-selected');
            }
        }
    } else {
        // Select the first satellite
        if (satellites.length!=0){
            for (curr_satellite_index in satellites){
                // Add to active_satellites
                active_satellites[active_satellites.length] = satellites[curr_satellite_index]['name'];
                $('#select_satellite_'+satellites[curr_satellite_index]['id']).addClass('ui-selected');
                break;
            }
        }
    }
    
    // Set the active satellite to the first one in the list
    selected_satellite = active_satellites[0];
}

function initializeActiveGroups(){
    /*
    Selects the default groups.
    */
    
    // Loop through all of the default groups
    for (curr_group_index in default_elements['groups']){
        // Select the group
        $("#select_group_"+default_elements['groups'][curr_group_index]).addClass('ui-selected');
    }
}

function initializeActiveStations(){
    /*
    Initializes active_stations to be all available stations.
    */
    
    // Loop through all of the available stations
    active_stations = [];
    for (curr_station_index in stations){
        // Add to active_stations
        active_stations[active_stations.length] = stations[curr_station_index]['name'];
    }
    
    // Set the active station
    if (configuration['default_ground_station']['value'] in stations){
        // Default present, use it
        selected_station = configuration['default_ground_station']['value'];
    } else {
        // Default not present, use first
        selected_station = active_stations[0];
    }
    
    // Select all satellites in the menu
    $("#station_list").children().addClass('ui-selected');
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
    $("#satellite_list").bind("selectablestop", function(event, ui) {
        // Clear active_satellites
        active_satellites = [];
        
        // Unselect all groups
        $("#group_list").children().removeClass('ui-selected');
        
        // Loop through the selected children and add the rel value to 'active_satellites'
        $(this).children('.ui-selected').each(function(){
            // Add the satellite ID to active_satellites
            active_satellites[active_satellites.length] = $(this).attr('rel');
        });
        
        // Set the selected satellite
        selected_satellite = active_satellites[0];
        
        // Reload the PLib Satellites
        retroTrack.setPlibSatellites();
        
        // Update plot
        retroTrack.updatePlot();
    });
    
    // Handle station selection menu changes
    $("#station_list").bind("selectablestop", function(event, ui) {
        // Clear active_stations
        active_stations = [];
        
        // Loop through the selected children and add the rel value to 'active_stations'
        $(this).children('.ui-selected').each(function(){
            // Add the station to active_stations
            active_stations[active_stations.length] = $(this).attr('rel');
        });
		
        // Set the active station to be the first one in the list
        selected_station = active_stations[0];
		
        // Update plot
        retroTrack.updatePlot();
    });
    
    // Handle group selection menu changes
    $("#group_list").bind("selectablestop", function(event, ui) {
        // Clear active_satellites
        active_satellites = [];
        selected_satellite = null;
        
        // Unselect all currently selected satellites
        $("#satellite_list").children().removeClass('ui-selected');
        
        // Loop through the selected groups and load their satellites
        $(this).children('.ui-selected').each(function(){
            // Loop through the active group's satellites
            group_id = $(this).attr('rel');
            for (satellite_index in groups[group_id]['satellites']){
                // Make sure the satellite is available
                if (groups[group_id]['satellites'][satellite_index]['id'] in satellites){
                    // Add the satellite to active_satellites
                    active_satellites[active_satellites.length] = groups[group_id]['satellites'][satellite_index]['name'];
                    
                    // Select the satellite in the menu
                    $("#select_satellite_"+groups[group_id]['satellites'][satellite_index]['id']).addClass('ui-selected');
                }
            }
            
            // Set the active satellite to the first one in the list
            selected_satellite = active_satellites[0];
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
