/*
RetroTrack Initialization Script
*/

// Configuration Settings
var update_period_clock = 1000;   // Seconds
var update_period_map   = 5000;   // Seconds
var map_url = "images/world.jpg"; // Map background image
var lat = 43.154;
var lon = -77.615;
var map_width  = 860; 
var map_height = 430; 
var sat_sun = "#FFFF00";
var update_period = 5000;
var trackPeriodC = 1; 
var trackPeriodX = 10;
var DRAW_GRID = 1;
var tle_url = "tle.txt";
var tles = new String();
var sat_selected = "RAX-2";
var lat_orig = 42.283405;	// N
var lon_orig = -83.739166;	// E

var map = new Image();

$(document).ready(function() 
{
$("#pb-menu-sat").click(function(e) {
e.preventDefault();
$("#position8").toggle();
$("#pb-menu-sat").toggleClass("menu-open");
});
$("#position8").mouseup(function() {
return false
});
$(document).mouseup(function(e) {
if($(e.target).parent("#pb-menu-sat").length==0) {
    $("#pb-menu-sat").removeClass("menu-open");
    $("#position8").hide();
}
});            

$("#pb-menu-controls").click(function(e) {
e.preventDefault();
$("#position9").toggle();
$("#pb-menu-controls").toggleClass("menu-open");
});
$("#position9").mouseup(function() {
return false
});
$(document).mouseup(function(e) {
if($(e.target).parent("#pb-menu-controls").length==0) {
    $("#pb-menu-controls").removeClass("menu-open");
    $("#position9").hide();
}
});            


});

//function set_tles( data ) { Orb.tles = data; }

function load()
{
	map.src = map_url;
	Orb.drawMap( document.getElementById('canvas'), map_url );
	PLib.InitializeData();
	Orb.initialize();
	Orb.drawGrid = DRAW_GRID;
	Orb.fetchTLEs( tle_url );

	// Do initial update.
	Orb.updateClock(0);
	updatePage(0);

	// Queue up updates infinitely.
	Orb.updateClock(update_period_clock);
	updatePage(update_period_map);
}


function updatePage(delay)
{
	//var ctx=document.getElementById('canvas').getContext('2d'); 


	if (  Orb.tles_loaded )
	{
		Orb.generatePositionBar( sat_selected );
		Orb.plotSat( document.getElementById('canvas').getContext('2d'), 
		                         sat_selected );
		Orb.plotGSPosition( document.getElementById('canvas').getContext('2d'), 
			lat_orig, lon_orig, sat_selected );
		setTimeout("updatePage(" + update_period_map + ")", update_period_map );
	}
	else
	{
		Orb.tles_loaded = Orb.loadTLEs();
		loadSatMenu2();
		setTimeout("updatePage(1)", 1);
	}
}

/*
function loadSatMenu( divSection )
{
	var tr;

	var frag = document.createDocumentFragment();
	var tbl = document.createElement("table");
	var tbody = document.createElement("tbody");

	tbl.appendChild(tbody);
	frag.appendChild(tbl);
	divSection.appendChild(frag);   
	for (var i = 0; i<PLib.sat.length; i++)
	{
		if ( i % 5 == 0 )
			tr = document.createElement("tr");
		Orb.crossBrowserSetStyle(tr, "bgcolor: black;white-space: nowrap;", true);
		if ( PLib.sat[i].name == sat_selected )
			Orb.createCell(tr, "sat-selected-label", PLib.sat[i].name);
		else
			Orb.createCell(tr, "sat-select-label", PLib.sat[i].name);


		if ( i % 5 == 0 )
			tbody.appendChild(tr);
		
	}

}
*/

function selectSatellite( name )
{
	sat_selected = name;
	loadSatMenu2();
}

function loadSatMenu2()
{
	var trow, label, name;
	var html = '<tbody>';

	for (var i = 0; i< PLib.sat.length; i++)
	{
		name = PLib.sat[i].name;
		if ( i % 5 == 0 )
			html += "<tr style='bgcolor: black;white-space: nowrap;'>";

		if ( PLib.sat[i].name == sat_selected )
			label = 'sat-selected-label';
		else
			label = 'sat-select-label';

		html += "<td class='" + label + "' onclick='selectSatellite(\"" + name + "\")'>" + PLib.sat[i].name + "</td>";

		if ( i % 5 == 4 )
			html += "</tr>";
	};

	if ( i % 5 != 4 )
		html += "</tr>";

	html += "</tbody>";

	$("#table_sats").empty();
	$("#table_sats").append( html );
//jwc
}


/*
function drawMap() 
{
	var ctx = document.getElementById('canvas').getContext('2d');
	var img = new Image();
	img.src = image_url;
	img.onload = function(){
				ctx.drawImage(img,0,0);
	}
}
*/
