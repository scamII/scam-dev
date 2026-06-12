import { useState, useEffect, useCallback } from 'react';

export default function BackToTop() {
	const [ visible, setVisible ] = useState( false );

	useEffect( () => {
		const handler = () => setVisible( window.scrollY > 300 );
		window.addEventListener( 'scroll', handler, { passive: true } );
		return () => window.removeEventListener( 'scroll', handler );
	}, [] );

	const scrollToTop = useCallback( () => {
		window.scrollTo( { top: 0, behavior: 'smooth' } );
	}, [] );

	if ( ! visible ) {
		return null;
	}

	return (
		<button
			onClick={ scrollToTop }
			className="
                fixed bottom-6 right-6 w-12 h-12 rounded-full z-50
                flex items-center justify-center
                border transition-all duration-300
                focus:outline-none focus:ring-2 focus:ring-[var(--color-accent)]/40
            "
			style={ {
				background: 'var(--color-surface)',
				borderColor: 'var(--color-border-strong)',
				color: 'var(--color-text-secondary)',
				opacity: visible ? 1 : 0,
			} }
			aria-label="Наверх"
			title="Наверх"
		>
			<svg
				className="w-5 h-5"
				fill="none"
				stroke="currentColor"
				viewBox="0 0 24 24"
			>
				<path
					strokeLinecap="round"
					strokeLinejoin="round"
					strokeWidth="2"
					d="M5 15l7-7 7 7"
				/>
			</svg>
		</button>
	);
}
