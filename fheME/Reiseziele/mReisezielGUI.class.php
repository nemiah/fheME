<?php
/**
 *  This file is part of fheME.

 *  fheME is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  fheME is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2024, open3A GmbH - Support@open3A.de
 */
#namespace open3A;

class mReisezielGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mReiseziel");
		$gui->screenHeight();

		$gui->name("Reiseziel");
		$gui->activateFeature("editInPopup", $this);
		$gui->attributes(array("ReisezielName"));
		
		$markers = "";
		$AC = anyC::get("Reiseziel");
		while($R = $AC->n()){
			$ex = explode(",", $R->A("ReisezielKoordinaten"));
			
			$markers .= "
			size = new OpenLayers.Size(32,32);
			offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
			icon = new OpenLayers.Icon('./ubiquitous/OSM/location_pin_y.png', size, offset);
			
			var AKlonLat".$R->getID()." = new OpenLayers.LonLat($ex[1], $ex[0]).transform(fromProjection, toProjection);
			var Marker".$R->getID()." = new OpenLayers.Marker(AKlonLat".$R->getID().", icon);
				
			Marker".$R->getID().".events.register('click', Marker".$R->getID().", function() {
				contentManager.editInPopup('Reiseziel', ".$R->getID().", 'Eintrag bearbeiten', '', {});
			});
			
			mainMapMarkers.addMarker(Marker".$R->getID().");";
		
		}
		
		echo "<div style=\"display:flex;\">
	<div id=\"demoMap\" style=\"width:100%;height:400px;\"></div>
	<div style=\"width:400px;\">".$gui->getBrowserHTML($id)."</div>
	</div>
  <script>
    map = new OpenLayers.Map('demoMap');
    map.addLayer(new OpenLayers.Layer.OSM());
 
	var fromProjection = new OpenLayers.Projection(\"EPSG:4326\");
	var toProjection   = new OpenLayers.Projection(\"EPSG:900913\");
		
    /*var lonLat = new OpenLayers.LonLat(10.9385, 48.6808)
          .transform(
            new OpenLayers.Projection(\"EPSG:4326\"), // transform from WGS 1984
            map.getProjectionObject() // to Spherical Mercator Projection
          );
 
    var zoom=10;
 
    var markers = new OpenLayers.Layer.Markers( \"Markers\" );
    map.addLayer(markers);
 
    markers.addMarker(new OpenLayers.Marker(lonLat));*/
 
	var mainMapMarkers = new OpenLayers.Layer.Markers(\"Markers\");
	$markers

	map.addLayer(mainMapMarkers);

	var extent = mainMapMarkers.getDataExtent();
		
	var bounds = [extent.left, extent.bottom, extent.right, extent.top];
	map.zoomToExtent(bounds);
	
    //map.setCenter (lonLat, zoom);
	\$j('#demoMap').css('height', contentManager.maxHeight());
  </script>";
	}


}
?>