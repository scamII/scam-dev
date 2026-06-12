const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		app: path.resolve( __dirname, 'assets/js/app.js' ),
		style: path.resolve( __dirname, 'assets/scss/style.css' ),
		editor: path.resolve( __dirname, 'assets/scss/editor.css' ),
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( __dirname, 'build' ),
	},
	optimization: {
		...defaultConfig.optimization,
		splitChunks: false,
	},
};
