import { useState, useEffect, useCallback } from 'react';

const STORAGE_KEY = 'scam-dev-theme';
const MODES = [ 'auto', 'light', 'dark' ];

const LABELS = { dark: 'Тёмная тема', light: 'Светлая тема', auto: 'Авто' };
const TITLES = {
	dark: 'Сменить на светлую',
	light: 'Переключить в авто',
	auto: 'Сменить на тёмную',
};

function icon( style ) {
	return {
		fill: 'none',
		stroke: 'currentColor',
		strokeWidth: 2,
		strokeLinecap: 'round',
		strokeLinejoin: 'round',
		...style,
	};
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
	const [ mode, setMode ] = useState(
		() => localStorage.getItem( STORAGE_KEY ) || 'auto'
	);
	const [ mounted, setMounted ] = useState( false );

	useEffect( () => {
		setMounted( true );
	}, [] );

	const cycle = useCallback( () => {
		setMode( ( prev ) => {
			const idx = MODES.indexOf( prev );
			const next = MODES[ ( idx + 1 ) % MODES.length ];
			localStorage.setItem( STORAGE_KEY, next );
			return next;
		} );
	}, [] );

	useEffect( () => {
		document.documentElement.setAttribute(
			'data-theme',
			resolveTheme( mode )
		);
	}, [ mode ] );

	useEffect( () => {
		const mq = window.matchMedia( '(prefers-color-scheme: light)' );
		const handler = () => {
			if ( mode === 'auto' ) {
				document.documentElement.setAttribute(
					'data-theme',
					resolveTheme( 'auto' )
				);
			}
		};
		mq.addEventListener( 'change', handler );
		return () => mq.removeEventListener( 'change', handler );
	}, [ mode ] );

	if ( ! mounted ) {
		return <div className="w-9 h-9" />;
	}

	return (
		<button
			onClick={ cycle }
			className="relative w-9 h-9 flex items-center justify-center rounded-lg transition-colors duration-300 hover:bg-slate-500/10 focus:outline-none focus:ring-2 focus:ring-[var(--color-accent)]/40"
			aria-label={ LABELS[ mode ] }
			title={ TITLES[ mode ] }
			style={ { color: 'var(--color-text-secondary)' } }
		>
			<svg className="w-5 h-5" { ...icon( {} ) } viewBox="0 0 24 24">
				{ mode === 'dark' && (
					<>
						<circle cx="12" cy="12" r="5" />
						<path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
					</>
				) }
				{ mode === 'light' && (
					<path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
				) }
				{ mode === 'auto' && (
					<path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41m11.32-11.32l1.41-1.41M14 12a2 2 0 11-4 0 2 2 0 014 0z" />
				) }
			</svg>
		</button>
	);
}
