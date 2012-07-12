/*
Primary retroTrack javascript object.

Programmed By: Jimmy Blanchard
Based on Version By: James Cutler
*/

var retroTrack = {
    // Variables
    tracker_canvas_context: null,
    tracker_canvas_width: null,
    tracker_canvas_height: null,
    map_image: null,
    step_size: 360,
    
    
    initialize: function(canvas){
        /*
        Setup the retroTrack display.
        
        @param canvas: Primary canvas to draw the tracker on.
        */
        
        // Setup
        tracker_canvas_context = document.getElementById(canvas).getContext('2d');
        tracker_canvas_width = $("#"+canvas).width();
        tracker_canvas_height = $("#"+canvas).height();
        
        // Load the map
        retroTrack.loadMap();
        
        // Initialize PLib
        retroTrack.setPlibSatellites();
        
        // Create the clock update loop
        retroTrack.updateClock();
        setInterval(retroTrack.updateClock, configuration['clock_update_period']['value']);
        
        // Create primary display update loop
        retroTrack.updatePlot();
        setInterval(retroTrack.updatePlot, configuration['map_update_period']['value']);
    },
    
    setPlibSatellites: function(){
        /*
        Loads TLE information into PLib and initialize.
        */
        
        // Reset the PLib TLE data
        PLib.tleData = [];
        PLib.sat = [];
        
        // Add everything in active_satellites
        for (curr_satellite_index in active_satellites){
            // Load TLE data into PLib
            curr_satellite_name = active_satellites[curr_satellite_index];
            PLib.tleData[PLib.tleData.length] = [curr_satellite_name, tles[curr_satellite_name]['raw_l1'], tles[curr_satellite_name]['raw_l2']];
        }
        
        // Initialize PLib
        PLib.InitializeData();
    },
    
    loadMap: function(){
        /*
        Loads the map image into a variable for later use
        */
        
        map_image = new Image();
        map_image.src = 'img/'+configuration['map_file']['value'];
    },
    
    updateClock: function(){
        /*
        Updates the tracker clock at the specified frequency.
        */
        
        // Update the new clock
        var curr_time = new Date();
        $("#top_clock").html(curr_time.toLocaleDateString()+" "+curr_time.getHours()+":"+curr_time.getMinutes()+":"+curr_time.getSeconds()+" (GMT - "+curr_time.getTimezoneOffset()/60+")");
        
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
        Draws the sun and the day/night boundary.
        */
        
        /*// Setup
        tracker_canvas_context.fillStyle = "#"+configuration['sun_color']['value'];
        
        // Calculate the position
        if (PLib.sun_lon > 180){
            sun_longitude = PLib.sun_lon - 180;
        } else {
            sun_longitude = PLib.sun_lon + 180;
        }
        sun_latitude = 90 - PLib.sun_lat;
        sun_x_pos = Math.round((sun_longitude/360)*tracker_canvas_width);
        sun_y_pos = Math.round((sun_
        

			// Shift lon to map coordinate system.  PLib.sun_lon should
			// be in degrees to the East, 0 - 360.
			if ( PLib.sun_lon > 180 )
				lon = PLib.sun_lon - 180;
			else
				lon = PLib.sun_lon + 180;
			x = Math.round( (lon ) / 360 * map_width  );

			// Shift lat to map coordinates.  PLib.sun_lat should be
			// degrees N from -90 to 90.
			lat = 90 - PLib.sun_lat;
			y = Math.round( ((lat))  / 180 * map_height );


			var font = ctx.font;
			ctx.font = "18pt Arial";
			ctx.fillText("*", x-2, y+10 );
			ctx.font = font;
			
			PLib.calcFootPrint( Orb.footprint, Orb.stepSize, 
			                    PLib.sun_lat, PLib.sun_lon, 149597892.0, 0.0 );
			for (k=0; k<360; k++ )
			{
				x = Math.round( (Orb.footprint[k].lon + 180) / 360 * map_width );
				y = Math.round( (180 - (Orb.footprint[k].lat + 90))  / 180 * map_height );
				ctx.fillRect(x,y,1,1);
			}*/
    },
    
    updatePlot: function(){
        /*
        Primary plot update loop. 
        
        - Plots the selected satellite (plus its path)
        - Plots all active satellites (markers)
        */
        
        // Reload the map (clears the previous satellite positions, etc.)
        tracker_canvas_context.drawImage(map_image, 0, 0);
        
        // Plot the grid (if configured)
        if (configuration['show_grid']['value']=='1'){
            retroTrack.drawGrid();
        }
        
        // Plot the sun (if configured)
        if (configuration['show_sun']['value']=='1'){
            retroTrack.drawSun();
        }
        
        // Plot the satellite eclipse
        
        // Plot the satellite footprint
        
        // Plot the ground station
        
        // Plot each satellite marker, including the selected satellite
        for (curr_satellite_index in PLib.sat){
            // Load the satellite from PLib
            curr_satellite_info = PLib.QuickFind(PLib.sat[curr_satellite_index].name);
            retroTrack.plotSatellitePosition(curr_satellite_info, PLib.sat[curr_satellite_index].name);
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
    }
}
