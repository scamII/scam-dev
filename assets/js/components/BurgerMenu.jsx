import { useState, useEffect, useCallback } from 'react';

export default function BurgerMenu( { items } ) {
	const [ open, setOpen ] = useState( false );

	const toggle = useCallback( () => setOpen( ( prev ) => ! prev ), [] );
	const close = useCallback( () => setOpen( false ), [] );

	useEffect( () => {
		if ( open ) {
			document.body.style.overflow = 'hidden';
		} else {
			document.body.style.overflow = '';
		}
		return () => {
			document.body.style.overflow = '';
		};
	}, [ open ] );

	useEffect( () => {
		const handler = ( e ) => {
			if ( e.key === 'Escape' ) {
				close();
			}
		};
		document.addEventListener( 'keydown', handler );
		return () => document.removeEventListener( 'keydown', handler );
	}, [ close ] );

	return (
		<>
			<button
				onClick={ toggle }
				className="lg:hidden p-2 rounded-lg transition-colors
                           hover:bg-slate-500/10 focus:outline-none focus:ring-2 focus:ring-[var(--color-accent)]/40"
				aria-expanded={ open }
				aria-label={ open ? 'Закрыть меню' : 'Открыть меню' }
				style={ { color: 'var(--color-text-secondary)' } }
			>
				{ open ? (
					<svg
						className="w-6 h-6"
						fill="none"
						stroke="currentColor"
						viewBox="0 0 24 24"
					>
						<path
							strokeLinecap="round"
							strokeLinejoin="round"
							strokeWidth="2"
							d="M6 18L18 6M6 6l12 12"
						/>
					</svg>
				) : (
					<svg
						className="w-6 h-6"
						fill="none"
						stroke="currentColor"
						viewBox="0 0 24 24"
					>
						<path
							strokeLinecap="round"
							strokeLinejoin="round"
							strokeWidth="2"
							d="M4 6h16M4 12h16M4 18h16"
						/>
					</svg>
				) }
			</button>

			{ open && (
				<div
					className="fixed inset-0 top-[60px] z-40 lg:hidden"
					onClick={ close }
					aria-hidden="true"
				>
					<div
						className="absolute inset-0 transition-colors duration-300"
						style={ {
							background: 'rgba(0,0,0,0.4)',
							backdropFilter: 'blur(4px)',
						} }
					/>
					<div
						className="absolute top-0 left-4 right-4 rounded-2xl shadow-2xl p-4"
						style={ {
							background: 'var(--color-header-bg)',
							backdropFilter: 'blur(20px)',
							border: '1.5px solid rgba(255,87,67,0.2)',
							opacity: 0.98,
						} }
						onClick={ ( e ) => e.stopPropagation() }
					>
						<div dangerouslySetInnerHTML={ { __html: items } } />
					</div>
				</div>
			) }
		</>
	);
}
