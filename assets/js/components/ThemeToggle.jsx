import { useState, useEffect, useCallback } from 'react';

const STORAGE_KEY = 'scam-dev-theme';
const MODES = [ 'auto', 'light', 'dark' ];

const LABELS = {
	dark: 'Тёмная тема',
	light: 'Светлая тема',
	auto: 'Автоматическая тема',
};

const TITLES = {
	dark: 'Переключить на светлую тему',
	light: 'Использовать системную тему',
	auto: 'Переключить на тёмную тему',
};

function readStoredMode() {
	try {
		const stored = window.localStorage.getItem( STORAGE_KEY );
		return MODES.includes( stored ) ? stored : 'auto';
	} catch {
		return 'auto';
	}
}

function storeMode( mode ) {
	try {
		window.localStorage.setItem( STORAGE_KEY, mode );
	} catch {
		// Storage can be unavailable in hardened browser configurations.
	}
}

function resolveTheme( mode ) {
	if ( mode === 'auto' ) {
		return window.matchMedia( '(prefers-color-scheme: light)' ).matches
			? 'light'
			: 'dark';
	}

	return mode;
}

export default function ThemeToggle() {
	const [ mode, setMode ] = useState( readStoredMode );
	const [ mounted, setMounted ] = useState( false );

	useEffect( () => setMounted( true ), [] );

	const applyMode = useCallback( ( selectedMode ) => {
		document.documentElement.setAttribute(
			'data-theme',
			resolveTheme( selectedMode )
		);
		document.documentElement.setAttribute(
			'data-theme-mode',
			selectedMode
		);
	}, [] );

	const cycle = useCallback( () => {
		setMode( ( previous ) => {
			const currentIndex = MODES.indexOf( previous );
			const next = MODES[ ( currentIndex + 1 ) % MODES.length ];
			storeMode( next );
			return next;
		} );
	}, [] );

	useEffect( () => applyMode( mode ), [ mode, applyMode ] );

	useEffect( () => {
		const mediaQuery = window.matchMedia(
			'(prefers-color-scheme: light)'
		);
		const handler = () => {
			if ( mode === 'auto' ) {
				applyMode( 'auto' );
			}
		};

		mediaQuery.addEventListener( 'change', handler );
		return () => mediaQuery.removeEventListener( 'change', handler );
	}, [ mode, applyMode ] );

	if ( ! mounted ) {
		return <div className="w-9 h-9" aria-hidden="true" />;
	}

	return (
		<button
			type="button"
			onClick={ cycle }
			className={ [
				'relative w-9 h-9 flex items-center justify-center',
				'rounded-lg transition-colors duration-300',
				'hover:bg-slate-500/10 focus:outline-none focus:ring-2',
				'focus:ring-[var(--color-accent)]/40',
			].join( ' ' ) }
			aria-label={ TITLES[ mode ] }
			title={ TITLES[ mode ] }
			data-theme-mode={ mode }
		>
			<span aria-hidden="true">
				{ mode === 'dark' && '☀' }
				{ mode === 'light' && '◐' }
				{ mode === 'auto' && '◑' }
			</span>
			<span className="screen-reader-text">{ LABELS[ mode ] }</span>
		</button>
	);
}
