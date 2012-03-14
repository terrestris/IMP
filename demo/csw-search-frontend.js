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
/**
 * 
 * @author terrestris GmbH & Co. KG
 * @author C. Mayer <mayer@terrestris.de> <mayer@terrestris.de>
 * @author M. Jansen <jansen@terrestris.de> <jansen@terrestris.de>
 * 
 * @version $Id: csw-search-frontend.js 57 2012-03-14 10:30:18Z mayer $
 */
CswSearch = function() {

	var me = this;
	
	var map = new OpenLayers.Map();

	var layer = new OpenLayers.Layer.WMS("Global Imagery",
			"http://maps.opengeo.org/geowebcache/service/wms", {
				layers : "bluemarble"
	});

	var layerVector = new OpenLayers.Layer.Vector("BBOXES");

	map.addLayers([ layer, layerVector ]);

	// map panel
	var mapPanel = new GeoExt.MapPanel({
		title : "GeoExt MapPanel",
		height : 400,
		width : 800,
		map : map,
		center : new OpenLayers.LonLat(5, 45),
		zoom : 4
	});

	/**
	 * adds the returned OL bbox features of IMP 
	 * to the vector layer
	 * 
	 * @param xhr
	 * @param evt
	 */
	function getBboxes(xhr, evt) {

		var olFeatures = Ext.decode(xhr.responseText);
		
		layerVector.addFeatures(olFeatures);

		Ext.getCmp('map-tab').add(mapPanel);
		Ext.getCmp('map-tab').doLayout();
	}

	/**
	 * Handler function if the "OpenLayers" tab is activated
	 * Requests the BBOX features of IMP
	 * 
	 * @param tab
	 * @returns {handleActivate}
	 */
	function handleActivate(tab) {

		Ext.Ajax.request({
			url : 'parse.php',
			success : getBboxes,
			failure : function() {
				Ext.msg.show('FAILURE', 'An error occured');
			},
			params : {
				format : 'ol'
			}
		});

	}
	
	/**
	 * Creates a GeoExt popup with the info
	 * of the clicked feature
	 * 
	 * @param feature
	 */
	function createPopup(feature) {

		popup = new GeoExt.Popup(
				{
					title : feature.attributes.title,
					location : feature,
					width : 400,
					html : feature.attributes.recordAbstract
							+ ' <br><br> <b>VON</b>:<br>'
							+ feature.attributes.responsibleParty.organisationName
							+ '<br><b>KONTAKT</b>:<br>'
							+ feature.attributes.responsibleParty.electronicMailAddress,
					maximizable : true,
					collapsible : true
				});
		// unselect feature when the popup
		// is closed
		popup.on({
			close : function() {
				if (OpenLayers.Util.indexOf(layerVector.selectedFeatures,
						this.feature) > -1) {
					selectCtrl.unselect(this.feature);
				}
			}
		});
		popup.show();
	}

	// create popup on "featureselected"
	layerVector.events.on({
		featureselected : function(e) {
			createPopup(e.feature);
		}
	});

	// create select feature control
	var selectCtrl = new OpenLayers.Control.SelectFeature(layerVector);
	mapPanel.map.addControl(selectCtrl);
	selectCtrl.activate();

	/*
	 * layout UI
	 */
	var tabs = new Ext.TabPanel({
		renderTo : 'tabs',
		width : 800,
		activeTab : 0,
		frame : true,
		height : 400,
		items : [ {
			title : 'HTML',
			width : 800,
			autoScroll : true,
			autoLoad : {
				url : 'parse.php',
				params : 'format=html'
			}
		}, {
			title : 'INSPIRE HTML',
			autoScroll : true,
			width : 800,
			autoLoad : {
				url : 'parse.php',
				params : 'format=inspireHtml'
			}
		}, {
			title : 'JSON',
			width : 800,
			autoScroll : true,
			autoLoad : {
				url : 'parse.php',
				params : 'format=jsonPretty'
			}
		}, {
			title : 'OpenLayers',
			id : 'map-tab',
			listeners : {
				activate : handleActivate
			}
		} ]
	});

};
