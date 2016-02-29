import L from 'leaflet';
import VirtualGrid from 'leaflet-virtual-grid';
//import Request from 'request';

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
    },

    createCell: function(bounds, coords){
	console.log('create cell', bounds, coords);
    },

    cellEnter: function(bounds, coords){
	console.log('cell enter', bounds, coords);
    },

    cellLeave: function(bounds, coords) {
	console.log('cell leave', bounds, coords);
    }
});


