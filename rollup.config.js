import babel from 'rollup-plugin-babel';

export default {
  entry: 'lib/InnoLeaflet.js',
  sourceMap: true,
  plugins: [babel()]
};
