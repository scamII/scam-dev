import { existsSync, lstatSync, readFileSync, readdirSync } from 'node:fs';
import { join, relative } from 'node:path';

const root = process.argv[ 2 ] || process.cwd();
const failures = [];

function read( path ) {
	return readFileSync( join( root, path ), 'utf8' );
}

function assert( condition, message ) {
	if ( ! condition ) {
		failures.push( message );
	}
}

const skippedDirectories = new Set( [ '.git', '.dev', 'build', 'node_modules', 'vendor' ] );

function walk( directory, extension ) {
	if ( ! existsSync( join( root, directory ) ) ) {
		return [];
	}

	const output = [];
	for ( const name of readdirSync( join( root, directory ) ) ) {
		if ( skippedDirectories.has( name ) ) {
			continue;
		}

		const absolute = join( root, directory, name );
		const item = lstatSync( absolute );
		if ( item.isSymbolicLink() ) {
			continue;
		}
		if ( item.isDirectory() ) {
			output.push(
				...walk( relative( root, absolute ), extension )
			);
		} else if ( ! extension || name.endsWith( extension ) ) {
			output.push( relative( root, absolute ) );
		}
	}
	return output;
}

const phpFiles = [
	...walk( '.', '.php' ),
].filter(
	( path ) =>
		! path.startsWith( 'vendor/' ) &&
		! path.startsWith( 'node_modules/' ) &&
		! path.startsWith( 'tests/' )
);

for ( const path of phpFiles ) {
	const content = read( path );
	assert(
		! /\breading_time\s*\(/.test( content ),
		`${ path }: obsolete reading_time() call`
	);
	assert(
		! /\son(?:click|mouseover|mouseout|load|error)\s*=/i.test( content ),
		`${ path }: inline event handler`
	);
}

const webpack = read( 'webpack.config.js' );
assert(
	webpack.includes( "assets/js/src/index.jsx" ),
	'webpack must build from the JSX source entry'
);

assert(
	! existsSync( join( root, 'assets/js/app.js' ) ),
	'generated assets/js/app.js must not be committed'
);
assert(
	! existsSync(
		join( root, 'scam-dev-full-audit-report-ru-utf8-bom.md' )
	),
	'outdated audit must not remain at webroot'
);

const updater = read( 'inc/class-theme-updater.php' );
assert(
	updater.includes( 'upgrader_pre_download' ),
	'updater must verify the downloaded package'
);
assert(
	! updater.includes( 'upgrader_pre_install' ),
	'updater must not expect a ZIP path in upgrader_pre_install'
);
assert(
	updater.includes( 'sodium_crypto_sign_verify_detached' ),
	'update manifest must use an independent signature'
);


const matrixPlugin = read( 'plugins/scam-dev-matrix/scam-dev-matrix.php' );
const matrixService = read(
	'plugins/scam-dev-matrix/class-matrix-register.php'
);
assert(
	matrixPlugin.includes(
		'Scam_Dev_Matrix_Registration_Service::get_registration_page()'
	),
	'Matrix template routing must resolve the plugin registration page'
);
assert(
	! matrixService.includes( 'Scam_Dev_Matrix_Register' ),
	'Matrix plugin must not reuse the legacy theme class name'
);

const gallery = read( 'plugins/scam-dev-gallery/scam-dev-gallery.php' );
assert(
	gallery.includes( "'scamdev-gallery'" ) &&
		! gallery.includes( "'scam-dev-main'" ),
	'Gallery must own its frontend asset handles'
);
assert(
	gallery.includes(
		"add_action( 'wp_ajax_scamdev_preview_img'"
	),
	'Gallery AJAX callback must be registered at plugin load time'
);

const svg = read( 'plugins/scam-dev-svg/scam-dev-svg.php' );
assert(
	! svg.includes( 'wp_strip_all_tags( $svg' ),
	'SVG validity must not depend on text nodes'
);
assert(
	svg.includes( 'false === $written' ),
	'SVG sanitization must fail closed when the clean file cannot be written'
);

const legacyClassNames = [
	'Scam_Dev_Matrix_Register',
	'Scam_Dev_Matrix_Stats_Widget',
	'Scam_Dev_VK_Import',
	'Scam_Dev_Donate_Widget',
];
for ( const className of legacyClassNames ) {
	for ( const path of phpFiles.filter( ( item ) => item.startsWith( 'plugins/' ) ) ) {
		const exactClass = new RegExp( `\\b${ className }\\b` );
		assert(
			! exactClass.test( read( path ) ),
			`${ path }: legacy class collision ${ className }`
		);
	}
}

const suiteVersion = read( 'style.css' ).match( /^Version:\s*(.+)$/m )?.[ 1 ];
assert( suiteVersion === '1.3.0', 'theme release version must be 1.3.0' );
for ( const mainFile of [
	'plugins/scam-dev-donate-widget/scam-dev-donate-widget.php',
	'plugins/scam-dev-gallery/scam-dev-gallery.php',
	'plugins/scam-dev-matrix/scam-dev-matrix.php',
	'plugins/scam-dev-seo/scam-dev-seo.php',
	'plugins/scam-dev-svg/scam-dev-svg.php',
	'plugins/scam-dev-vk-import/scam-dev-vk-import.php',
] ) {
	assert(
		read( mainFile ).includes( ' * Version: 1.3.0' ),
		`${ mainFile }: suite version is not synchronized`
	);
}

const docker = read( 'docker-compose.yml' );
assert(
	! /["']?3306:3306["']?/.test( docker ),
	'MySQL must not be published on the host'
);
assert(
	! /PMA_(?:USER|PASSWORD)/.test( docker ),
	'phpMyAdmin must not use automatic credentials'
);
assert(
	docker.includes( '127.0.0.1:8080:80' ),
	'WordPress dev port must bind to loopback'
);

const workflow = read( '.github/workflows/build.yaml' );
assert(
	workflow.includes( 'contents: read' ),
	'workflow must default to read-only repository access'
);
assert(
	workflow.includes( 'composer lint' ),
	'workflow must run PHPCS'
);
assert(
	workflow.includes( 'npm run test:static' ),
	'workflow must run static regression checks'
);

if ( failures.length ) {
	console.error( failures.map( ( failure ) => `- ${ failure }` ).join( '\n' ) );
	process.exit( 1 );
}

console.log( `Static checks passed (${ phpFiles.length } PHP files inspected).` );
