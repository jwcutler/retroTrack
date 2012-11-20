/*
Primary retroTrack javascript object.

Programmed By: Jimmy Blanchard
Based on Version By: James Cutler
*/

var retroTrack = {
    // Variabless
    tracker_canvas_context: null,
    tracker_canvas_width: null,
    tracker_canvas_height: null,
    map_image: null,
    footprint: null, // General purpose footprint array
    
    initialize: function(canvas){
        /*
        Setup the retroTrack display.
        
        @param canvas: Primary canvas to draw the tracker on.
        */
        
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
    
    continueInitialization: function(){
        /*
        Continue the initialization. A seperate function is needed to allow the script to pause while the image is loaded.
        */
        
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
        $("#load_bar").progressbar("value", 100);
        $("#load_progress_message").html('Complete.');
        $('#load_modal').dialog("close");
    },
    
    setPlibSatellites: function(){
        /*
        Loads TLE information into PLib and initialize.
        */
        
        // Reset the PLib TLE data
        PLib.tleData = [];
        PLib.sat = [];
        
        // Add everything in active_satellites
        temp_satellites = new Array();
        for (curr_satellite_index in active_satellites){
            // Load TLE data into PLib
            curr_satellite_name = active_satellites[curr_satellite_index];
            if (curr_satellite_name in tles){
                PLib.tleData[PLib.tleData.length] = [curr_satellite_name, tles[curr_satellite_name]['raw_l1'], tles[curr_satellite_name]['raw_l2']];
                temp_satellites.push(active_satellites[curr_satellite_index]);
            } else {
                // No TLE data exists for satellite, remove it from menus
                $("#satellite_list option[rel="+curr_satellite_name+"]").remove();
            }
        }
    
        active_satellites = temp_satellites.slice(0);
        selected_satellite = getLastSatellite();
        
        // Initialize PLib
        PLib.InitializeData();
    },
    
    loadMap: function(){
        /*
        Loads the map image into a variable for later use
        */
        
        // Update progress bar
        $("#load_bar").css('width','70%');
        $("#load_progress_message").html('Loading map image.');
        
        map_image = new Image();
        map_image.src = background_image_path;
        map_image.onload = function(){
            // Continue program execution once image has loaded
            retroTrack.continueInitialization();
        }
    },
    
    updateClock: function(){
        /*
        Updates the tracker clock at the specified frequency.
        */
    
        // Add time padding
        var curr_time = new Date();
        clock_hours = ((curr_time.getHours()+"").length==1)?'0'+curr_time.getHours():curr_time.getHours();
        clock_minutes = ((curr_time.getMinutes()+"").length==1)?'0'+curr_time.getMinutes():curr_time.getMinutes();
        clock_seconds = ((curr_time.getSeconds()+"").length==1)?'0'+curr_time.getSeconds():curr_time.getSeconds();
        
        // Update the new clock
        $("#top_clock").html((curr_time.getMonth()+1)+"/"+curr_time.getDate()+"/"+curr_time.getFullYear()+" "+clock_hours+":"+clock_minutes+":"+clock_seconds+" (GMT - "+curr_time.getTimezoneOffset()/60+")");
    },
    
    positionElement: function(){
        /*
        This is a simple position placeholder used to draw footprints.
        */
        
        this.lat = 0;
        this.lon = 0;
    },
    
    drawGrid: function(){
        /*
        Draws overlaying grid over the tracker canvas.
        */
        
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
    
    drawSun: function(){
        /*
        Draws the sun and the day/night shadow
        */
        
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
    
    drawSatellitePath: function(){
        /*
        Calculates and draws the path for the active satellite.
        */
        
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
    
    updatePlot: function(){
        /*
        Primary plot update loop. 
        
        - Plots the selected satellite (plus its path)
        - Plots all active satellites (markers)
        */
        
        // Reload the map (clears the previous satellite positions, etc.)
        tracker_canvas_context.drawImage(map_image, 0, 0);
    
        // Clear the satellite info pane
        $("#station_parameters").html("");
        
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
        for (curr_satellite_index in PLib.sat){
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
        for (curr_station_index in active_stations){
            // Plot the station
            retroTrack.plotStationPosition(active_stations[curr_station_index]);
                
            if (active_stations[curr_station_index]==selected_station){
                retroTrack.updateStationBar(selected_station);
            }
        }
    },
    
    updateSatelliteBar: function(curr_satellite_info, curr_satellite_name){
        /*
        Updates the satellite information bar with the specified satellite's parameters.
        
        @param curr_satellite_info: PLib satellite info object.
        @param curr_satellite_name: Name of the specified satellite.
        */
        
        // Display the satellite information.
        $("#satellite_parameters").html("");
        
        if (!selected_satellite){
          // No satellite selected
          $("#satellite_parameters").append("<li></li>");
        } else {
          // Calculate orbit number
          curr_satellite_orbit = parseInt(PLib.tle.revnum, 10) + curr_satellite_info.orbitNumber;
          curr_satellite_orbit = PLib.rv;
          
          // Load the satellite parameters
          $("#satellite_parameters").append("<li id='satellite_info_name'><span style='color: #"+configuration['satellite_selected_color']['value']+";'>"+curr_satellite_name+"</span></li>");
          $("#satellite_parameters").append("<li>Lat: "+curr_satellite_info.latitude.toFixed(1)+"</li>");
          $("#satellite_parameters").append("<li>Lon: "+curr_satellite_info.longitude.toFixed(1)+"</li>");
          $("#satellite_parameters").append("<li>Alt: "+curr_satellite_info.altitude.toFixed(1)+" km</li>");
          $("#satellite_parameters").append("<li>Orbit: #"+curr_satellite_orbit+"</li>");
        }
    },
    
    updateStationBar: function(curr_station_name){
        /*
        Updates the station information bar with the currently selected ground station's parameters.
        
        @param curr_station_name: Name of the ground station.
        */
        
        // Display the status bar
        if (selected_satellite){
            selected_satellite_trimmed = (selected_satellite.length<=10)?selected_satellite:selected_satellite.substring(0,10)+"...";
            $("#station_parameters").append("<li id='station_info_name'><span style='color: #"+configuration['station_selected_color']['value']+";'>"+curr_station_name+"</span> - <span style='color: #"+configuration['satellite_selected_color']['value']+";'>"+selected_satellite_trimmed+"</span></li>");
        } else {
             $("#station_parameters").append("<li id='station_info_name'><span style='color: #"+configuration['station_selected_color']['value']+";'>"+curr_station_name+"</span></li>");
        }
        $("#station_parameters").append("<li>Lat: "+Number(stations[curr_station_name]['latitude']).toFixed(3)+"</li>");
        $("#station_parameters").append("<li>Lon: "+Number(stations[curr_station_name]['longitude']).toFixed(3)+"</li>");
        if(selected_satellite){
            selected_satellite_info = PLib.QuickFind(selected_satellite);
            $("#station_parameters").append("<li>Az: "+selected_satellite_info.azimuth+"</li>");
            $("#station_parameters").append("<li>El: "+selected_satellite_info.elevation+"</li>");
            $("#station_parameters").append("<li>Range: "+selected_satellite_info.slantRange.toFixed(2)+" km</li>");
        }
    },
    
    plotStationPosition: function(curr_station_name){
        /*
        Plots the provided ground station on the canvas.
        
        @param curr_station_name: Name of the station to plot.
        */
        
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
    
    plotSatellitePosition: function(curr_satellite_info, curr_satellite_name){
        /*
        Plots a marker on the canvas for the specified satellite.
        
        @param curr_satellite_info: PLib satellite info object.
        @param curr_satellite_name: Name of the current satellite.
        */
        
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
