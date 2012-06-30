/****************************************************************************
*  retroTrack.js:  A library for satellite orbit visulation.                *
*  	Copyright James W. Cutler 2012.                                     *
*	Some functions derived from OrbTrak, 07-Jun-2008, by Andrew T. West *
*	Some functions derived from SatTrack, version 3.1.5                 *
*****************************************************************************
*                                                                           *
* This program is free software; you can redistribute it and/or modify it   *
* under the terms of the GNU General Public License as published by the     *
* Free Software Foundation; either version 2 of the License or any later    *
* version.                                                                  *
*                                                                           *
* This program is distributed in the hope that it will be useful,           *
* but WITHOUT ANY WARRANTY; without even the implied warranty of            *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU         *
* General Public License for more details.                                  *
*                                                                           *
*****************************************************************************
 * Update Log                                                               *
 *                                                                          *
 *                                                                          *
 *                                                                          *
*****************************************************************************/

var Orb = 
	{
		stepSize:			360, 
		drawGrid:			1,
		satelliteMarkers: 	new Array(),
		month:				new Array(12),
		footprint:			new Array(360),
		tles:				new String(""),
		tles_loaded:		0,
		view_sun:			1,
		view_tracks:		1,
		view_multi:			1,
		view_foot:			1,
		sat_square:			6, 
		sat_color: 			"#FFFFFF",
		gs_color: 			"#FF0000",
		sat_eclipse: 		"#DDDDDD",

		// Init function for Orb.
		initialize: function()
		{
			for (z=0;z<Orb.stepSize;z++)
			{
				Orb.footprint[z] = new Orb.pos_t();
			}
			Orb.createMonths();
		},


		pos_t: function()
	   	{
			this.lat = 0;
			this.lon = 0;
	   	},

		fetchTLEs: function( url )
		{
			$.get( url, 
   				function(data) { 
					Orb.tles = data;
				}
			);
		},

		drawMap: function(canvas, url) 
		{
			var ctx = canvas.getContext('2d');
			var img = new Image();
			img.src = url;
			img.onload = function(){
						ctx.drawImage(img,0,0);
			}
		},


		loadTLEs: function()
		{
			var name  = new String();
			var line1 = new String();
			var line2 = new String();
			var nos;

			if (Orb.tles != "" )
			{
				//alert( Orb.tles );
				// We have TLEs.  So let's load into the PLib data structure.  Let's
				// assume the first line is a name.
				// TBD, insert line length checking on Line1, Line2, and checksum 
				// checking.

				var lines = Orb.tles.split('\n');
				nos = Math.floor(lines.length / 3);
				for (var i=0;i<nos;i++ )
				{
					name  = lines[i*3+0].replace(/^\s+|\s+$/g, '');
					line1 = lines[i*3+1];
					line2 = lines[i*3+2];
					PLib.tleData[i] = new Array(3);
					PLib.tleData[i][0] = name;
					PLib.tleData[i][1] = line1;
					PLib.tleData[i][2] = line2;
				
				}
				PLib.InitializeData();
				return 1;
			}
			
			return 0;
		},

		/*
		startTracking: function(map, homeLat, homeLng)
		{

			Orb.map = map;
			Orb.crossBrowserSetStyle(map, "background-image: url("+
	                               image_url + "); overflow: hidden;", true);

			var frag = document.createDocumentFragment();
			var div = document.createElement("div");
			div.id = "home";
			Orb.crossBrowserSetStyle(div, "position:relative; width: 24px; height: 24px; background-image: url(orbimages/home.gif);", false);
			frag.appendChild(div);
			Orb.map.appendChild(frag);
			Orb.home = document.getElementById("home");

			PLib.InitializeData();
			Orb.setHomeCoordinates(homeLat, homeLng);
			Orb.createSatelliteMarkers();
			//Orb.updateSatellites();

		},
		*/

		createMonths: function()
		{
			Orb.month[0]="Jan";
			Orb.month[1]="Feb";
			Orb.month[2]="Mar";
			Orb.month[3]="Apr";
			Orb.month[4]="May";
			Orb.month[5]="Jun";
			Orb.month[6]="Jul";
			Orb.month[7]="Aug";
			Orb.month[8]="Sep";
			Orb.month[9]="Oct";
			Orb.month[10]="Nov";
			Orb.month[11]="Dec";
		},

		setHomeCoordinates: function(homeLat, homeLng)
		{
			PLib.configureGroundStation(homeLat, homeLng);

			Orb.home.style.top = ((-homeLat + 90) * 1.5 - 12.0) + "px";
			Orb.home.style.left =  ((parseInt(homeLng) + 180) * 1.5 - 12.0) + "px";
		},

		crossBrowserSetStyle: function(element, css, append)
		{
			var obj, attributeName;
			var useStyleObject = element.style.setAttribute;

			obj = useStyleObject ? element.style : element;
			attributeName = useStyleObject ? "cssText" : "style";

			if (append)
				css += obj.getAttribute(attributeName);

			obj.setAttribute(attributeName, css);
		},

		createOneMarker: function(txt)
		{
			var frag = document.createDocumentFragment();
			var markerCount = Orb.satelliteMarkers.length;

			var div = document.createElement("div");
			div.id = "satelliteMarker" + markerCount;
			Orb.crossBrowserSetStyle(div, "position:absolute; width: 24px; height: 24px; " + 
			                              "background-image: url(orbimages/saticon.gif);", false);
			var innerDiv = document.createElement("div");
			Orb.crossBrowserSetStyle(innerDiv, "position:absolute; left: 7px; top: 5px;");
			var txt = document.createTextNode(txt);

			innerDiv.appendChild(txt);
			div.appendChild(innerDiv);
			frag.appendChild(div);
			Orb.map.appendChild(frag);

			Orb.satelliteMarkers[markerCount] = document.getElementById(div.id)
		},

		createSatelliteMarkers: function()
		{
			for (var i = 1; i <= PLib.sat.length; i++)
				Orb.createOneMarker(i);
		},

		updateSatellites: function()
		{
			var satInfo;

			for (var i = 0; i < PLib.sat.length; i++)
			{
				satInfo = PLib.QuickFind(PLib.sat[i].name);

				Orb.satelliteMarkers[i].style.top = ((-satInfo.latitude + 90) * 1.5 - 12.0) + "px";
				Orb.satelliteMarkers[i].style.left =  ((satInfo.longitude + 180) * 1.5 - 12.0) + "px";
			}

			setTimeout("Orb.updateSatellites()", 5000);
		},

		createCell: function(tr, className, txt)
		{
			var td = document.createElement("td");
			td.className = className;
			txt = document.createTextNode(txt);
			td.appendChild(txt);
			tr.appendChild(td);
		},

		createHeaderColumn: function(tr, txt)
		{
			var th = document.createElement("th");
			th.className = "orb-header";
			txt = document.createTextNode(txt);
			th.appendChild(txt);
			tr.appendChild(th);
		},

		generateTable: function(divTable)
		{
			var tr, visibilityText, detailClassName;
			var frag = document.createDocumentFragment();
			var satInfoColl = PLib.getTodaysPasses();
			
			while (divTable.childNodes.length > 0)
			{
			    divTable.removeChild(divTable.firstChild);
			}
			
			var tbl = document.createElement("table");
			Orb.crossBrowserSetStyle(tbl, "border-collapse: collapse; margin-left: auto; margin-right: auto;", false);
			
			var thead = document.createElement("thead");
			tr = document.createElement("tr");
			
			Orb.createHeaderColumn(tr, '# on Map');
			Orb.createHeaderColumn(tr, 'Name');
			Orb.createHeaderColumn(tr, 'Pass #');
			Orb.createHeaderColumn(tr, 'Date');
			Orb.createHeaderColumn(tr, 'Local Time');
			Orb.createHeaderColumn(tr, 'Peak Elev.');
			Orb.createHeaderColumn(tr, 'Azimuth');
			Orb.createHeaderColumn(tr, 'Range (km)');
			Orb.createHeaderColumn(tr, 'Visibility');
			
			thead.appendChild(tr);
			tbl.appendChild(thead);
			
			var tbody = document.createElement("tbody");
			
			for (var i = 0; i < satInfoColl.length; i++)
			{
				tr = document.createElement("tr");
				
				detailClassName = satInfoColl[i].visibility == "+" ? "orb-detailVisible" : "orb-detail";
				
				Orb.createCell(tr, detailClassName, satInfoColl[i].number);
				Orb.createCell(tr, detailClassName, satInfoColl[i].name);
				Orb.createCell(tr, detailClassName, satInfoColl[i].passNo);
				Orb.createCell(tr, detailClassName, PLib.formatDateOnly(satInfoColl[i].dateTimeStart));
				Orb.createCell(tr, detailClassName, PLib.formatTimeOnly(satInfoColl[i].dateTimeStart) + " - " + PLib.formatTimeOnly(satInfoColl[i].dateTimeEnd));
				Orb.createCell(tr, detailClassName, satInfoColl[i].peakElevation + "\u00B0");
				Orb.createCell(tr, detailClassName, satInfoColl[i].riseAzimuth + ", " + satInfoColl[i].peakAzimuth + ", " + satInfoColl[i].decayAzimuth);
				Orb.createCell(tr, detailClassName, satInfoColl[i].riseRange + ", " + satInfoColl[i].peakRange + ", " + satInfoColl[i].decayRange);
				
				switch(satInfoColl[i].visibility)
				{
					case "+":
						visibilityText = 'Visible';
						break;    
					case "*":
						visibilityText = 'Not Visible';
						break;
					default:
						visibilityText = 'Eclipsed';
				}
				
				Orb.createCell(tr, detailClassName, visibilityText);
				
				tbody.appendChild(tr);
			}
			
			tbl.appendChild(tbody);
			frag.appendChild(tbl);
			divTable.appendChild(frag);
		},

		updateClock: function( delay )
		{
			var now = new Date();
			$('table#pb td#pb-time').html( now.getDate() + " " +
					Orb.month[now.getMonth()] + " " +
					now.getFullYear() + " " +
					now.getHours() + ":" +
					now.getMinutes() + ":" +
					now.getSeconds() + 
					" (GMT -" + now.getTimezoneOffset()/60 + ")");
			if ( delay > 0 )
				setTimeout("Orb.updateClock(" + delay+ ")", delay);

		},

		generatePositionBar: function(sat_name)
		{
			var tr, visibilityText, detailClassName;
			var frag = document.createDocumentFragment();
			var satInfoColl = PLib.getTodaysPasses();
			var satInfo;
			
			var now = new Date();

			for (var i = 0; i < PLib.sat.length; i++)
			{
				if( PLib.sat[i].name == sat_name)
				{
				satInfo = PLib.QuickFind(PLib.sat[i].name);
				var on = parseInt(PLib.tle.revnum, 10) + satInfo.orbitNumber;
				on = PLib.rv;

				$('table#pb td#pb-name').html( "<div class='select_sat2'>" + sat_name + "</div>" );
				$('table#pb td#pb-orbit').html( "Orbit #:" + on );
				$('table#pb td#pb-lat').html( "Lat: " + satInfo.latitude.toFixed(1));
				$('table#pb td#pb-lon').html( "Lon: " + satInfo.longitude.toFixed(1));
				$('table#pb td#pb-alt').html( "Alt: " + satInfo.altitude.toFixed(1) + " km");


				}
			}
		},

		generatePositionMap: function(divCanvas)
		{
			var tr, visibilityText, detailClassName;
			var frag = document.createDocumentFragment();
			var satInfoColl = PLib.getTodaysPasses();
			var satInfo;
			
			while (divCanvas.childNodes.length > 0)
			{
			    divCanvas.removeChild(divCanvas.firstChild);
			}
			
			var tbl = document.createElement("table");
			Orb.crossBrowserSetStyle(tbl, "background:#000000;width: 540px; border-collapse: collapse; margin-left: 0; margin-right: auto;", false);
			
			var tbody = document.createElement("tbody");
			
			tbl.appendChild(tbody);
			frag.appendChild(tbl);
			divCanvas.appendChild(frag);   

			var now = new Date();

			for (var i = 0; i < PLib.sat.length; i++)
			{
				satInfo = PLib.QuickFind(PLib.sat[i].name);
				tr = document.createElement("tr");
				Orb.crossBrowserSetStyle(tr, "bgcolor: black;", true);
				Orb.createCell(tr, "orb-label", PLib.sat[i].name);
				Orb.createCell(tr, "orb-label", "Orbit #: TBD" );
				Orb.createCell(tr, "orb-label", "Lat: " + satInfo.latitude.toFixed(1));
				Orb.createCell(tr, "orb-label", "Lon: " + satInfo.longitude.toFixed(1));
				Orb.createCell(tr, "orb-label", now.getDate() + 
				                                    Orb.month[now.getMonth()] +
				                                    now.getFullYear() + " " +
				                                    now.getHours() + ":" +
				                                    now.getMinutes() + ":" +
				                                    now.getSeconds() 
							  );
				tbody.appendChild(tr);
				
			}
			tr = document.createElement("tr");
			Orb.crossBrowserSetStyle(tr, "height: 270px; " + 
		                             "background-image: url(" + image_url +"); " +
		                             "overflow: hidden;", true);
			var td = document.createElement("td");
			td.setAttribute("colspan","5");
			txt = document.createTextNode(" ");
			td.appendChild( txt );
			tr.appendChild(td);
			tbody.appendChild(tr);

		},

		drawGridLines: function( ctx, color )
		{
			var fs = ctx.fillStyle;

			ctx.fillStyle = "#666666";
			for( var i = 1; i <= 6; i++ )
			{
				ctx.fillRect( 0, i*(map_height/6), map_width, 1 );
			}
			for( var i = 1; i <= 12; i++ )
			{
				ctx.fillRect( i*(map_width/12), 0, 1, map_height );
			}
			ctx.fillStyle = fs;
		},

		//*************************************************************
		// toggleFeature
		//*************************************************************
		toggleFeature: function ( index, cell )
		{
			var toggleVar;
			if ( index == "sun" )
			{
				Orb.view_sun = (Orb.view_sun + 1) % 2;
				toggleVar = Orb.view_sun;
			}
			else if ( index == "tracks" )
			{
				Orb.view_tracks = (Orb.view_tracks + 1) % 2;
				toggleVar = Orb.view_tracks;
			}
			else if ( index == "multi" )
			{
				Orb.view_multi = (Orb.view_multi + 1) % 2;
				toggleVar = Orb.view_multi;
			}
			else if ( index == "foot" )
			{
				Orb.view_foot = (Orb.view_foot + 1) % 2;
				toggleVar = Orb.view_foot;
			}

			if ( toggleVar == 1 )
				$("#cb-" + index).attr("class","cb-cell-active");
			else
				$("#cb-" + index).attr("class","cb-cell-hidden");

			return;

		},

		//*************************************************************
		// plotSatPosition 
		//*************************************************************
		plotGSPosition: function( ctx, lat, lon, sat_name )
		{
			var satInfo;
			var sat_square = Orb.sat_square;

			x = Math.round( (lon + 180) / 360 * map_width  );
			y = Math.round( (180 - (lat + 90))  / 180 * map_height );
			ctx.fillStyle = sat_sun;
			ctx.fillStyle = Orb.gs_color;
			//ctx.fillRect(x-sat_square/2,y-sat_square/2,sat_square,sat_square); 
			ctx.arc(x,y,sat_square/2,0,Math.PI*2,true); 
			//ctx.arc(89,102,2,0,Math.PI*2,true);  
			ctx.fill();

			ctx.fillStyle = "#FFFFFF";
			PLib.configureGroundStation( lat, lon );
			satInfo = PLib.QuickFind( sat_name );
			PLib.calcFootPrint( Orb.footprint, Orb.stepSize, lat,
			                    lon, satInfo.altitude, 0.0 );
			for (k=0; k<360; k++ )
			{
				x = Math.round( (Orb.footprint[k].lon + 180) / 360 * map_width );
				y = Math.round( (180 - (Orb.footprint[k].lat + 90))  / 180 * map_height );
				ctx.fillRect(x,y,1,1);
			}
			$('table#gs td#gs_lat').html( "Lat: " + lat + " N" );
			$('table#gs td#gs_lon').html( "Lon: " + lon + " E" );
			//$('table#gs td#gs_az').html( "Az: " + az );
			//$('table#gs td#gs_el').html( "El: " + el );
			$('table#gs td#gs_az').html( "Az: " + satInfo.azimuth );
			$('table#gs td#gs_el').html( "El: " + satInfo.elevation );
			$('table#gs td#gs_range').html( "Range: " + satInfo.slantRange + " km" );
		},

		//jwc

		//*************************************************************
		// plotSatPosition 
		//*************************************************************
		plotSatPosition: function( sat, ctx, name )
		{
			var sat_square = Orb.sat_square;
			lat = sat.latitude;
			lon = sat.longitude;
			x = Math.round( (lon + 180) / 360 * map_width  );
			y = Math.round( (180 - (lat + 90))  / 180 * map_height );
			if ( PLib.sat_sun_status == 1 )
				ctx.fillStyle = sat_sun;
			else
				ctx.fillStyle = Orb.sat_eclipse;
			ctx.fillRect(x-sat_square/2,y-sat_square/2,sat_square,sat_square); 
			ctx.fillStyle = Orb.sat_color;

				// Write sat name and calc position.
				t_w = ctx.measureText( "hello" );
				if ( lon > 0 ) 
					t_x = x - sat_square - t_w.width;
				else
					t_x = x + sat_square;

				if ( lat > 0 )
					t_y = y + 2*sat_square;
				else
					t_y = y -2*sat_square;
				ctx.fillText(name, t_x, t_y );
		},


		//*************************************************************
		// plotSat 
		//*************************************************************
		plotSat: function( ctx, sat_name )
		{
			var satInfo, lat, lon, t_x, t_y, tf, tb, fs;
			var sat_square = Orb.sat_square;

			// Draw background Map.
			ctx.drawImage(map,0,0); 

			// Get detailed satellite information

			for (var i = 0; i < PLib.sat.length; i++)
			{
				if( PLib.sat[i].name == sat_name)
				{
					satDetails = PLib.sat[i];
					i = PLib.sat.Length + 1;
				}
			}


			// Lat/Lon lines.
			if( Orb.drawGrid == 1 )	
				Orb.drawGridLines( ctx, "#EEEEEEE" );

				// Calc position
				//satInfo = PLib.QuickFind(PLib.sat[i].name);
				satInfo = PLib.QuickFind( sat_name );

				// Plot track
				if ( Orb.view_tracks == 1 )
				{
				tb = PLib.daynum - 0.05 * (1 / satDetails.meanmo );
				tf = PLib.daynum + 3 * (1 / satDetails.meanmo );
				PLib.daynum = tb;
				var j = 0;
				while ( PLib.daynum < tf )
				{
					PLib.Calc();
					PLib.daynum += 10 / (24 * 3600);
					//PLib.daynum += 0.1;
					lat = PLib.sat_lat;
					lon = PLib.sat_lon;
					lon = 360 - PLib.isplong;
					if (lon > 180) lon = -PLib.isplong;

					x = Math.round( (lon + 180) / 360 * map_width  );
					y = Math.round( (180 - (lat + 90))  / 180 * map_height );
					ctx.fillStyle = "#8888FF";
					ctx.fillRect(x,y,2,2); 
					j++;
				}
				}



				// Plot Multi Sats, if enabled.
				if ( Orb.view_multi == 1 )
				{
					for (var i = 0; i < PLib.sat.length; i++)
					{
						satInfo = PLib.QuickFind(PLib.sat[i].name);
						Orb.plotSatPosition( satInfo, ctx, PLib.sat[i].name );
					}
				}

				// Plot Main slected Satellite position
				satInfo = PLib.QuickFind(satDetails.name);
				Orb.plotSatPosition( satInfo, ctx, satDetails.name );



				// Write sat name and calc position.
				t_w = ctx.measureText( satDetails.name );
				if ( lon > 0 ) 
					t_x = x - sat_square - t_w.width;
				else
					t_x = x + sat_square;

				if ( lat > 0 )
					t_y = y + 2*sat_square;
				else
					t_y = y -2*sat_square;
				ctx.fillText(satDetails.name, t_x, t_y );

				// ------------------
				// Plot Sun location
				// ------------------
				if ( Orb.view_sun == 1 )
				{
				ctx.fillStyle = "#FFFF00";

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
				}
				}

				// Plot Satellite Foot Print
				if ( Orb.view_foot )
				{
				ctx.fillStyle = "#FFFFFF";
				PLib.calcFootPrint( Orb.footprint, Orb.stepSize, satInfo.latitude, 
				                    satInfo.longitude, satInfo.altitude, 0.0 );
				for (k=0; k<360; k++ )
				{
					x = Math.round( (Orb.footprint[k].lon + 180) / 360 * map_width );
					y = Math.round( (180 - (Orb.footprint[k].lat + 90))  / 180 * map_height );
					ctx.fillRect(x,y,1,1);
				}
				}
				
		}

	}
