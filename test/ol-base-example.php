<?php

/*
 * Copyright (c) 2012, terrestris GmbH & Co. KG
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, 
 *   this list of conditions and the following disclaimer in the documentation 
 *   and/or other materials provided with the distribution.
 * - Neither the name of terrestris GmbH & Co. KG nor the names of its 
 *   contributors may be used to endorse or promote products derived from this 
 *   software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 * BSD-License from http://www.opensource.org/licenses/bsd-license.php
 * 
 */

#error_reporting(E_ALL);
#ini_set('display_errors', 1);
require_once('../classes/Imp.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <title>IMP - OpenLayers Example</title>
        <link rel="stylesheet" href="default-style.css" type="text/css">
        <link rel="stylesheet" href="style.css" type="text/css">
        <SCRIPT LANGUAGE="JavaScript" SRC="http://www.openlayers.org/api/OpenLayers.js">
        </SCRIPT>
		
		<?php
			#$imp = new Imp('../data/delphi-one-record.xml', true);
			#$imp = new Imp('../data/delphi.xml', true);
			$imp = new Imp('../data/portal-u.xml', true);
			echo "<script type='text/javascript'>var cswFeatures = " . $imp->asOpenLayersFeatures() . ";</script>";
		?>
		
        <script>
        	
	        function onPopupClose(evt){
	            selectControl.unselect(selectedFeature);
	        }
	        
	        function onFeatureSelect(feature){
	            selectedFeature = feature;
				var html = '';
				html += "<div style='font-size:.8em'>" + feature.attributes.title + "<br />Abstract: " + feature.attributes.recordAbstract + "</div>";
				html += "<div style='font-size:.6em; padding-top: 15px;'>von " + feature.attributes.responsibleParty.organisationName + "</div>"
	            popup = new OpenLayers.Popup.FramedCloud("chicken", feature.geometry.getBounds().getCenterLonLat(), null, html, null, true, onPopupClose);
	            feature.popup = popup;
	            map.addPopup(popup);
	        }
	        
	        function onFeatureUnselect(feature){
	            map.removePopup(feature.popup);
	            feature.popup.destroy();
	            feature.popup = null;
	        }
        	
			function init() {
			
	            map = new OpenLayers.Map("map");
	            
	            var ol_wms = new OpenLayers.Layer.WMS("OpenLayers WMS", "http://vmap0.tiles.osgeo.org/wms/vmap0", {
	                layers: "basic"
	            });
				
				var featureLayer = new OpenLayers.Layer.Vector('CSW');
				
	            featureLayer.addFeatures(cswFeatures);
				
                selectControl = new OpenLayers.Control.SelectFeature(featureLayer, {
                    onSelect: onFeatureSelect,
                    onUnselect: onFeatureUnselect
                });
				
				
	            map.addLayers([ol_wms, featureLayer]);
	            map.addControl(new OpenLayers.Control.LayerSwitcher());
				map.addControl(selectControl);
				selectControl.activate();
	            map.zoomToExtent(new OpenLayers.Bounds(5.865,47.2747,15.0338,55.0565));
			
			}
        </script>
    </head>
    <body onload="init();">
    	<div>
        	<img src="../images/imp-logo.png" width="80" style="float:left; padding-right: 20px;">
			<h1 id="title">IMP - OpenLayers Example</h1>
			<p>INSPIRE Metadata Parser with OpenLayers</p>
		</div>

        <div id="map" style="width: 600px; height: 400px;">
        </div>
		<p id="shortdesc">
            Demonstrating how IMP returns CSW records as OpenLayers features
        </p>
    </body>
</html>
