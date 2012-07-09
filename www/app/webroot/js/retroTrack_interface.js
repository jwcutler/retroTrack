/*
This file handles most retroTrack interface interactions (e.g. menu, clock, etc.).
*/

function resetMenus(){
    /*
    Resets all menu link classes and hides any open panels.
    */
    
    // Remove active class
    $("[id^=show_menu_]").removeClass('active');
    
    // Hide control panels
    $("[id^=menu_]").hide();
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
        for (curr_satellite = 0; curr_satellite < satellites.length; curr_satellite=curr_satellite+1){
            // Append the satellite to the menu
            $("#satellite_list").append('<li id="'+satellites[curr_satellite]['id']+'">'+satellites[curr_satellite]['name']+'</li>');
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
        for (curr_station = 0; curr_station < stations.length; curr_station=curr_station+1){
            // Append the station to the menu
            $("#station_list").append('<li id="'+stations[curr_station]['id']+'">'+stations[curr_station]['name']+'</li>');
        }
        
        // Enable selectable
        $("#station_list").selectable({
        });
    }
}

function populateGroupsMenu(groups, active_groups){
    /*
    Populate the group selection menu.
    
    @param groups: All groups to display in the menu.
    @param active_groups: All of the currently selected groups.
    */
    
    if (groups.length==0){
        $('#menu_groups').html('No groups are currently enabled for this tracker.');
    } else {
        // Loop through all of the groups and add them to the list
        for (curr_group = 0; curr_group < groups.length; curr_group=curr_group+1){
            // Append the group to the menu
            $("#group_list").append('<li id="'+groups[curr_group]['id']+'">'+groups[curr_group]['name']+'</li>');
        }

        // Enable selectable
        $("#group_list").selectable({
        });
    }
}

function populateOptionsMenu(configuration){
    /*
    Populate the group selection menu. Because we don't want to show all options in the menu, just convert the list to selectable.
    
    @param groups: All groups to display in the menu.
    @param active_groups: All of the currently selected groups.
    */
    
    // Enable selectable
    $("#option_list").selectable({
    });
}

/*
Handle menu interactions.
*/
$().ready(function(){
    // Handle menu actions
    $("[id^=show_menu_]").click(function(){
        // Clean up the menus
        resetMenus();
        
        // Show the selected menu
        $(this).toggleClass("active");
        menu_div = $(this).attr('rel');
        $("#"+menu_div).show(400);
    });
});
