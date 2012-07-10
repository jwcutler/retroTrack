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
            $("#satellite_list").append('<li id="select_satellite_'+curr_satellite_id+'" rel="'+curr_satellite_id+'">'+satellites[curr_satellite_id]['name']+'</li>');
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
            $("#station_list").append('<li id="select_station_'+curr_station_id+'" rel="'+curr_station_id+'">'+stations[curr_station_id]['name']+'</li>');
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
            $("#group_list").append('<li id="select_group_'+curr_group_id+'" rel="'+curr_group_id+'">'+groups[curr_group_id]['name']+'</li>');
        }

        // Enable selectable
        $("#group_list").selectable({
        });
    }
}

function populateOptionsMenu(){
    /*
    Populate the option select menu. Because we don't want to show all options in the menu, just convert the existing list to selectable.
    */
    
    // Enable selectable
    $("#option_list").selectable({
    });
}

/*
Random functions.
*/
function initializeActiveSatellites(){
    /*
    Initializes active_satellites to be all available satellites.
    */
    
    // Loop through all of the passed satellites
    active_satellites = [];
    for (curr_satellite_index in satellites){
        // Add to active_satellites
        active_satellites[active_satellites.length] = satellites[curr_satellite_index]['id'];
    }
    
    // Select all satellites in the menu
    $("#satellite_list").children().addClass('ui-selected');
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
        $("#"+menu_div).toggle(400);
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
    });
    
    // Handle group selection menu changes
    $("#group_list").bind("selectablestop", function(event, ui) {
        // Clear active_satellites
        active_satellites = [];
        
        // Unselect all currently selected satellites
        $("#satellite_list").children().removeClass('ui-selected');
        
        // Loop through the selected groups and load their satellites
        $(this).children('.ui-selected').each(function(){
            // Loop through the active group's satellites
            group_id = $(this).attr('rel');
            for (satellite_index in groups[group_id]['satellites']){
                // Add the satellite to active_satellites
                temp_satellite_id = groups[group_id]['satellites'][satellite_index]['id'];
                active_satellites[active_satellites.length] = temp_satellite_id;
                
                // Select the satellite in the menu
                $("#select_satellite_"+temp_satellite_id).addClass('ui-selected');
            }
        });
    });
});
