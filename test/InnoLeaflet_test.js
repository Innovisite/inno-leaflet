import test from 'tape-catch';
import { area } from '../lib/InnoLeaflet';
import L from 'leaflet';

function createMap() {
    var container = document.createElement('div');
    // give container a width/height
    container.setAttribute('style', 'width:500px; height: 500px;');    
    // add container to body
    document.body.appendChild(container);    
    return L.map(container);
}

test('area', function(t) {

    t.plan(1);

    var map = createMap();
    map.toto = 10;

    t.ok(area(0, 2) === 0);
});

