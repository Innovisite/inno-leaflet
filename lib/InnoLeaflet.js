function InnoLeafLet(map) {

    this.map = map;
    
    this.geoLayer = undefined;

    var that = this;

    this.map.on('moveend', function(e) {
	var bounds = map.getBounds();
	var zoom = map.getZoom();
	console.log("bounds=", bounds);
	console.log("zoom=", zoom);

	$.ajax({
	    type: "POST",
	    url: "geojson.php",
	    data: {
		"bounds": [
		    bounds.getNorthWest().lng,
		    bounds.getNorthWest().lat,
		    bounds.getSouthEast().lng,
		    bounds.getSouthEast().lat
		],
		"zoom": zoom
	    },
	    dataType: "json",
	}).done(function(res) {
	    if(that.geoLayer !== undefined) {
		map.removeLayer(that.geoLayer);
	    }
	    that.geoLayer = L.geoJson(res.data);
	    that.geoLayer.addTo(map);
	});	    
    });
}
