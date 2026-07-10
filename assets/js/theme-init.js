( function () {
	'use strict';

	const storageKey = 'scam-dev-theme';
	const allowedModes = [ 'auto', 'light', 'dark' ];

	let mode = 'auto';

	try {
		const saved = window.localStorage.getItem( storageKey );
		if ( allowedModes.includes( saved ) ) {
			mode = saved;
		}
	} catch {
		// Storage can be disabled by browser privacy settings.
	}

	const resolved =
		mode === 'auto'
			? window.matchMedia( '(prefers-color-scheme: light)' ).matches
				? 'light'
				: 'dark'
			: mode;

	document.documentElement.setAttribute( 'data-theme', resolved );
	document.documentElement.setAttribute( 'data-theme-mode', mode );
} )();
