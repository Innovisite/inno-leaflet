import config from './rollup.config';

config.format = 'umd';
config.dest = 'dist/inno-leaflet.umd.js';
config.moduleName = 'InnoLeaflet';

export default config;
