<?php
/**
 *  This file is part of ubiquitous.

 *  ubiquitous is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  ubiquitous is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2024, open3A GmbH - Support@open3A.de
 */
#namespace open3A;
class OpenLayers extends UnpersistentClass implements icontextMenu {
	public static function getButton($type, $AdresseID){
		$B = new Button("Karte\nanzeigen", "./ubiquitous/OSM/OpenLayers.png");
		$B->popup("", "Karte anzeigen", "OpenLayers", "-1", "popupShowPosition", array("'$AdresseID'"), "", "{position: 'left'}");
		$B->settings("OpenLayers");
		
		return $B;
	}
	
	public static function getLocation($AdresseID){
		if(Session::isPluginLoaded("mOSM")){
			$Adresse = new Adresse($AdresseID);
			$Location = OSM::getGeoLocation($AdresseID, $Adresse->A("land"), $Adresse->A("plz"), $Adresse->A("ort"), $Adresse->A("strasse")." ".$Adresse->A("nr"));
			$latLon = $Location->A("OSMData");
			return explode(" ", $latLon);
		}
		
		return false;
	}
	
	public function popupShowLatLon($lon, $lat){
		echo "
	<div id=\"demoMap\" style=\"width:400px;height:400px;\"></div>
  <script>
    map = new OpenLayers.Map('demoMap');
    map.addLayer(new OpenLayers.Layer.OSM());
 
    var lonLat = new OpenLayers.LonLat($lat, $lon)
          .transform(
            new OpenLayers.Projection(\"EPSG:4326\"), // transform from WGS 1984
            map.getProjectionObject() // to Spherical Mercator Projection
          );
 
    var zoom=16;
 
    var markers = new OpenLayers.Layer.Markers( \"Markers\" );
    map.addLayer(markers);
 
    markers.addMarker(new OpenLayers.Marker(lonLat));
 
    map.setCenter (lonLat, zoom);
  </script>";
	}
	
	public function popupShowPosition($AdresseID){
		$K = mUserdata::getGlobalSettingValue("googleAPIKeyMaps");
		if(!$K)
			die("<p class=\"highlight\">Bitte tragen Sie einen Google API-Key für die Kartendienste ein. Öffnen Sie dazu die Einstellungen mit dem Werkzeug-Symbol direkt neben dem Knopf \"Karte anzeigen\"</p>");
		
		$latLon = self::getLocation($AdresseID);
		$this->popupShowLatLon($latLon[0], $latLon[1]);
	}
	
	public function popupShowLocation($latitude, $longitude){
		echo $this->getMap($latitude, $longitude);
	}
	
	public function getMap($latitude, $longitude, $onclickCallback = null){
		return "
	<div id=\"demoMap\" style=\"width:100%;height:400px;\"></div>".OnEvent::script($this->getMapJS("demoMap", $latitude, $longitude, $onclickCallback));
	}

	public function getMapJS($targetElement, $latitude = null, $longitude = null, $onclickCallback = null, $proxyMapnik = null){
		return "
    var $targetElement = new OpenLayers.Map('$targetElement');
    $targetElement.addLayer(new OpenLayers.Layer.OSM(".($proxyMapnik != null ? "'Mapnik', '$proxyMapnik'" : "\"OpenStreetMap\"/*, [
        '//a.tile.openstreetmap.org/\${z}/\${x}/\${y}.png',
        '//b.tile.openstreetmap.org/\${z}/\${x}/\${y}.png',
        '//c.tile.openstreetmap.org/\${z}/\${x}/\${y}.png'
    ]*/")."));
 
	var fromProjection = new OpenLayers.Projection(\"EPSG:4326\");
	var toProjection   = new OpenLayers.Projection(\"EPSG:900913\");
	
     
    var {$targetElement}Markers = new OpenLayers.Layer.Markers(\"Markers\");
    $targetElement.addLayer({$targetElement}Markers);
	
	".(($latitude AND $longitude) ? "
	var zoom = 14;
	var lonLat = new OpenLayers.LonLat($longitude, $latitude).transform(fromProjection, toProjection);
    {$targetElement}Markers.addMarker(new OpenLayers.Marker(lonLat));
	" : "
	var zoom = 6;
	var lonLat = new OpenLayers.LonLat(10.447683, 51.163375).transform(fromProjection, toProjection);
	
	")."
	$targetElement.setCenter(lonLat, zoom);
		
	".($onclickCallback != null ? "
	OpenLayers.Control.Click = OpenLayers.Class(OpenLayers.Control, {               
		defaultHandlerOptions: {
			'single': true,
			'double': false,
			'pixelTolerance': 0,
			'stopSingle': false,
			'stopDouble': false
		},

		initialize: function(options) {
			this.handlerOptions = OpenLayers.Util.extend({}, this.defaultHandlerOptions);

			OpenLayers.Control.prototype.initialize.apply(this, arguments);

			this.handler = new OpenLayers.Handler.Click(this, {
				'click': this.trigger
				}, this.handlerOptions);
		},

		trigger: function(e) {
			var lonlatM = $targetElement.getLonLatFromPixel(e.xy);
			{$targetElement}Markers.clearMarkers();
			{$targetElement}Markers.addMarker(new OpenLayers.Marker(lonlatM));
			$onclickCallback(new OpenLayers.LonLat(lonlatM.lon, lonlatM.lat).transform(toProjection, fromProjection));
		}

	});
	
	var click = new OpenLayers.Control.Click();
	$targetElement.addControl(click);

	click.activate();" : "")."";
	}

	public function getID(){
		return 0;
	}
	
	public function getContextMenuHTML($identifier) {
		$F = New HTMLForm("apiKey", array("key"));
		$F->cols(1);
		
		$F->setSaveContextMenu($this, "saveContextMenu");
		
		echo "<p>Bitte tragen Sie unten den Google API-Key für die Kartendienste ein.</p>".$F;
	}
	
	public function saveContextMenu($key){
		mUserdata::setUserdataS("googleAPIKeyMaps", $key, "", -1);
	}

}
?>