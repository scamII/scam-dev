import { useState, useEffect, useCallback } from 'react';

export default function BackToTop() {
	const [ visible, setVisible ] = useState( false );

	useEffect( () => {
		let frame = 0;
		const update = () => {
			frame = 0;
			setVisible( window.scrollY > 300 );
		};
		const handler = () => {
			if ( ! frame ) {
				frame = window.requestAnimationFrame( update );
			}
		};

		window.addEventListener( 'scroll', handler, { passive: true } );
		update();

		return () => {
			window.removeEventListener( 'scroll', handler );
			if ( frame ) {
				window.cancelAnimationFrame( frame );
			}
		};
	}, [] );

	const scrollToTop = useCallback( () => {
		const reduceMotion = window.matchMedia(
			'(prefers-reduced-motion: reduce)'
		).matches;
		window.scrollTo( {
			top: 0,
			behavior: reduceMotion ? 'auto' : 'smooth',
		} );
	}, [] );

	if ( ! visible ) {
		return null;
	}

	return (
		<button
			type="button"
			onClick={ scrollToTop }
			className={ [
				'fixed bottom-6 right-6 w-12 h-12 rounded-full z-50',
				'flex items-center justify-center border transition-all',
				'duration-300 focus:outline-none focus:ring-2',
				'focus:ring-[var(--color-accent)]/40',
			].join( ' ' ) }
			aria-label="Наверх"
			title="Наверх"
		>
			<span aria-hidden="true">↑</span>
		</button>
	);
}
