import L from 'leaflet';
import VirtualGrid from 'leaflet-virtual-grid';

export function area(width, height) {
    return width * height;
}

export var FeatureManager = VirtualGrid.extend({
    options: {
	server: 'sqlite'
    },
    
    initialize: function(options) {
	VirtualGrid.prototype.initialize.call(this, options);
	options = L.setOptions(this, options);
	
	this.on('cellcreate', function(e) {
	    console.log(e.coords);
	});
    }
});


